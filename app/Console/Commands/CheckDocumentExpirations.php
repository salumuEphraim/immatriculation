<?php

namespace App\Console\Commands;

use App\Services\DocumentExpirationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDocumentExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les documents expirants et envoie les notifications automatiques';

    /**
     * Execute the console command.
     */
    public function handle(DocumentExpirationService $expirationService): int
    {
        $this->info('Début de la vérification des expirations de documents...');
        
        try {
            $notifications = $expirationService->processExpirationNotifications();
            
            if (empty($notifications)) {
                $this->info('Aucune notification à envoyer aujourd\'hui.');
                Log::info('Vérification des expirations terminée - aucune notification requise');
            } else {
                $this->info(count($notifications) . ' notifications traitées:');
                
                foreach ($notifications as $notification) {
                    $type = match($notification['type']) {
                        'reminder' => '🔔 Rappel',
                        'expired' => '⚠️ Expiré',
                        default => '📋 Notification'
                    };
                    
                    $this->line("  {$type}: {$notification['message']}");
                }
                
                Log::info('Vérification des expirations terminée', [
                    'notifications_count' => count($notifications),
                    'notifications' => $notifications
                ]);
            }
            
            $this->info('Vérification terminée avec succès.');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Erreur lors de la vérification des expirations: ' . $e->getMessage());
            Log::error('Erreur lors de la vérification des expirations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
