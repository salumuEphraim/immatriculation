<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicule;
use App\Models\Contravention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InfractionController extends Controller
{
    /**
     * Affiche la liste des infractions pour l'administrateur.
     * Permet à l'admin de voir QUEL agent a signalé QUEL véhicule.
     */
    public function index()
    {
        // On charge les relations 'vehicule' et 'agent' pour l'affichage
$infractions = Contravention::with(['vehicule', 'agent'])->latest()->get();
        return view('admin.infractions.index', compact('infractions'));
    }

    /**
     * Enregistre une nouvelle infraction (utilisée par l'Agent ou l'Admin).
     */
    public function store(Request $request)
    {
        // 1. Validation des données arrivant du formulaire
        $request->validate([
            'vehicule_id' => 'required|exists:vehicules,id',
            'type' => 'nullable|string',
            'lieu' => 'nullable|string',
        ]);

        // 2. Récupération du véhicule pour vérifier les documents
        $vehicule = Vehicule::findOrFail($request->vehicule_id);

        // 3. Détermination automatique des documents manquants
        $documents = $vehicule->documents()->pluck('type')->all();
        $manquants = collect(Vehicule::REQUIRED_DOCUMENT_TYPES)
            ->reject(fn ($label, $type) => in_array($type, $documents, true))
            ->values()
            ->all();

        $description = 'Documents manquants : ' . (empty($manquants) ? 'Aucun (Autre infraction)' : implode(', ', $manquants));

        // 4. Création de l'infraction
$infraction = Contravention::create([
            'vehicule_id'     => $vehicule->id,
            'agent_id'        => Auth::id(), // L'ID de l'agent connecté (ou admin)
            'type'            => $request->type ?? 'Défaut de documents',
            'lieu'            => $request->lieu ?? 'Lubumbashi Centre',
            'montant'         => 100000.00, // Montant fixe ou calculé
            'statut'          => 'en_attente',
            'code_unique'     => 'RS-' . strtoupper(bin2hex(random_bytes(3))), // Code pour le reçu
            'date_infraction' => now(),
            'description'     => $description,
            'documents_manquants' => $manquants,
        ]);

        // 5. Redirection vers le reçu pour l'agent
        if (Auth::user()->hasRole('agent')) {
            return redirect()->route('agent.infractions.recu', $infraction->id)
                             ->with('success', "Signalement effectué avec succès.");
        }

        return back()->with('success', "La contravention a été enregistrée avec succès dans RoadShield.");
    }

    /**
     * Permet à l'admin de changer le statut en "validee".
     */
    public function valider($id)
    {
$contravention = Contravention::findOrFail($id);
        $contravention->statut = 'validee';
$contravention->save();

        return back()->with('success', "Contravention validée avec succès.");
    }

    /**
     * Mise à jour complète du statut d'infraction.
     */
    public function updateStatut(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,validee,rejetee,payee',
        ]);

$contravention = Contravention::findOrFail($id);
        $contravention->statut = $request->statut;
$contravention->save();

        return back()->with('success', "Statut de la contravention mis à jour.");
    }

    /**
     * Génère le rapport global (stats) pour l'admin.
     */
    public function genererRapport()
    {
        // Recettes totales (infractions validées)
$recettesTotales = (float) Contravention::where('statut', 'validee')->sum('montant');

        // Répartition par nature/type
        $statsType = Contravention::query()
            ->select([
                'type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(montant) as recettes'),
            ])
            ->where('statut', 'validee')
            ->groupBy('type')
            ->orderByDesc('recettes')
            ->get();

        // Top agents de contrôle (par nombre de PV dressés)
        $performanceAgentsRows = Contravention::query()
            ->select([
                'agent_id',
                DB::raw('COUNT(*) as total'),
            ])
            ->where('statut', 'validee')
            ->groupBy('agent_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $performanceAgents = $performanceAgentsRows->map(function ($row) {
            $row->agent = User::find($row->agent_id) ?: (object) ['name' => 'Agent inconnu'];
            return $row;
        });

        return view('admin.rapports.index', compact(
            'recettesTotales',
            'statsType',
            'performanceAgents'
        ));
    }
}
