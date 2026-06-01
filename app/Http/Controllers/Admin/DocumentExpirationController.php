<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use App\Services\DocumentExpirationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentExpirationController extends Controller
{
    protected DocumentExpirationService $expirationService;

    public function __construct(DocumentExpirationService $expirationService)
    {
        $this->expirationService = $expirationService;
    }

    /**
     * Affiche le tableau de bord des expirations de documents
     */
    public function index()
    {
        $vehicles = Vehicule::with(['documents', 'proprietaire.user'])->get();
        
        $summaries = [];
        $stats = [
            'total_vehicles' => $vehicles->count(),
            'vehicles_with_expired_docs' => 0,
            'vehicles_with_expiring_soon' => 0,
            'vehicles_all_valid' => 0,
            'total_expired_documents' => 0,
            'total_expiring_soon_documents' => 0,
        ];

        foreach ($vehicles as $vehicle) {
            $summary = $this->expirationService->getVehicleExpirationSummary($vehicle);
            $summaries[] = $summary;

            if ($summary['expired'] > 0) {
                $stats['vehicles_with_expired_docs']++;
                $stats['total_expired_documents'] += $summary['expired'];
            }

            if ($summary['expiring_soon'] > 0) {
                $stats['vehicles_with_expiring_soon']++;
                $stats['total_expiring_soon_documents'] += $summary['expiring_soon'];
            }

            if ($summary['expired'] === 0 && $summary['expiring_soon'] === 0 && $summary['total_documents'] > 0) {
                $stats['vehicles_all_valid']++;
            }
        }

        return view('admin.document_expirations.index', compact('summaries', 'stats', 'vehicles'));
    }

    /**
     * Traite manuellement les notifications d'expiration
     */
    public function processNotifications()
    {
        try {
            $notifications = $this->expirationService->processExpirationNotifications();
            
            Log::info('Notifications traitées manuellement par l\'administrateur', [
                'admin_id' => auth()->id(),
                'notifications_count' => count($notifications)
            ]);

            return back()->with('success', count($notifications) . ' notifications ont été traitées avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement manuel des notifications', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Une erreur est survenue lors du traitement des notifications: ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails d'un véhicule spécifique
     */
    public function showVehicle(Vehicule $vehicule)
    {
        $vehicule->load(['documents', 'proprietaire.user']);
        $summary = $this->expirationService->getVehicleExpirationSummary($vehicule);
        
        return view('admin.document_expirations.show', compact('vehicule', 'summary'));
    }

    /**
     * API pour obtenir les statistiques en temps réel
     */
    public function getStats()
    {
        $vehicles = Vehicule::with(['documents'])->get();
        
        $stats = [
            'total_vehicles' => $vehicles->count(),
            'vehicles_with_expired_docs' => 0,
            'vehicles_with_expiring_soon' => 0,
            'vehicles_all_valid' => 0,
            'total_expired_documents' => 0,
            'total_expiring_soon_documents' => 0,
        ];

        foreach ($vehicles as $vehicle) {
            $summary = $this->expirationService->getVehicleExpirationSummary($vehicle);

            if ($summary['expired'] > 0) {
                $stats['vehicles_with_expired_docs']++;
                $stats['total_expired_documents'] += $summary['expired'];
            }

            if ($summary['expiring_soon'] > 0) {
                $stats['vehicles_with_expiring_soon']++;
                $stats['total_expiring_soon_documents'] += $summary['expiring_soon'];
            }

            if ($summary['expired'] === 0 && $summary['expiring_soon'] === 0 && $summary['total_documents'] > 0) {
                $stats['vehicles_all_valid']++;
            }
        }

        return response()->json($stats);
    }

    /**
     * API pour obtenir les détails des documents en temps réel avec jours restants
     */
    public function getRealtimeDocuments()
    {
        $vehicles = Vehicule::with(['documents', 'proprietaire.user'])->get();
        $documentsData = [];

        foreach ($vehicles as $vehicle) {
            foreach ($vehicle->documents as $document) {
                $status = $this->expirationService->getExpirationStatus($document);
                
                $documentsData[] = [
                    'id' => $document->id,
                    'vehicle_id' => $vehicle->id,
                    'vehicle_info' => [
                        'plaque' => $vehicle->plaque_immatriculation,
                        'marque' => $vehicle->marque,
                        'modele' => $vehicle->modele,
                        'proprietaire' => $vehicle->proprietaire->nom ?? 'N/A',
                        'proprietaire_email' => $vehicle->proprietaire->user->email ?? null
                    ],
                    'document_type' => $document->type,
                    'status' => $status['status'],
                    'days_remaining' => $status['days_remaining'],
                    'expiration_date' => $status['expiration_date'],
                    'is_notification_day' => $status['is_notification_day'],
                    'formatted_days' => $this->formatDaysRemaining($status['days_remaining'])
                ];
            }
        }

        return response()->json([
            'documents' => $documentsData,
            'timestamp' => now()->toISOString(),
            'total_count' => count($documentsData)
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
}
