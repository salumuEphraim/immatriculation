<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpirationNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Document $document,
        protected int $daysRemaining,
        protected string $type,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $documentName = ucfirst(str_replace('_', ' ', $this->document->type));
        $plaque = $this->document->vehicule?->plaque_immatriculation ?? 'inconnue';
        $daysText = $this->daysRemaining <= 1 ? '1 jour' : "{$this->daysRemaining} jours";

        $message = "Attention, votre {$documentName} pour le véhicule {$plaque} expire dans {$daysText}. Pensez au renouvellement.";

        $mail = (new MailMessage)
            ->subject("Attention : votre {$documentName} expire dans {$daysText}")
            ->line($message)
            ->line('Détails du document :')
            ->line('Document : ' . $documentName)
            ->line('Plaque : ' . $plaque)
            ->line('Date d\'expiration : ' . ($this->document->date_expiration?->format('d/m/Y') ?? 'Inconnue'))
            ->line('Merci de prendre les dispositions nécessaires pour le renouvellement.');

        if ($this->type === 'expired') {
            $mail->line('Ce document est déjà expiré. Veuillez réagir immédiatement.');
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'type' => $this->document->type,
            'plaque' => $this->document->vehicule?->plaque_immatriculation,
            'days_remaining' => $this->daysRemaining,
            'notification_type' => $this->type,
        ];
    }
}
