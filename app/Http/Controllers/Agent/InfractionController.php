<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Contravention;
use App\Models\Controle;
use App\Models\Agent;
use App\Models\BaremePrix;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; // Pour l'envoi de mail
use Barryvdh\DomPDF\Facade\Pdf;   
use App\Mail\ContraventionRecuMail;   // Pour le PDF

class InfractionController extends Controller
{
    /**
     * Affiche l'historique des infractions signalées par l'agent.
     */
    public function index()
    {
        $user = Auth::user();
        $agent = $user->agent ?? null;
        $infractions = $agent ? $agent->contraventions()->with(['vehicule', 'controle', 'baremePrix'])->latest()->get() : collect();

        return view('agent.infractions.index', compact('infractions'));
    }

    /**
     * Enregistre une infraction et redirige vers le reçu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicule_id' => 'nullable|exists:vehicules,id',
            'plaque'      => 'nullable|string',
            'type'        => 'required|string',
            'lieu'        => 'required|string',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
        ]);

        // Supporte les 2 cas: signalement depuis résultat (vehicule_id)
        // et depuis dashboard modal (plaque).
        $vehicule = null;
        if ($request->filled('vehicule_id')) {
            $vehicule = Vehicule::find($request->vehicule_id);
        } elseif ($request->filled('plaque')) {
            $plaque = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->plaque));
            $vehicule = Vehicule::whereRaw("REPLACE(REPLACE(UPPER(plaque), '/', ''), '-', '') = ?", [$plaque])->first();
        }

        if (!$vehicule) {
            return back()->with('error', "Véhicule introuvable. Vérifiez la plaque ou refaites la recherche.");
        }

        $lat = $request->filled('latitude') ? (float) $request->latitude : null;
        $lng = $request->filled('longitude') ? (float) $request->longitude : null;
        if ($lat === null || $lng === null) {
            $lat = $lng = null;
        }

        // Détecter les documents manquants pour le type "Défaut de documents"
        $documentsManquants = [];
        if ($request->type === 'Défaut de documents') {
            $documentsManquants = $this->detecterDocumentsManquants($vehicule);
        }

        $contravention = Contravention::create([
            'vehicule_id'     => $vehicule->id,
            'agent_id'        => Auth::id(),
            'type'            => $request->type,
            'lieu'            => $request->lieu,
            'latitude'        => $lat,
            'longitude'       => $lng,
            'montant'         => $this->calculerMontant($request->type),
            'code_unique'     => 'RS-' . strtoupper(bin2hex(random_bytes(3))),
            'statut'          => 'en_attente',
            'est_payee'       => false,
            'date_infraction' => now(),
            'documents_manquants' => $documentsManquants,
        ]);

        // Envoi automatique du reçu par email au propriétaire (si email disponible)
        $emailDestinataire = $vehicule->proprietaire->user->email ?? null;
        if ($emailDestinataire) {
            try {
                $pdf = Pdf::loadView('agent.infractions.pdf_recu', ['contravention' => $contravention]);
                Mail::to($emailDestinataire)->send(new ContraventionRecuMail($contravention, $pdf->output()));
            } catch (\Throwable $e) {
                // Ne bloque pas le flux de signalement si la messagerie échoue.
                Log::error('Echec envoi email automatique contravention', [
                    'contravention_id' => $contravention->id,
                    'to' => $emailDestinataire,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('agent.infractions.recu', $contravention->id)
                         ->with('success', "Signalement enregistré avec succès. Le reçu a été envoyé au propriétaire si son email est disponible.");
    }

    /**
     * Affiche le reçu à l'écran.
     */
    public function showRecu($id)
    {
        $contravention = Contravention::with(['vehicule.proprietaire.user', 'agent'])->findOrFail($id);
        $this->assertCanAccessContravention($contravention);
        return view('agent.infractions.recu', compact('contravention'));
    }

    /**
     * Génère le PDF et l'envoie par email au propriétaire.
     */
    public function envoyerEmail($id)
    {
        // 1. Récupérer la contravention avec l'email du propriétaire (via la relation User)
        $contravention = Contravention::with(['vehicule.proprietaire.user', 'agent'])->findOrFail($id);
        $this->assertCanAccessContravention($contravention);
        
        $emailDestinataire = $contravention->vehicule->proprietaire->user->email ?? null;

        if (!$emailDestinataire) {
            return back()->with('error', "Le propriétaire n'a pas d'adresse email associée à son compte.");
        }

        if ($this->isMailConfigInvalid()) {
            return back()->with('error', "Configuration email invalide. Vérifiez MAIL_USERNAME / MAIL_PASSWORD (mot de passe d'application Gmail) puis réessayez.");
        }

        // 2. Générer le PDF (utilise une vue simplifiée pour le PDF)
        $pdf = Pdf::loadView('agent.infractions.pdf_recu', compact('contravention'));

        // 3. Envoyer l'email avec le PDF en pièce jointe
        try {
            Mail::to($emailDestinataire)->send(new ContraventionRecuMail($contravention, $pdf->output()));
            return back()->with('success', "Le reçu PDF a été envoyé avec succès à l'adresse : {$emailDestinataire}");
        } catch (\Exception $e) {
            Log::error('Echec envoi email manuel contravention', [
                'contravention_id' => $contravention->id,
                'to' => $emailDestinataire,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $this->humanReadableMailError($e));
        }
    }

    /**
     * Grille tarifaire Lubumbashi.
     */
    private function calculerMontant($type) {
        return match($type) {
            'Défaut d\'assurance' => 150000,
            'Vignette impayée'   => 100000,
            'Excès de vitesse'    => 85000,
            'Défaut de documents' => 50000,
            default               => 45000,
        };
    }

    /**
     * Télécharge le PDF du reçu.
     */
    public function downloadPdf($id)
    {
        $contravention = Contravention::with(['vehicule.proprietaire.user', 'agent'])->findOrFail($id);
        $this->assertCanAccessContravention($contravention);
        
        $pdf = Pdf::loadView('agent.infractions.pdf_recu', compact('contravention'));
        
        return $pdf->download('recu_contravention_' . $contravention->code_unique . '.pdf');
    }

    /**
     * Vérifie l'accès selon le rôle:
     * - admin: tout voir
     * - agent: uniquement ses infractions
     * - proprietaire: uniquement infractions liées à ses véhicules
     */
    private function assertCanAccessContravention(Contravention $contravention): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'agent' && (int) $contravention->agent_id === (int) $user->id) {
            return;
        }

        if ($user->role === 'proprietaire' && $user->proprietaire) {
            $ownerId = $contravention->vehicule->proprietaire_id ?? null;
            if ((int) $ownerId === (int) $user->proprietaire->id) {
                return;
            }
        }

        abort(403, "Accès non autorisé à cette contravention.");
    }

    private function isMailConfigInvalid(): bool
    {
        $username = (string) config('mail.mailers.smtp.username');
        $password = (string) config('mail.mailers.smtp.password');

        return $username === '' || strtolower($username) === 'null'
            || $password === '' || strtolower($password) === 'null';
    }

    /**
     * Détecte les documents manquants pour un véhicule
     */
    private function detecterDocumentsManquants(Vehicule $vehicule): array
    {
        $requiredTypes = array_keys(Vehicule::REQUIRED_DOCUMENT_TYPES);
        $existingTypes = $vehicule->documents()->pluck('type')->toArray();
        $missingTypes = array_diff($requiredTypes, $existingTypes);
        
        $documentsManquants = [];
        foreach ($missingTypes as $type) {
            $documentsManquants[] = [
                'type' => $type,
                'nom' => match($type) {
                    'assurance' => 'Assurance',
                    'vignette' => 'Vignette fiscale',
                    'controle_technique' => 'Contrôle technique',
                    'carte_rose' => 'Carte rose',
                    default => ucfirst(str_replace('_', ' ', $type))
                }
            ];
        }
        
        // Vérifier aussi les documents expirés
        foreach ($vehicule->documents as $document) {
            if (!in_array($document->type, $requiredTypes, true)) {
                continue;
            }

            if ($document->date_expiration && $document->date_expiration < now()) {
                $documentsManquants[] = [
                    'type' => $document->type,
                    'nom' => match($document->type) {
                        'assurance' => 'Assurance (expirée)',
                        'vignette' => 'Vignette fiscale (expirée)',
                        'controle_technique' => 'Contrôle technique (expiré)',
                        'carte_rose' => 'Carte rose (expirée)',
                        default => ucfirst(str_replace('_', ' ', $document->type)) . ' (expiré)'
                    },
                    'date_expiration' => $document->date_expiration->format('d/m/Y')
                ];
            }
        }
        
        return $documentsManquants;
    }

    private function humanReadableMailError(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (
            str_contains($message, '535')
            || str_contains($message, 'BadCredentials')
            || str_contains($message, 'Failed to authenticate on SMTP server')
        ) {
            return "Échec d'authentification Gmail. Vérifiez l'adresse Gmail et surtout le mot de passe d'application Google, puis réessayez.";
        }

        if (str_contains($message, 'Connection could not be established') || str_contains($message, 'timed out')) {
            return "Connexion au serveur email impossible. Vérifiez l'hôte SMTP, le port, le chiffrement et l'accès réseau.";
        }

        return "Échec de l'envoi de l'email. Vérifiez la configuration email et les journaux applicatifs, puis réessayez.";
    }
}
