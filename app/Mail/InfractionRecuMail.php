<?php

namespace App\Mail;

use App\Models\Contravention;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InfractionRecuMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contravention;
    public $pdfContent;

    public function __construct(Contravention $contravention, $pdfContent)
    {
        $this->contravention = $contravention;
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this->subject("RoadShield - Recu de contravention {$this->contravention->code_unique}")
                    ->view('emails.infraction_recu')
                    ->attachData($this->pdfContent, 'recu_roadshield.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
