<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use App\Services\AdvancedOCRService;
use App\Services\BrokerService;
use App\Services\TesseractOCRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdvancedScanController extends Controller
{
    public function __construct(
        private readonly AdvancedOCRService $ocrService,
        private readonly TesseractOCRService $tesseractService
    ) {
    }

    public function index()
    {
        return view('agent.scanner-avance', [
            'supportedFormats' => $this->ocrService->getSupportedFormats(),
            'openAIEnabled' => $this->canUseAdvancedOcr(),
            'tesseractEnabled' => $this->tesseractService->isAvailable(),
            'tesseractStatus' => $this->tesseractService->getStatus(),
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,bmp|max:5120',
            'use_openai' => 'boolean',
        ]);

        $imagePath = null;

        try {
            $user = Auth::user();
            $imagePath = $request->file('image')->store('scans/temp', 'local');
            $fullPath = Storage::disk('local')->path($imagePath);

            $ocrResult = $this->analyzeImage($fullPath, $request->boolean('use_openai', true));

            if (!($ocrResult['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Impossible de lire la plaque d\'immatriculation.',
                    'details' => $ocrResult['error'] ?? 'Image illisible.',
                    'method' => $ocrResult['method'] ?? 'unknown',
                ], 400);
            }

            $plateNumber = $this->normalizePlate($ocrResult['plate_number'] ?? $ocrResult['text'] ?? '');
            $vehicule = $this->findVehicle($plateNumber);

            $this->logScanAttempt($user, $plateNumber, $ocrResult, $vehicule);

            if (!$vehicule) {
                return response()->json([
                    'success' => false,
                    'plate_number' => $plateNumber,
                    'confidence' => $ocrResult['confidence'] ?? 0,
                    'method' => $ocrResult['method'] ?? 'unknown',
                    'format' => $ocrResult['format_detected'] ?? 'UNKNOWN',
                    'message' => "Vehicule [{$plateNumber}] non repertorie dans la base de donnees.",
                    'vehicle_found' => false,
                ], 404);
            }

            $documentStatus = $this->checkDocuments($vehicule);
            $brokerData = app(BrokerService::class)->verifyAll($plateNumber);

            return response()->json([
                'success' => true,
                'plate_number' => $plateNumber,
                'confidence' => $ocrResult['confidence'] ?? 0,
                'method' => $ocrResult['method'] ?? 'unknown',
                'format' => $ocrResult['format_detected'] ?? 'UNKNOWN',
                'vehicle' => [
                    'id' => $vehicule->id,
                    'plaque_immatriculation' => $vehicule->plaque_immatriculation,
                    'marque' => $vehicule->marque,
                    'modele' => $vehicule->modele,
                    'couleur' => $vehicule->couleur,
                    'proprietaire' => [
                        'nom' => $vehicule->proprietaire->nom ?? 'N/A',
                        'prenom' => $vehicule->proprietaire->prenom ?? '',
                        'email' => $vehicule->proprietaire->user->email ?? 'N/A',
                    ],
                ],
                'documents' => $documentStatus,
                'broker_data' => $brokerData,
                'redirect_url' => route('agent.scanner.resultat', ['plaque' => $plateNumber]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur lors du scan avance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur technique lors du traitement de l\'image.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } finally {
            if ($imagePath) {
                Storage::disk('local')->delete($imagePath);
            }
        }
    }

    public function scanLive(Request $request)
    {
        $request->validate([
            'image_data' => 'required|string',
            'use_openai' => 'boolean',
        ]);

        $tempPath = null;

        try {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('image_data'));
            $imageBytes = base64_decode($imageData, true);

            if ($imageBytes === false) {
                return response()->json([
                    'success' => false,
                    'error' => 'Donnees image invalides.',
                ], 400);
            }

            $tempPath = 'scans/live_' . uniqid('', true) . '.jpg';
            Storage::disk('local')->put($tempPath, $imageBytes);
            $fullPath = Storage::disk('local')->path($tempPath);

            $ocrResult = $this->analyzeImage($fullPath, $request->boolean('use_openai', true));

            return response()->json([
                'success' => (bool) ($ocrResult['success'] ?? false),
                'plate_number' => $this->normalizePlate($ocrResult['plate_number'] ?? $ocrResult['text'] ?? ''),
                'confidence' => $ocrResult['confidence'] ?? 0,
                'method' => $ocrResult['method'] ?? 'unknown',
                'format' => $ocrResult['format_detected'] ?? 'UNKNOWN',
                'candidates' => $ocrResult['candidates'] ?? [],
                'error' => ($ocrResult['success'] ?? false) ? null : ($ocrResult['error'] ?? 'Plaque non detectee.'),
            ], ($ocrResult['success'] ?? false) ? 200 : 422);
        } catch (\Throwable $e) {
            Log::error('Erreur lors du scan live', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur technique.',
            ], 500);
        } finally {
            if ($tempPath) {
                Storage::disk('local')->delete($tempPath);
            }
        }
    }

    public function getFormats()
    {
        return response()->json([
            'formats' => $this->ocrService->getSupportedFormats(),
            'openai_enabled' => $this->canUseAdvancedOcr(),
            'tesseract_enabled' => $this->tesseractService->isAvailable(),
        ]);
    }

    private function analyzeImage(string $path, bool $useOpenAI): array
    {
        if ($useOpenAI && $this->canUseAdvancedOcr()) {
            return $this->ocrService->analyzeImage($path);
        }

        $result = $this->tesseractService->analyzeImageMultiple($path);
        $best = $result['best_result'] ?? [];

        return [
            'success' => (bool) ($best['success'] ?? false) && !empty($best['text']),
            'plate_number' => $best['text'] ?? null,
            'confidence' => $best['confidence'] ?? 0,
            'method' => 'tesseract',
            'all_results' => $result['all_results'] ?? [],
        ];
    }

    private function canUseAdvancedOcr(): bool
    {
        return $this->ocrService->isOpenAIConfigured()
            && class_exists(\Intervention\Image\Facades\Image::class);
    }

    private function findVehicle(string $plateNumber): ?Vehicule
    {
        $cleanPlate = $this->normalizePlate($plateNumber);

        return Vehicule::with(['proprietaire.user', 'contraventions', 'documents'])
            ->whereRaw('UPPER(REPLACE(REPLACE(plaque_immatriculation, "-", ""), "/", "")) = ?', [$cleanPlate])
            ->first();
    }

    private function checkDocuments(Vehicule $vehicule): array
    {
        $requiredTypes = array_keys(Vehicule::REQUIRED_DOCUMENT_TYPES);
        $existingTypes = $vehicule->documents->pluck('type')->all();
        $missingTypes = array_values(array_diff($requiredTypes, $existingTypes));
        $expired = $vehicule->documents
            ->whereIn('type', $requiredTypes)
            ->filter(fn ($document) => $document->date_expiration && $document->date_expiration->isPast())
            ->values();

        return [
            'en_regle' => empty($missingTypes) && $expired->isEmpty(),
            'missing' => $missingTypes,
            'expired' => $expired->map(fn ($document) => [
                'type' => $document->type,
                'date_expiration' => $document->date_expiration?->format('d/m/Y'),
            ])->all(),
            'missing_count' => count($missingTypes),
            'expired_count' => $expired->count(),
            'total_required' => count($requiredTypes),
        ];
    }

    private function normalizePlate(?string $plate): string
    {
        return strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $plate ?? ''));
    }

    private function logScanAttempt($user, string $plateNumber, array $ocrResult, ?Vehicule $vehicule = null): void
    {
        Log::info('Tentative de scan OCR', [
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'plate_detected' => $plateNumber,
            'ocr_method' => $ocrResult['method'] ?? 'unknown',
            'ocr_confidence' => $ocrResult['confidence'] ?? 0,
            'vehicle_found' => $vehicule !== null,
            'vehicle_id' => $vehicule?->id,
        ]);
    }
}
