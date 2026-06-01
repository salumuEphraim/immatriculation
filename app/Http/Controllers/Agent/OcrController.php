<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use App\Services\BrokerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrController extends Controller
{
    public function showScanner()
    {
        return view('agent.scanner');
    }

    public function processScanner(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            $result = $this->analyzeUploadedFile($validated['file']);

            if (! ($result['success'] ?? false)) {
                return back()->with('error', $result['error'] ?? 'Plaque illisible. Essayez une photo plus nette et bien cadree.');
            }

            return redirect()->route('agent.scanner.resultat', ['plaque' => $result['plate']]);
        } catch (\Throwable $e) {
            Log::error('Erreur pendant le scan OCR (PaddleOCR)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Impossible de contacter le service OCR Python. Verifiez le serveur et reessayez.');
        }
    }

    public function processScannerJson(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            $result = $this->analyzeUploadedFile($validated['file']);

            if (! ($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Plaque non detectee.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'plate' => $result['plate'],
                'confidence' => $result['confidence'] ?? 0,
                'method' => $result['method'] ?? 'unknown',
                'redirect_url' => route('agent.scanner.resultat', ['plaque' => $result['plate']]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur pendant le scan OCR automatique', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Service OCR indisponible.',
            ], 503);
        }
    }

    private function analyzeUploadedFile($file): array
    {
        $pythonServiceUrl = (string) config('services.ocr_python.url');
        $timeout = (int) config('services.ocr_python.timeout', 60);
        $connectTimeout = (int) config('services.ocr_python.connect_timeout', 10);
        $useOpenAI = (bool) config('services.ocr_python.use_openai', true);

        if (function_exists('set_time_limit')) {
            set_time_limit(max($timeout + 30, 90));
        }

        $response = Http::timeout($timeout)
            ->connectTimeout($connectTimeout)
            ->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName() ?: 'plaque.jpg'
            )
            ->post($pythonServiceUrl . '?' . http_build_query(['use_openai' => $useOpenAI ? 'true' : 'false']));

        if (! $response->successful()) {
            if ($response->status() === 422) {
                return [
                    'success' => false,
                    'error' => 'Plaque non detectee.',
                    'payload' => $response->json(),
                ];
            }

            Log::error('Micro-service PaddleOCR a echoue', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Le service OCR n a pas pu traiter l image.');
        }

        $json = $response->json();

        $status = $json['status'] ?? null;
        $plaqueBrute = $json['plaque'] ?? ($json['result'] ?? null);
        $confidence = (float) ($json['confidence'] ?? 0);
        $method = (string) ($json['method'] ?? 'unknown');

        if ($status !== 'success' || ! is_string($plaqueBrute) || trim($plaqueBrute) === '') {
            Log::warning('Reponse OCR invalide', ['json' => $json]);

            return [
                'success' => false,
                'error' => 'Plaque illisible. Essayez une photo plus nette et bien cadree.',
            ];
        }

        $plaqueNettoyee = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $plaqueBrute));

        if (strlen($plaqueNettoyee) < 3) {
            return [
                'success' => false,
                'error' => 'La plaque detectee est trop courte ou ambigue.',
            ];
        }

        Log::info('Scan OCR reussi', [
            'plate' => $plaqueNettoyee,
            'confidence' => $confidence,
            'method' => $method,
        ]);

        return [
            'success' => true,
            'plate' => $plaqueNettoyee,
            'confidence' => $confidence,
            'method' => $method,
            'payload' => $json,
        ];
    }

    public function showScannerResult(string $plaque)
    {
        $numeroPlaque = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $plaque));

        $vehicule = Vehicule::with(['proprietaire.user', 'contraventions', 'documents'])
            ->get()
            ->first(function (Vehicule $vehicule) use ($numeroPlaque) {
                $current = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $vehicule->plaque_immatriculation ?? ''));

                return $current === $numeroPlaque;
            });

        if (! $vehicule) {
            Log::info('Scan OCR sans vehicule repertorie', ['plaque' => $numeroPlaque]);

            return redirect()
                ->route('agent.scanner')
                ->with('error', "Vehicule [$numeroPlaque] non repertorie.");
        }

        $this->checkDocuments($vehicule);

        $brokerData = app(BrokerService::class)->verifyAll($numeroPlaque);
        $plaqueNumero = $vehicule->plaque_immatriculation;
        $backRoute = 'agent.scanner';
        $backLabel = 'Nouveau scan';

        return view('agent.recherche_resultat', compact('vehicule', 'plaqueNumero', 'brokerData', 'backRoute', 'backLabel'));
    }

    private function checkDocuments(Vehicule $vehicule): void
    {
        $requiredTypes = ['assurance', 'vignette', 'controle_technique', 'carte_rose'];
        $existingTypes = $vehicule->documents()->pluck('type')->toArray();
        $missingTypes = array_diff($requiredTypes, $existingTypes);
        $expired = $vehicule->documents()
            ->whereIn('type', $requiredTypes)
            ->where('date_expiration', '<', now());

        $issues = [];

        if (! empty($missingTypes)) {
            $missingList = collect($missingTypes)
                ->map(fn ($type) => ucfirst(str_replace('_', ' ', $type)))
                ->implode(', ');
            $issues[] = "Documents manquants : $missingList";
        }

        if ($expired->count() > 0) {
            $expiredList = $expired->pluck('type')
                ->map(fn ($type) => ucfirst(str_replace('_', ' ', $type)))
                ->implode(', ');
            $issues[] = "Documents expires : $expiredList";
        }

        if (! empty($issues)) {
            session()->now('error', implode(' | ', $issues) . ' - Vehicule en infraction.');
            return;
        }

        session()->now('success', 'Le vehicule est en regle.');
    }
}
