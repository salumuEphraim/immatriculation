<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\DocumentExpirationController;
use App\Http\Controllers\Admin\InfractionController as AdminInfractionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehiculeController;
use App\Http\Controllers\Agent\InfractionController as AgentInfractionController;
use App\Http\Controllers\Agent\SearchController;
use App\Http\Controllers\Agent\TesseractTestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhooks/flexpay', \App\Http\Controllers\Webhooks\FlexpayWebhookController::class)
    ->name('webhooks.flexpay');

Route::post('/webhooks/shwary', \App\Http\Controllers\Webhooks\ShwaryWebhookController::class)
    ->name('webhooks.shwary');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin,agent,proprietaire'])->group(function () {
    Route::prefix('recherche')->group(function () {
        Route::get('/', [SearchController::class, 'index'])
            ->middleware('role:admin,agent')
            ->name('agent.recherche');

        Route::post('/scan', [SearchController::class, 'scan'])
            ->middleware('role:admin,agent')
            ->name('shared.scan');

        Route::get('/resultat/{plaque}', [SearchController::class, 'showResult'])->name('shared.resultat');
    });

    Route::get('/infractions/recu/{infraction}', [AgentInfractionController::class, 'showRecu'])
        ->name('agent.infractions.recu');
});

Route::middleware(['auth', 'role:admin,agent'])->prefix('tesseract')->name('tesseract.')->group(function () {
    Route::get('/test', [TesseractTestController::class, 'index'])->name('test');
    Route::post('/test', [TesseractTestController::class, 'test'])->name('test.submit');
    Route::get('/demo', [TesseractTestController::class, 'testDemo'])->name('demo');
});

Route::middleware(['auth', 'role:admin,agent'])->group(function () {
    Route::post('/infractions/store', [AgentInfractionController::class, 'store'])->name('agent.infractions.store');

    Route::post('/infractions/{infraction}/envoyer-email', [AgentInfractionController::class, 'envoyerEmail'])
        ->name('agent.infractions.email');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/utilisateurs', 'index')->name('users.index');
        Route::patch('/utilisateurs/{user}/role', 'updateRole')->name('users.updateRole');
        Route::delete('/utilisateurs/{user}', 'destroy')->name('users.destroy');
    });

    Route::post('/accounts/store', [AccountController::class, 'store'])->name('accounts.store');

    Route::controller(VehiculeController::class)->group(function () {
        Route::get('/vehicules', 'index')->name('vehicules.index');
        Route::post('/vehicules/store', 'store')->name('vehicules.store');
        Route::get('/vehicules/{vehicule}/edit', 'edit')->name('vehicules.edit');
        Route::put('/vehicules/{vehicule}', 'update')->name('vehicules.update');
    });

    Route::controller(AdminInfractionController::class)->group(function () {
        Route::get('/infractions', 'index')->name('infractions.index');
        Route::patch('/infractions/{infraction}/valider', 'valider')->name('infractions.valider');
        Route::patch('/infractions/{infraction}/statut', 'updateStatut')->name('infractions.updateStatut');
        Route::get('/rapports/infractions', 'genererRapport')->name('rapports.infractions');
    });

    Route::controller(DocumentExpirationController::class)->prefix('expirations')->name('expirations.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/process', 'processNotifications')->name('process');
        Route::get('/stats', 'getStats')->name('stats');
        Route::get('/realtime', 'getRealtimeDocuments')->name('realtime');
        Route::get('/vehicles/{vehicule}', 'showVehicle')->name('show');
    });
});

Route::middleware(['auth', 'role:agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/recherche-manuelle', [SearchController::class, 'manualSearch'])->name('recherche.manuelle');
    Route::get('/mes-infractions', [AgentInfractionController::class, 'index'])->name('infractions.index');

    // Diagnostic (connexion micro-service Python)
    Route::get('/scanner/diagnostic', [\App\Http\Controllers\Agent\OcrDiagnosticsController::class, 'ping'])->name('scanner.diagnostic');

    // Scanner OCR (micro-service Python PaddleOCR)
    Route::get('/scanner', [\App\Http\Controllers\Agent\OcrController::class, 'showScanner'])->name('scanner');
    Route::get('/scanner/resultat/{plaque}', [\App\Http\Controllers\Agent\OcrController::class, 'showScannerResult'])->name('scanner.resultat');
    Route::post('/scanner/process', [\App\Http\Controllers\Agent\OcrController::class, 'processScanner'])->name('scanner.process');
    Route::post('/scanner/process-json', [\App\Http\Controllers\Agent\OcrController::class, 'processScannerJson'])->name('scanner.process-json');

    // Routes pour le scanner OCR avancé
    Route::get('/scanner-avance', [\App\Http\Controllers\Agent\AdvancedScanController::class, 'index'])->name('scanner.avance');
    Route::post('/advanced-scan', [\App\Http\Controllers\Agent\AdvancedScanController::class, 'scan'])->name('scanner.advanced');
    Route::post('/live-scan', [\App\Http\Controllers\Agent\AdvancedScanController::class, 'scanLive'])->name('scanner.live');
    Route::get('/scanner-formats', [\App\Http\Controllers\Agent\AdvancedScanController::class, 'getFormats'])->name('scanner.formats');
});

Route::middleware(['auth', 'role:proprietaire'])->prefix('proprietaire')->name('proprietaire.')->group(function () {
    Route::get('/mes-vehicules', [DashboardController::class, 'myVehicles'])->name('vehicules');
    Route::post('/mes-vehicules/test-notification', [DashboardController::class, 'sendTestNotification'])->name('vehicules.testNotification');
    Route::get('/mes-vehicules/realtime', [DashboardController::class, 'getMyVehiclesRealtimeDocuments'])->name('vehicules.realtime');
    Route::get('/mes-infractions', [DashboardController::class, 'proprietaireInfractions'])->name('infractions');
    Route::post('/mes-infractions/{contravention}/payer-mobile-money', [DashboardController::class, 'payerAmendeMobileMoney'])
        ->name('infractions.payer');
});

Route::middleware(['auth', 'role:agent'])->group(function () {
    Route::get('/infractions/{infraction}/pdf', [AgentInfractionController::class, 'downloadPdf'])
        ->name('agent.infractions.pdf');
});

require __DIR__ . '/auth.php';
