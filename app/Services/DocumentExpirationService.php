<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Vehicule;
use App\Notifications\DocumentExpirationNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DocumentExpirationService
{
    /**
     * Calcule les jours restants avant l'expiration d'un document
     */
    public function calculateDaysRemaining(Document $document): int
    {
        if (!$document->date_expiration) {
            return 0;
        }

        return Carbon::now()->diffInDays($document->date_expiration, false);
    }

    /**
     * Vérifie si un document expire dans 30 jours (exactement aujourd'hui + 30 jours)
     */
    public function expiresInThirtyDays(Document $document): bool
    {
        if (!$document->date_expiration) {
            return false;
        }

        $expirationDate = Carbon::parse($document->date_expiration);
        $notificationDate = Carbon::now()->addDays(30);

        return $expirationDate->isSameDay($notificationDate);
    }

    /**
     * Vérifie si un document est déjà expiré
     */
    public function isExpired(Document $document): bool
    {
        if (!$document->date_expiration) {
            return false;
        }

        return Carbon::now()->isAfter($document->date_expiration);
    }

    /**
     * Vérifie si un document expire bientôt (dans les 30 prochains jours)
     */
    public function expiresSoon(Document $document): bool
    {
        if (!$document->date_expiration) {
            return false;
        }

        $daysRemaining = $this->calculateDaysRemaining($document);
        return $daysRemaining > 0 && $daysRemaining <= 30;
    }

    /**
     * Retourne la date d'expiration par défaut en fonction du type de document
     */
    public function getExpirationDateByType(string $type, ?int $assuranceDurationMonths = null): string
    {
        $now = Carbon::now();

        return match ($type) {
            'assurance' => $now->copy()->addMonths($assuranceDurationMonths ?? 12)->toDateString(),
            'controle_technique' => $now->copy()->addMonths(6)->toDateString(),
            'vignette' => $now->copy()->addYear()->toDateString(),
            'carte_rose' => $now->copy()->addYears(5)->toDateString(),
            default => $now->copy()->addYear()->toDateString(),
        };
    }

    /**
     * Obtient le statut d'expiration d'un document
     */
    public function getExpirationStatus(Document $document): array
    {
        $daysRemaining = $this->calculateDaysRemaining($document);

        return [
            'status' => $this->isExpired($document) ? 'expired' :
                       ($this->expiresSoon($document) ? 'expiring_soon' : 'valid'),
            'days_remaining' => $daysRemaining,
            'expiration_date' => $document->date_expiration ? $document->date_expiration->format('d/m/Y') : null,
            'notification_message' => $this->generateAlertMessage($document, $daysRemaining),
            'is_notification_day' => $daysRemaining >= 0 && $daysRemaining <= 7,
        ];
    }

    /**
     * Traite tous les documents qui nécessitent une notification
     */
    public function processExpirationNotifications(): array
    {
        $notifications = [];
        $documents = Document::with(['vehicule.proprietaire.user'])->get();

        foreach ($documents as $document) {
            $status = $this->getExpirationStatus($document);

            if ($status['days_remaining'] >= 0 && $status['days_remaining'] <= 7) {
                $result = $this->sendExpirationNotification($document, 'alert');
                if ($result) {
                    $notifications[] = [
                        'type' => 'alert',
                        'document' => $document,
                        'message' => "Alerte envoyée pour {$document->type} ({$status['days_remaining']} jours restants)"
                    ];
                }
            }

            if ($status['status'] === 'expired') {
                $result = $this->sendExpirationNotification($document, 'expired');
                if ($result) {
                    $notifications[] = [
                        'type' => 'expired',
                        'document' => $document,
                        'message' => "Alerte d'expiration envoyée pour {$document->type}"
                    ];
                }
            }
        }

        return $notifications;
    }

    /**
     * Envoie une notification d'expiration par email ou SMS de secours
     */
    public function sendExpirationNotification(Document $document, string $type): bool
    {
        try {
            $proprietaire = $document->vehicule?->proprietaire;
            $email = $proprietaire?->user?->email;
            $phone = $proprietaire?->telephone ?? $proprietaire?->user?->telephone;
            $daysRemaining = $this->calculateDaysRemaining($document);

            if ($email) {
                Notification::route('mail', $email)
                    ->notify(new DocumentExpirationNotification($document, $daysRemaining, $type));

                Log::info("Notification d'expiration envoyée par email", [
                    'document_id' => $document->id,
                    'email' => $email,
                    'type' => $type,
                    'days_remaining' => $daysRemaining,
                ]);

                return true;
            }

            if ($phone) {
                Log::info("Notification d'expiration par SMS de secours", [
                    'document_id' => $document->id,
                    'phone' => $phone,
                    'type' => $type,
                    'message' => $this->generateAlertMessage($document, $daysRemaining),
                ]);

                return true;
            }

            Log::warning("Aucun contact disponible pour la notification d'expiration", [
                'document_id' => $document->id,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de la notification d'expiration", [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Génère le sujet de l'email selon le type de notification
     */
    private function generateEmailSubject(Document $document, string $type): string
    {
        $vehicleInfo = $document->vehicule ? 
            "Véhicule {$document->vehicule->marque} {$document->vehicule->modele} ({$document->vehicule->plaque_immatriculation})" : 
            "Véhicule inconnu";

        return match($type) {
            'alert' => "Attention: {$document->type} expire bientôt - {$vehicleInfo}",
            'expired' => "⚠️ URGENT: {$document->type} expiré - {$vehicleInfo}",
            default => "Notification d'expiration pour {$document->type}"
        };
    }

    /**
     * Génère le contenu de l'email
     */
    private function generateEmailContent(Document $document, string $type, int $daysRemaining): string
    {
        $message = $this->generateAlertMessage($document, $daysRemaining);
        $expirationDate = $document->date_expiration?->format('d/m/Y') ?? 'Inconnue';
        $vehicleInfo = $document->vehicule ? "{$document->vehicule->marque} {$document->vehicule->modele}" : "Véhicule inconnu";
        $plaque = $document->vehicule?->plaque_immatriculation ?? 'Inconnue';
        $proprietaire = $document->vehicule?->proprietaire?->nom ?? 'Propriétaire';

        return implode("\n", [
            "Cher/Chère {$proprietaire},",
            "",
            $message,
            "",
            "Détails du document:",
            "- Document: " . ucfirst(str_replace('_', ' ', $document->type)),
            "- Véhicule: {$vehicleInfo}",
            "- Plaque: {$plaque}",
            "- Date d'expiration: {$expirationDate}",
            "",
            "Merci de prendre les dispositions nécessaires pour le renouvellement.",
            "",
            "Cordialement,",
            "Le Service des Immatriculations de Lubumbashi",
        ]);
    }

    /**
     * Génère le message d'alerte standard pour email/SMS
     */
    private function generateAlertMessage(Document $document, int $daysRemaining): string
    {
        $documentName = ucfirst(str_replace('_', ' ', $document->type));
        $plaque = $document->vehicule?->plaque_immatriculation ?? 'inconnue';
        $daysText = $daysRemaining <= 1 ? '1 jour' : "{$daysRemaining} jours";

        return "Attention, votre {$documentName} pour le véhicule {$plaque} expire dans {$daysText}. Pensez au renouvellement.";
    }

    /**
     * Vérifie un document lors de son enregistrement et envoie les notifications appropriées
     */
    public function checkDocumentOnCreation(Document $document): array
    {
        $notifications = [];
        $status = $this->getExpirationStatus($document);

        if ($status['days_remaining'] >= 0 && $status['days_remaining'] <= 7) {
            $result = $this->sendExpirationNotification($document, 'alert');
            if ($result) {
                $notifications[] = [
                    'type' => 'alert_immediate',
                    'message' => "Alerte envoyée pour '{$document->type}' lors de l'enregistrement"
                ];
            }
        }

        if ($status['status'] === 'expired') {
            $result = $this->sendExpirationNotification($document, 'expired');
            if ($result) {
                $notifications[] = [
                    'type' => 'expired_immediate',
                    'message' => "Alerte immédiate: Document '{$document->type}' déjà expiré"
                ];
            }
        }

        return $notifications;
    }

    /**
     * Obtient le résumé des documents expirants pour un véhicule
     */
    public function getVehicleExpirationSummary(Vehicule $vehicule): array
    {
        $documents = $vehicule->documents;
        $summary = [
            'total_documents' => $documents->count(),
            'expired' => 0,
            'expiring_soon' => 0,
            'valid' => 0,
            'details' => []
        ];

        foreach ($documents as $document) {
            $status = $this->getExpirationStatus($document);
            
            $summary[$status['status']]++;
            $summary['details'][] = [
                'type' => $document->type,
                'status' => $status['status'],
                'days_remaining' => $status['days_remaining'],
                'expiration_date' => $status['expiration_date']
            ];
        }

        return $summary;
    }

    /**
     * Envoie une alerte de test au propriétaire via email ou SMS de secours.
     */
    public function sendTestExpirationNotification(Vehicule $vehicule, string $type = 'assurance'): bool
    {
        $vehicule->loadMissing('proprietaire.user');
        $proprietaire = $vehicule->proprietaire;
        $email = $proprietaire?->user?->email ?? $proprietaire?->email;
        $phone = $proprietaire?->telephone;

        $document = new Document([
            'vehicule_id' => $vehicule->id,
            'type' => $type,
            'date_emission' => Carbon::now(),
            'date_expiration' => Carbon::today(),
            'numero_plaque' => $vehicule->plaque_immatriculation,
            'serie' => 'TEST-ALERTE',
        ]);
        $document->setRelation('vehicule', $vehicule);

        $daysRemaining = 0;
        $sent = false;

        if ($email) {
            Notification::route('mail', $email)
                ->notify(new DocumentExpirationNotification($document, $daysRemaining, 'alert'));

            Log::info('Alerte de test d\'expiration envoyée par email', [
                'vehicule_id' => $vehicule->id,
                'document_type' => $type,
                'email' => $email,
            ]);

            $sent = true;
        }

        if (!$sent && $phone) {
            Log::info('Alerte de test d\'expiration par SMS de secours (log)', [
                'vehicule_id' => $vehicule->id,
                'document_type' => $type,
                'phone' => $phone,
            ]);
            $sent = true;
        }

        return $sent;
    }
}
