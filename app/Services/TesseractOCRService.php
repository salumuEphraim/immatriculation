<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TesseractOCRService
{
    protected $tesseract;
    protected $tempPath;
    
    public function __construct()
    {
        // Configuration du chemin Tesseract pour Windows
        $tesseractPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
        $tessdataPath = 'C:\Program Files\Tesseract-OCR\tessdata';
        
        $this->tesseract = new TesseractOCR();
        if (file_exists($tesseractPath)) {
            $this->tesseract->executable($tesseractPath);
        }
        if (is_dir($tessdataPath)) {
            putenv('TESSDATA_PREFIX=' . $tessdataPath);
        }
        
        $this->tempPath = storage_path('app/temp/ocr/');
        
        // Créer le répertoire temporaire s'il n'existe pas
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }
    
    /**
     * Analyser une image avec Tesseract OCR
     */
    public function analyzeImage($imagePath, array $options = [])
    {
        try {
            // Configuration par défaut
            $defaultOptions = [
                'psm' => 7, // Page segmentation mode
                'oem' => 3, // OCR engine mode
                'whitelist' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                'lang' => 'eng'
            ];
            
            $options = array_merge($defaultOptions, $options);
            
            // Prétraitement de l'image
            $processedImagePath = $this->preprocessImage($imagePath);
            
            // Configuration de Tesseract
            $this->tesseract->image($processedImagePath)
                         ->lang($options['lang'])
                         ->psm($options['psm'])
                         ->oem($options['oem'])
                         ->whitelist($options['whitelist']);
            
            // Exécution de l'OCR
            $result = $this->tesseract->run();
            
            // Nettoyage du résultat
            $cleanedResult = $this->cleanOCRResult($result);
            
            // Nettoyage des fichiers temporaires
            $this->cleanupTempFiles($processedImagePath);
            
            return [
                'success' => true,
                'text' => $cleanedResult,
                'confidence' => $this->estimateConfidence($cleanedResult),
                'raw_text' => $result,
                'metadata' => [
                    'psm' => $options['psm'],
                    'oem' => $options['oem'],
                    'lang' => $options['lang']
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'text' => '',
                'confidence' => 0
            ];
        }
    }
    
    /**
     * Analyse multiple avec différentes configurations
     */
    public function analyzeImageMultiple($imagePath)
    {
        $configurations = [
            ['psm' => 7, 'oem' => 3, 'whitelist' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'],
            ['psm' => 6, 'oem' => 3, 'whitelist' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'],
            ['psm' => 8, 'oem' => 3, 'whitelist' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'],
            ['psm' => 7, 'oem' => 1, 'whitelist' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'],
        ];
        
        $results = [];
        
        foreach ($configurations as $index => $config) {
            $result = $this->analyzeImage($imagePath, $config);
            $result['config_index'] = $index;
            $results[] = $result;
        }
        
        // Trier par confiance
        usort($results, function($a, $b) {
            return $b['confidence'] - $a['confidence'];
        });
        
        return [
            'best_result' => $results[0] ?? null,
            'all_results' => $results,
            'total_analyzed' => count($results)
        ];
    }
    
    /**
     * Prétraitement de l'image pour améliorer l'OCR
     */
    protected function preprocessImage($imagePath)
    {
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $tempFilename = 'ocr_' . time() . '_' . Str::random(8) . '.' . $extension;
        $tempPath = $this->tempPath . $tempFilename;
        
        try {
            // Vérifier si GD est disponible
            if (extension_loaded('gd')) {
                $image = null;
                
                // Charger l'image selon le format
                switch (strtolower($extension)) {
                    case 'jpg':
                    case 'jpeg':
                        $image = imagecreatefromjpeg($imagePath);
                        break;
                    case 'png':
                        $image = imagecreatefrompng($imagePath);
                        break;
                    case 'gif':
                        $image = imagecreatefromgif($imagePath);
                        break;
                    default:
                        // Copier le fichier si le format n'est pas supporté
                        copy($imagePath, $tempPath);
                        return $tempPath;
                }
                
                if ($image) {
                    // Convertir en niveaux de gris
                    imagefilter($image, IMG_FILTER_GRAYSCALE);
                    
                    // Améliorer le contraste
                    imagefilter($image, IMG_FILTER_CONTRAST, 50);
                    
                    // Augmenter la netteté
                    imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
                    imagefilter($image, IMG_FILTER_SELECTIVE_BLUR);
                    
                    // Sauvegarder l'image traitée
                    imagejpeg($image, $tempPath, 90);
                    imagedestroy($image);
                    
                    return $tempPath;
                }
            }
            
            // Fallback: copier le fichier original
            copy($imagePath, $tempPath);
            return $tempPath;
            
        } catch (\Exception $e) {
            // En cas d'erreur, copier le fichier original
            copy($imagePath, $tempPath);
            return $tempPath;
        }
    }
    
    /**
     * Nettoyer le résultat de l'OCR
     */
    protected function cleanOCRResult($text)
    {
        // Supprimer les espaces et caractères indésirables
        $text = preg_replace('/[^A-Z0-9]/', '', strtoupper($text));
        
        // Corriger les confusions courantes
        $corrections = [
            'B' => '8',
            'S' => '5',
            'Z' => '2',
            'O' => '0',
            'I' => '1',
            'G' => '6',
        ];
        
        foreach ($corrections as $wrong => $correct) {
            $text = str_replace($wrong, $correct, $text);
        }
        
        return trim($text);
    }
    
    /**
     * Estimer la confiance du résultat
     */
    protected function estimateConfidence($text)
    {
        $confidence = 0;
        
        // Longueur appropriée (6-10 caractères)
        if (strlen($text) >= 6 && strlen($text) <= 10) {
            $confidence += 30;
        }
        
        // Contient des chiffres et des lettres
        if (preg_match('/[0-9]/', $text) && preg_match('/[A-Z]/', $text)) {
            $confidence += 25;
        }
        
        // Pas de caractères répétés plus de 2 fois
        if (!preg_match('/(.)\1\1/', $text)) {
            $confidence += 20;
        }
        
        // Format plausible de plaque RDC
        if (preg_match('/^[A-Z]{2}[0-9]{3}[A-Z]{2}$/', $text) || 
            preg_match('/^[A-Z]{3}[0-9]{3}[A-Z]{2}$/', $text)) {
            $confidence += 25;
        }
        
        return min($confidence, 100);
    }
    
    /**
     * Nettoyer les fichiers temporaires
     */
    protected function cleanupTempFiles($processedImagePath)
    {
        if (file_exists($processedImagePath) && $processedImagePath !== $this->tempPath) {
            unlink($processedImagePath);
        }
    }
    
    /**
     * Vérifier si Tesseract est disponible
     */
    public function isAvailable()
    {
        try {
            $tesseractPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
            if (file_exists($tesseractPath)) {
                $version = shell_exec('"' . $tesseractPath . '" --version 2>&1');
                return !empty($version);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtenir la version de Tesseract
     */
    public function getVersion()
    {
        try {
            $tesseractPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
            if (file_exists($tesseractPath)) {
                return shell_exec('"' . $tesseractPath . '" --version 2>&1');
            }
            return 'Non disponible';
        } catch (\Exception $e) {
            return 'Non disponible';
        }
    }

    public function getStatus(): array
    {
        return [
            'available' => $this->isAvailable(),
            'version' => $this->getVersion(),
            'temp_path' => $this->tempPath,
        ];
    }
}
