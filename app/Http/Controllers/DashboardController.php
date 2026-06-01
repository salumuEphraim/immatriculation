<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Controle;
use App\Models\Contravention;
use App\Models\User;
use App\Models\Vehicule;
use App\Services\MobileMoney\MobileMoneyService;
use App\Services\DocumentExpirationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;

        if ($role === 'admin') {
            // Données réelles du système
            $totalVehicules = Vehicule::count();
            
            // Agents actifs (ayant un utilisateur associé)
            $totalAgents = Agent::whereHas('user')->count();
            
            // Contrôles réels effectués (aujourd'hui et cette semaine)
            $totalControlesToday = Controle::whereDate('created_at', today())->count();
            $totalControlesThisWeek = Controle::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $totalControles = Controle::count();
            
            // Infractions validées (seulement celles avec statut 'validee')
            $totalInfractions = Contravention::where('statut', 'validee')->count();
            $infractionsToday = Contravention::where('statut', 'validee')->whereDate('created_at', today())->count();
            
            // Recettes réelles (seulement des infractions PAYÉES)
            $totalRecettes = Contravention::where('statut', 'validee')->where('est_payee', true)->sum('montant');
            $recettesToday = Contravention::where('statut', 'validee')->where('est_payee', true)->whereDate('updated_at', today())->sum('montant');
            $recettesThisMonth = Contravention::where('statut', 'validee')->where('est_payee', true)
                ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('montant');
            
            // Activités récentes mixtes (contrôles + infractions + nouveaux agents)
            $recentActivities = collect();
            
            // Ajouter les contrôles récents
            $recentControles = Controle::with('agent.user')
                ->latest('created_at')
                ->take(3)
                ->get()
                ->map(function ($controle) {
                    return [
                        'type' => 'scan',
                        'plaque' => $controle->plaque_immatriculation ?? 'Inconnue',
                        'description' => 'Contrôle routier effectué',
                        'lieu' => $controle->lieu ?? 'Non spécifié',
                        'agent' => $controle->agent->user->name ?? 'Agent inconnu',
                        'created_at' => $controle->created_at,
                    ];
                });
            
            // Ajouter les infractions récentes validées
            $recentInfractions = Contravention::with(['vehicule', 'agent'])
                ->where('statut', 'validee')
                ->latest('created_at')
                ->take(3)
                ->get()
                ->map(function (Contravention $contravention) {
                    return [
                        'type' => 'infraction',
                        'plaque' => $contravention->vehicule->plaque_immatriculation ?? 'Inconnue',
                        'description' => $contravention->type,
                        'lieu' => $contravention->lieu ?? 'Non spécifié',
                        'agent' => $contravention->agent->name ?? 'Agent inconnu',
                        'montant' => $contravention->montant,
                        'created_at' => $contravention->created_at,
                    ];
                });
            
            // Ajouter les nouveaux agents récents
            $recentAgents = User::where('role', 'agent')
                ->with('agent')
                ->latest('created_at')
                ->take(2)
                ->get()
                ->map(function ($user) {
                    return [
                        'type' => 'user',
                        'plaque' => null,
                        'description' => "Nouvel agent enregistré: {$user->name}",
                        'lieu' => null,
                        'agent' => $user->name,
                        'created_at' => $user->created_at,
                    ];
                });
            
            // Construire un tableau simple et trier
            $allActivities = [];
            
            // Ajouter les contrôles
            foreach ($recentControles as $controle) {
                $allActivities[] = $controle;
            }
            
            // Ajouter les infractions
            foreach ($recentInfractions as $infraction) {
                $allActivities[] = $infraction;
            }
            
            // Ajouter les agents
            foreach ($recentAgents as $agent) {
                $allActivities[] = $agent;
            }
            
            // Trier par date
            usort($allActivities, function($a, $b) {
                $dateA = $a['created_at'];
                $dateB = $b['created_at'];
                return $dateB <=> $dateA;
            });
            
            $recentActivities = collect(array_slice($allActivities, 0, 8));

            return view('admin.dashboard', compact(
                'totalVehicules',
                'totalAgents',
                'totalControles',
                'totalControlesToday',
                'totalControlesThisWeek',
                'totalInfractions',
                'infractionsToday',
                'totalRecettes',
                'recettesToday',
                'recettesThisMonth',
                'recentActivities'
            ));
        }

        if ($role === 'agent') {
            if (view()->exists('agent.dashboard')) {
                return view('agent.dashboard');
            }

            return redirect('/')->with('error', 'Interface Agent en cours de développement.');
        }

        if ($role === 'proprietaire') {
            $vehicules = collect();
            $infractionsImpayees = collect();
            $vehiclesData = collect();

            if ($user->proprietaire) {
                $vehicules = $user->proprietaire->vehicules()->with(['documents'])->get();
                $infractionsImpayees = Contravention::with('vehicule')
                    ->whereIn('vehicule_id', $vehicules->pluck('id'))
                    ->where('statut', 'validee')
                    ->where('est_payee', false)
                    ->latest('date_infraction')
                    ->get();

                // Calculer les statuts précis avec le service d'expiration
                $vehiclesData = $vehicules->map(function ($vehicle) {
                    $summary = app(DocumentExpirationService::class)->getVehicleExpirationSummary($vehicle);
                    
                    // Statut précis : EN RÈGLE seulement si TOUS les documents sont présents ET valides
                    $requiredTypes = ['assurance', 'vignette', 'controle_technique', 'carte_rose'];
                    $existingTypes = $vehicle->documents->pluck('type')->toArray();
                    $missingTypes = array_diff($requiredTypes, $existingTypes);
                    
                    $hasAllDocuments = empty($missingTypes);
                    $hasExpiredDocs = $summary['expired'] > 0;
                    $hasExpiringSoonDocs = $summary['expiring_soon'] > 0;
                    
                    $status = 'en_regle';
                    $statusText = 'En règle';
                    $statusColor = 'success';
                    $statusIcon = 'check-circle-fill';
                    
                    if (!$hasAllDocuments || $hasExpiredDocs) {
                        $status = 'en_defaut';
                        $statusText = 'En défaut';
                        $statusColor = 'danger';
                        $statusIcon = 'exclamation-triangle-fill';
                    } elseif ($hasExpiringSoonDocs) {
                        $status = 'attention';
                        $statusText = 'Attention';
                        $statusColor = 'warning';
                        $statusIcon = 'clock-fill';
                    }
                    
                    return [
                        'vehicle' => $vehicle,
                        'summary' => $summary,
                        'status' => $status,
                        'status_text' => $statusText,
                        'status_color' => $statusColor,
                        'status_icon' => $statusIcon,
                        'missing_documents' => $missingTypes,
                        'has_all_documents' => $hasAllDocuments,
                        'urgent_count' => $summary['expired'] + $summary['expiring_soon']
                    ];
                });
            }

            if (view()->exists('proprietaire.dashboard')) {
                return view('proprietaire.dashboard', compact('vehiclesData', 'infractionsImpayees'));
            }

            return redirect('/')->with('info', 'Bienvenue sur RoadShield. Votre espace est en maintenance.');
        }

        return redirect('/')->with('error', 'Rôle non reconnu.');
    }

    public function proprietaireInfractions()
    {
        $user = Auth::user();

        if (!$user->proprietaire) {
            abort(404, 'Profil propriétaire non trouvé.');
        }

        $infractions = Contravention::with(['vehicule', 'agent'])
            ->whereIn('vehicule_id', $user->proprietaire->vehicules()->pluck('vehicules.id'))
            ->latest('date_infraction')
            ->get();

        return view('proprietaire.infractions', compact('infractions'));
    }

    public function myVehicles(DocumentExpirationService $expirationService)
    {
        $user = Auth::user();

        if (!$user->proprietaire) {
            abort(404, 'Profil propriétaire non trouvé.');
        }

        $vehicles = $user->proprietaire->vehicules()
            ->with(['documents'])
            ->withCount([
                'contraventions as amendes_impayees_count' => function ($query) {
                    $query->where('statut', 'validee')->where('est_payee', false);
                },
            ])
            ->orderBy('plaque_immatriculation')
            ->get();

        // Calculer les statuts et dates d'expiration pour chaque véhicule
        $vehiclesData = $vehicles->map(function ($vehicle) use ($expirationService) {
            $summary = $expirationService->getVehicleExpirationSummary($vehicle);
            
            // Récupérer les détails des documents avec jours restants
            $documentsDetails = [];
            foreach ($vehicle->documents as $document) {
                $status = $expirationService->getExpirationStatus($document);
                $documentsDetails[] = [
                    'type' => $document->type,
                    'type_formatted' => match($document->type) {
                        'assurance' => 'Assurance',
                        'vignette' => 'Vignette fiscale',
                        'controle_technique' => 'Contrôle technique',
                        'carte_rose' => 'Carte rose',
                        default => ucfirst(str_replace('_', ' ', $document->type))
                    },
                    'status' => $status['status'],
                    'days_remaining' => $status['days_remaining'],
                    'expiration_date' => $status['expiration_date'],
                    'formatted_days' => $this->formatDaysRemaining($status['days_remaining'])
                ];
            }

            return [
                'vehicle' => $vehicle,
                'summary' => $summary,
                'documents_details' => $documentsDetails,
                'overall_status' => $this->getOverallStatus($summary),
                'urgent_count' => $summary['expired'] + $summary['expiring_soon']
            ];
        });

        return view('proprietaire.vehicles', compact('vehiclesData'));
    }

    public function sendTestNotification(DocumentExpirationService $expirationService)
    {
        $user = Auth::user();

        if (!$user->proprietaire) {
            abort(404, 'Profil propriétaire non trouvé.');
        }

        $vehicule = $user->proprietaire->vehicules()->with('proprietaire.user')->first();
        if (!$vehicule) {
            return back()->with('error', 'Aucun véhicule trouvé pour envoyer le test.');
        }

        $sent = $expirationService->sendTestExpirationNotification($vehicule);

        if ($sent) {
            return back()->with('success', 'Alerte de test envoyée au propriétaire par email ou SMS de secours.');
        }

        return back()->with('error', 'Impossible d\'envoyer l\'alerte de test. Email ou téléphone introuvable.');
    }

    public function getMyVehiclesRealtimeDocuments(DocumentExpirationService $expirationService)
    {
        $user = Auth::user();

        if (!$user->proprietaire) {
            abort(404, 'Profil propriétaire non trouvé.');
        }

        $vehicles = $user->proprietaire->vehicules()->with(['documents'])->get();
        $vehiclesData = [];

        foreach ($vehicles as $vehicle) {
            $documents = [];

            foreach ($vehicle->documents as $document) {
                $status = $expirationService->getExpirationStatus($document);

                $documents[] = [
                    'id' => $document->id,
                    'type' => $document->type,
                    'type_formatted' => match($document->type) {
                        'assurance' => 'Assurance',
                        'vignette' => 'Vignette fiscale',
                        'controle_technique' => 'Contrôle technique',
                        'carte_rose' => 'Carte rose',
                        default => ucfirst(str_replace('_', ' ', $document->type))
                    },
                    'expiration_date' => $status['expiration_date'],
                    'status' => $status['status'],
                    'days_remaining' => $status['days_remaining'],
                    'formatted_days' => $this->formatDaysRemaining($status['days_remaining']),
                    'is_notification_day' => $status['is_notification_day'] ?? false,
                ];
            }

            $vehiclesData[] = [
                'id' => $vehicle->id,
                'plaque' => $vehicle->plaque_immatriculation,
                'marque' => $vehicle->marque,
                'modele' => $vehicle->modele,
                'documents' => $documents,
            ];
        }

        return response()->json([
            'vehicles' => $vehiclesData,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Formate l'affichage des jours restants
     */
    private function formatDaysRemaining(int $days): string
    {
        if ($days < 0) {
            return "Expiré depuis " . abs($days) . " jours";
        } elseif ($days === 0) {
            return "Expire aujourd'hui";
        } elseif ($days === 1) {
            return "Expire demain";
        } elseif ($days <= 7) {
            return "Expire dans {$days} jours";
        } elseif ($days <= 30) {
            return "Expire dans {$days} jours";
        } else {
            return "Expire dans {$days} jours";
        }
    }

    /**
     * Détermine le statut global du véhicule
     */
    private function getOverallStatus(array $summary): string
    {
        if ($summary['expired'] > 0) {
            return 'critical';
        } elseif ($summary['expiring_soon'] > 0) {
            return 'warning';
        } elseif ($summary['total_documents'] === 0) {
            return 'no_documents';
        } else {
            return 'good';
        }
    }

    public function payerAmendeMobileMoney(Request $request, Contravention $contravention, MobileMoneyService $mobileMoney)
    {
        $user = Auth::user();

        if (!$user->proprietaire) {
            abort(403);
        }

        if (!$this->proprietairePossedeInfraction($user, $contravention)) {
            abort(403, 'Cette contravention ne concerne pas vos véhicules.');
        }

        $montantAttendu = (int) round((float) $contravention->montant);

        $driver = config('mobile_money.driver', 'mock');
        $minAmount = $driver === 'shwary' ? (int) config('mobile_money.shwary.min_amount_cdf', 2900) : 1;

        $request->validate([
            'telephone' => 'required|string|max:30',
            'operateur' => 'nullable|in:mpesa,orange,money,airtel,shwary',
            'montant_cdf' => 'required|numeric|min:' . $minAmount,
            'code_secret' => 'nullable|string|regex:/^\d{4,5}$/',
        ], [
            'code_secret.regex' => 'Le code secret doit comporter 4 ou 5 chiffres.',
            'montant_cdf.min' => $driver === 'shwary'
                ? "Le montant minimum Shwary pour la RDC est de {$minAmount} CDF."
                : 'Le montant doit etre superieur a zero.',
        ]);

        $montantSaisi = (int) round((float) $request->montant_cdf);
        if ($montantSaisi !== $montantAttendu) {
            return redirect()
                ->route('proprietaire.infractions')
                ->with('error', 'Le montant saisi doit être exactement celui de l\'amende.');
        }

        $statut = $contravention->statut ?? 'en_attente';
        if ($statut !== 'validee' || ($contravention->est_payee ?? false)) {
            return redirect()
                ->route('proprietaire.infractions')
                ->with('error', 'Cette amende ne peut pas être payée en ligne.');
        }

        if (($contravention->paiement_statut ?? null) === 'initiated' && !$contravention->est_payee) {
            return redirect()
                ->route('proprietaire.infractions')
                ->with('error', 'Un paiement est déjà en cours pour cette amende.');
        }

        $result = $mobileMoney->initiate($contravention, $request->telephone, $request->operateur ?? $driver);

        if (!$result->success) {
            return redirect()
                ->route('proprietaire.infractions')
                ->with('error', $result->message);
        }

        if ($result->completedImmediately) {
            return redirect()
                ->route('proprietaire.infractions')
                ->with('success', trim($result->message . ' Référence : ' . ($result->reference ?? '') . '.'));
        }

        $extra = $result->orderNumber ? ' N° commande : ' . $result->orderNumber . '.' : '';

        return redirect()
            ->route('proprietaire.infractions')
            ->with('success', trim($result->message . $extra . ' Référence : ' . ($result->reference ?? '') . '.'));
    }

    private function proprietairePossedeInfraction(User $user, Contravention $contravention): bool
    {
        return $user->proprietaire
            ->vehicules()
            ->where('vehicules.id', $contravention->vehicule_id)
            ->exists();
    }
}
