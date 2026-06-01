<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Services\TesseractOCRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TesseractTestController extends Controller
{
    protected $ocrService;
    
    public function __construct(TesseractOCRService $ocrService)
    {
        $this->ocrService = $ocrService;
    }
    
    /**
     * Afficher le formulaire de test OCR
     */
    public function index()
    {
        $tesseractAvailable = $this->ocrService->isAvailable();
        $tesseractVersion = $this->ocrService->getVersion();
        
        return view('agent.tesseract-test', compact('tesseractAvailable', 'tesseractVersion'));
    }
    
    /**
     * Traiter l'image uploadée pour test OCR
     */
    public function test(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);
        
        try {
            // Sauvegarder l'image temporairement
            $image = $request->file('image');
            $imagePath = $image->store('temp/ocr', 'local');
            $fullPath = storage_path('app/' . $imagePath);
            
            // Analyser avec Tesseract
            $result = $this->ocrService->analyzeImageMultiple($fullPath);
            
            // Nettoyer le fichier temporaire
            Storage::disk('local')->delete($imagePath);
            
            return response()->json([
                'success' => true,
                'result' => $result,
                'tesseract_info' => [
                    'available' => $this->ocrService->isAvailable(),
                    'version' => $this->ocrService->getVersion()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Tester avec une image de démonstration
     */
    public function testDemo()
    {
        try {
            // Créer une image de test avec du texte
            $demoText = 'RN12345CD';
            $demoImagePath = $this->createDemoImage($demoText);
            
            // Analyser l'image de démonstration
            $result = $this->ocrService->analyzeImageMultiple($demoImagePath);
            
            // Nettoyer
            if (file_exists($demoImagePath)) {
                unlink($demoImagePath);
            }
            
            return response()->json([
                'success' => true,
                'demo_text' => $demoText,
                'result' => $result,
                'tesseract_info' => [
                    'available' => $this->ocrService->isAvailable(),
                    'version' => $this->ocrService->getVersion()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Créer une image de démonstration
     */
    protected function createDemoImage($text)
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('GD extension is required for demo image creation');
        }
        
        // Créer une image
        $width = 400;
        $height = 100;
        $image = imagecreatetruecolor($width, $height);
        
        // Couleurs
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fond blanc
        imagefill($image, 0, 0, $white);
        
        // Police (utiliser une police système si disponible)
        $font = 5; // Police système intégrée
        
        // Calculer la position pour centrer le texte
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        // Dessiner le texte
        imagestring($image, $font, $x, $y, $text, $black);
        
        // Sauvegarder l'image
        $tempPath = storage_path('app/temp/demo_' . time() . '.png');
        
        // Créer le répertoire si nécessaire
        $tempDir = dirname($tempPath);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        imagepng($image, $tempPath);
        imagedestroy($image);
        
        return $tempPath;
    }
}
