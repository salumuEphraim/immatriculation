<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\DocumentExpirationService;
use Illuminate\Support\Facades\Log;

class DocumentObserver
{
    protected DocumentExpirationService $expirationService;

    public function __construct(DocumentExpirationService $expirationService)
    {
        $this->expirationService = $expirationService;
    }

    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        Log::info('Nouveau document créé', [
            'document_id' => $document->id,
            'type' => $document->type,
            'vehicule_id' => $document->vehicule_id,
            'date_expiration' => $document->date_expiration
        ]);

        // Vérifier immédiatement le document et envoyer les notifications si nécessaire
        $notifications = $this->expirationService->checkDocumentOnCreation($document);

        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                Log::info('Notification traitée lors de la création du document', [
                    'document_id' => $document->id,
                    'type' => $notification['type'],
                    'message' => $notification['message']
                ]);
            }
        }
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        // Si la date d'expiration a été modifiée, revérifier
        if ($document->wasChanged('date_expiration')) {
            Log::info('Date d\'expiration modifiée', [
                'document_id' => $document->id,
                'type' => $document->type,
                'old_date' => $document->getOriginal('date_expiration'),
                'new_date' => $document->date_expiration
            ]);

            $notifications = $this->expirationService->checkDocumentOnCreation($document);

            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    Log::info('Notification traitée après mise à jour du document', [
                        'document_id' => $document->id,
                        'type' => $notification['type'],
                        'message' => $notification['message']
                    ]);
                }
            }
        }
    }
}
