<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class AdvancedOCRService
{
    private string $openaiApiKey;
    private array $plateFormats;

    public function __construct()
    {
        $this->openaiApiKey = (string) (config('services.openai.api_key') ?? env('OPENAI_API_KEY') ?? '');
        $this->plateFormats = [
            'RDC_STANDARD' => [
                'pattern' => '/^[A-Z]{2}\d{3}[A-Z]{2}$/',
                'examples' => ['AB123CD', 'XY456ZA', 'KM789GH'],
                'description' => 'Format standard RDC: 2 lettres + 3 chiffres + 2 lettres',
                'priority' => 1
            ],
            'RDC_NEW' => [
                'pattern' => '/^[A-Z]{2}\d{4}[A-Z]{2}$/',
                'examples' => ['AB1234CD', 'XY5678ZA'],
                'description' => 'Format RDC récent: 2 lettres + 4 chiffres + 2 lettres',
                'priority' => 2
            ],
            'RDC_OLD' => [
                'pattern' => '/^[A-Z]{1,2}\d{4,6}$/',
                'examples' => ['A12345', 'AB12345', 'K123456'],
                'description' => 'Format RDC ancien: 1-2 lettres + 4-6 chiffres',
                'priority' => 3
            ],
            'RDC_COMMERCIAL' => [
                'pattern' => '/^[A-Z]{3}\d{3,4}[A-Z]$/',
                'examples' => ['COM1234A', 'BUS5678B', 'TAX9012C'],
                'description' => 'Format commercial: 3 lettres + 3-4 chiffres + 1 lettre',
                'priority' => 4
            ],
            'RDC_MOTO' => [
                'pattern' => '/^[A-Z]{2}\d{3,4}[A-Z]$/',
                'examples' => ['MO123A', 'MC4567B', 'MT7890C'],
                'description' => 'Format moto: 2 lettres + 3-4 chiffres + 1 lettre',
                'priority' => 5
            ],
            'DIPLOMATIQUE' => [
                'pattern' => '/^[A-Z]{2,3}\d{3,4}[A-Z]{0,2}$/',
                'examples' => ['CD1234', 'CMD5678', 'DIP9012AB'],
                'description' => 'Format diplomatique: 2-3 lettres + 3-4 chiffres + 0-2 lettres',
                'priority' => 6
            ],
            'GOUVERNEMENTAL' => [
                'pattern' => '/^[A-Z]{3}\d{3,4}$/',
                'examples' => ['GOV1234', 'MIN5678', 'PUB9012'],
                'description' => 'Format gouvernemental: 3 lettres + 3-4 chiffres',
                'priority' => 7
            ],
            'TEMPORAIRE' => [
                'pattern' => '/^[A-Z]{1,2}\d{3,4}[A-Z]{1,2}$/',
                'examples' => ['TMP123AB', 'T4567CD', 'TEMP8901EF'],
                'description' => 'Format temporaire: préfixe + chiffres + suffixe',
                'priority' => 8
            ]
        ];
    }

    /**
     * Analyse une image avec OCR avancé utilisant OpenAI Vision et FE-Schrift
     */
    public function analyzeImage($imagePath): array
    {
        try {
            // Prétraitement de l'image standard
            $processedImage = $this->preprocessImage($imagePath);
            
            // Créer les versions optimisées pour FE-Schrift et B/8
            $optimizedVersions = $this->createB8OptimizedVersion(Image::make($imagePath));
            
            // Analyse avec OpenAI Vision (version standard)
            $openaiResult = $this->analyzeWithOpenAI($processedImage);
            
            // Analyse avec OpenAI Vision (version FE-Schrift) si disponible
            $feschriftResult = null;
            if ($this->isOpenAIConfigured()) {
                $feschriftPath = $this->saveTempImage($optimizedVersions['feschrift']);
                $feschriftResult = $this->analyzeWithOpenAI($feschriftPath);
                $this->cleanupTempImage($feschriftPath);
            }
            
            // Analyse avec OpenAI Vision (version B/8 optimisée) si disponible
            $b8Result = null;
            if ($this->isOpenAIConfigured()) {
                $b8Path = $this->saveTempImage($optimizedVersions['b8_optimized']);
                $b8Result = $this->analyzeWithOpenAI($b8Path);
                $this->cleanupTempImage($b8Path);
            }
            
            // Analyse avec OpenAI Vision (version morphologique B/8) si disponible
            $b8MorphologyResult = null;
            if ($this->isOpenAIConfigured()) {
                $b8MorphologyPath = $this->saveTempImage($optimizedVersions['b8_morphology']);
                $b8MorphologyResult = $this->analyzeWithOpenAI($b8MorphologyPath);
                $this->cleanupTempImage($b8MorphologyPath);
            }
            
            // Analyse avec Tesseract en fallback
            $tesseractResult = $this->analyzeWithTesseract($processedImage);
            
            // Analyse avec Tesseract (version FE-Schrift)
            $feschriftTesseractResult = null;
            $feschriftTesseractPath = $this->saveTempImage($optimizedVersions['feschrift']);
            $feschriftTesseractResult = $this->analyzeWithTesseract($feschriftTesseractPath);
            $this->cleanupTempImage($feschriftTesseractPath);
            
            // Analyse avec Tesseract (version B/8 optimisée)
            $b8TesseractResult = null;
            $b8TesseractPath = $this->saveTempImage($optimizedVersions['b8_optimized']);
            $b8TesseractResult = $this->analyzeWithTesseract($b8TesseractPath);
            $this->cleanupTempImage($b8TesseractPath);
            
            // Analyse avec Tesseract (version morphologique B/8)
            $b8MorphologyTesseractResult = null;
            $b8MorphologyTesseractPath = $this->saveTempImage($optimizedVersions['b8_morphology']);
            $b8MorphologyTesseractResult = $this->analyzeWithTesseract($b8MorphologyTesseractPath);
            $this->cleanupTempImage($b8MorphologyTesseractPath);
            
            // Analyse multi-niveaux B/8 avec OpenAI
            $multiLevelResults = [];
            if ($this->isOpenAIConfigured()) {
                foreach ($optimizedVersions['b8_multilevel'] as $level => $image) {
                    $path = $this->saveTempImage($image);
                    $result = $this->analyzeWithOpenAI($path);
                    $this->cleanupTempImage($path);
                    if ($result['success'] && !empty($result['plate_number'])) {
                        $multiLevelResults[] = [
                            'plate' => $result['plate_number'],
                            'confidence' => $result['confidence'],
                            'method' => 'openai_multilevel_' . $level,
                            'score' => $this->calculatePlateScore($result['plate_number'], $result['confidence']) + 20 // Bonus multi-niveaux
                        ];
                    }
                }
            }
            
            // Fusion et validation des résultats avec priorité B/8 et FE-Schrift
            return $this->mergeAndValidateAdvancedB8Results(
                $openaiResult, 
                $feschriftResult, 
                $b8Result,
                $b8MorphologyResult,
                $tesseractResult, 
                $feschriftTesseractResult,
                $b8TesseractResult,
                $b8MorphologyTesseractResult,
                $multiLevelResults
            );
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'analyse OCR avancée B/8-FE-Schrift', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath
            ]);
            
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sauvegarde une image temporaire pour l'analyse
     */
    private function saveTempImage($image): string
    {
        $tempPath = 'temp_' . uniqid() . '.jpg';
        Storage::disk('local')->put($tempPath, $image->encode('jpg', 95));
        return Storage::disk('local')->path($tempPath);
    }

    /**
     * Nettoie une image temporaire
     */
    private function cleanupTempImage($path): void
    {
        try {
            if (file_exists($path)) {
                unlink($path);
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs de nettoyage
        }
    }

    /**
     * Fusionne les résultats avec priorité aux versions FE-Schrift
     */
    private function mergeAndValidateFESchriftResults($openaiResult, $feschriftResult, $tesseractResult, $feschriftTesseractResult): array
    {
        $candidates = [];
        
        // Ajouter le résultat OpenAI standard
        if ($openaiResult['success'] && !empty($openaiResult['plate_number'])) {
            $candidates[] = [
                'plate' => $openaiResult['plate_number'],
                'confidence' => $openaiResult['confidence'],
                'method' => 'openai_standard',
                'score' => $this->calculatePlateScore($openaiResult['plate_number'], $openaiResult['confidence'])
            ];
        }
        
        // Ajouter le résultat OpenAI FE-Schrift (priorité haute)
        if ($feschriftResult && $feschriftResult['success'] && !empty($feschriftResult['plate_number'])) {
            $candidates[] = [
                'plate' => $feschriftResult['plate_number'],
                'confidence' => $feschriftResult['confidence'],
                'method' => 'openai_feschrift',
                'score' => $this->calculatePlateScore($feschriftResult['plate_number'], $feschriftResult['confidence']) + 10 // Bonus FE-Schrift
            ];
        }
        
        // Ajouter le résultat Tesseract standard
        if ($tesseractResult['success'] && !empty($tesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $tesseractResult['plate_number'],
                'confidence' => $tesseractResult['confidence'],
                'method' => 'tesseract_standard',
                'score' => $this->calculatePlateScore($tesseractResult['plate_number'], $tesseractResult['confidence'])
            ];
        }
        
        // Ajouter le résultat Tesseract FE-Schrift (priorité haute)
        if ($feschriftTesseractResult && $feschriftTesseractResult['success'] && !empty($feschriftTesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $feschriftTesseractResult['plate_number'],
                'confidence' => $feschriftTesseractResult['confidence'],
                'method' => 'tesseract_feschrift',
                'score' => $this->calculatePlateScore($feschriftTesseractResult['plate_number'], $feschriftTesseractResult['confidence']) + 8 // Bonus FE-Schrift
            ];
        }
        
        // Trier par score et retourner le meilleur
        if (empty($candidates)) {
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'no_candidates',
                'candidates' => $candidates
            ];
        }
        
        usort($candidates, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        $best = $candidates[0];
        
        return [
            'success' => true,
            'plate_number' => $best['plate'],
            'confidence' => $best['confidence'],
            'method' => $best['method'],
            'score' => $best['score'],
            'candidates' => $candidates,
            'format_detected' => $this->detectPlateFormat($best['plate']),
            'feschrift_detected' => strpos($best['method'], 'feschrift') !== false
        ];
    }

    /**
     * Fusionne les résultats avec priorité aux versions B/8 et FE-Schrift
     */
    private function mergeAndValidateB8FESchriftResults($openaiResult, $feschriftResult, $b8Result, $tesseractResult, $feschriftTesseractResult, $b8TesseractResult): array
    {
        $candidates = [];
        
        // Ajouter le résultat OpenAI standard
        if ($openaiResult['success'] && !empty($openaiResult['plate_number'])) {
            $candidates[] = [
                'plate' => $openaiResult['plate_number'],
                'confidence' => $openaiResult['confidence'],
                'method' => 'openai_standard',
                'score' => $this->calculatePlateScore($openaiResult['plate_number'], $openaiResult['confidence'])
            ];
        }
        
        // Ajouter le résultat OpenAI FE-Schrift (priorité haute)
        if ($feschriftResult && $feschriftResult['success'] && !empty($feschriftResult['plate_number'])) {
            $candidates[] = [
                'plate' => $feschriftResult['plate_number'],
                'confidence' => $feschriftResult['confidence'],
                'method' => 'openai_feschrift',
                'score' => $this->calculatePlateScore($feschriftResult['plate_number'], $feschriftResult['confidence']) + 10 // Bonus FE-Schrift
            ];
        }
        
        // Ajouter le résultat OpenAI B/8 optimisé (priorité très haute)
        if ($b8Result && $b8Result['success'] && !empty($b8Result['plate_number'])) {
            $candidates[] = [
                'plate' => $b8Result['plate_number'],
                'confidence' => $b8Result['confidence'],
                'method' => 'openai_b8_optimized',
                'score' => $this->calculatePlateScore($b8Result['plate_number'], $b8Result['confidence']) + 15 // Bonus B/8
            ];
        }
        
        // Ajouter le résultat Tesseract standard
        if ($tesseractResult['success'] && !empty($tesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $tesseractResult['plate_number'],
                'confidence' => $tesseractResult['confidence'],
                'method' => 'tesseract_standard',
                'score' => $this->calculatePlateScore($tesseractResult['plate_number'], $tesseractResult['confidence'])
            ];
        }
        
        // Ajouter le résultat Tesseract FE-Schrift (priorité haute)
        if ($feschriftTesseractResult && $feschriftTesseractResult['success'] && !empty($feschriftTesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $feschriftTesseractResult['plate_number'],
                'confidence' => $feschriftTesseractResult['confidence'],
                'method' => 'tesseract_feschrift',
                'score' => $this->calculatePlateScore($feschriftTesseractResult['plate_number'], $feschriftTesseractResult['confidence']) + 8 // Bonus FE-Schrift
            ];
        }
        
        // Ajouter le résultat Tesseract B/8 optimisé (priorité très haute)
        if ($b8TesseractResult && $b8TesseractResult['success'] && !empty($b8TesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $b8TesseractResult['plate_number'],
                'confidence' => $b8TesseractResult['confidence'],
                'method' => 'tesseract_b8_optimized',
                'score' => $this->calculatePlateScore($b8TesseractResult['plate_number'], $b8TesseractResult['confidence']) + 12 // Bonus B/8
            ];
        }
        
        // Trier par score et retourner le meilleur
        if (empty($candidates)) {
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'no_candidates',
                'candidates' => $candidates
            ];
        }
        
        usort($candidates, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        $best = $candidates[0];
        
        return [
            'success' => true,
            'plate_number' => $best['plate'],
            'confidence' => $best['confidence'],
            'method' => $best['method'],
            'score' => $best['score'],
            'candidates' => $candidates,
            'format_detected' => $this->detectPlateFormat($best['plate']),
            'feschrift_detected' => strpos($best['method'], 'feschrift') !== false,
            'b8_optimized' => strpos($best['method'], 'b8_optimized') !== false
        ];
    }

    /**
     * Fusionne les résultats avancés avec priorité B/8 morphologique et multi-niveaux
     */
    private function mergeAndValidateAdvancedB8Results($openaiResult, $feschriftResult, $b8Result, $b8MorphologyResult, $tesseractResult, $feschriftTesseractResult, $b8TesseractResult, $b8MorphologyTesseractResult, $multiLevelResults): array
    {
        $candidates = [];
        
        // Ajouter le résultat OpenAI standard
        if ($openaiResult['success'] && !empty($openaiResult['plate_number'])) {
            $candidates[] = [
                'plate' => $openaiResult['plate_number'],
                'confidence' => $openaiResult['confidence'],
                'method' => 'openai_standard',
                'score' => $this->calculatePlateScore($openaiResult['plate_number'], $openaiResult['confidence'])
            ];
        }
        
        // Ajouter le résultat OpenAI FE-Schrift (priorité haute)
        if ($feschriftResult && $feschriftResult['success'] && !empty($feschriftResult['plate_number'])) {
            $candidates[] = [
                'plate' => $feschriftResult['plate_number'],
                'confidence' => $feschriftResult['confidence'],
                'method' => 'openai_feschrift',
                'score' => $this->calculatePlateScore($feschriftResult['plate_number'], $feschriftResult['confidence']) + 10 // Bonus FE-Schrift
            ];
        }
        
        // Ajouter le résultat OpenAI B/8 optimisé (priorité très haute)
        if ($b8Result && $b8Result['success'] && !empty($b8Result['plate_number'])) {
            $candidates[] = [
                'plate' => $b8Result['plate_number'],
                'confidence' => $b8Result['confidence'],
                'method' => 'openai_b8_optimized',
                'score' => $this->calculatePlateScore($b8Result['plate_number'], $b8Result['confidence']) + 15 // Bonus B/8
            ];
        }
        
        // Ajouter le résultat OpenAI B/8 morphologique (priorité maximale)
        if ($b8MorphologyResult && $b8MorphologyResult['success'] && !empty($b8MorphologyResult['plate_number'])) {
            $candidates[] = [
                'plate' => $b8MorphologyResult['plate_number'],
                'confidence' => $b8MorphologyResult['confidence'],
                'method' => 'openai_b8_morphology',
                'score' => $this->calculatePlateScore($b8MorphologyResult['plate_number'], $b8MorphologyResult['confidence']) + 25 // Bonus morphologique
            ];
        }
        
        // Ajouter les résultats multi-niveaux (priorité très haute)
        foreach ($multiLevelResults as $result) {
            $candidates[] = $result;
        }
        
        // Ajouter le résultat Tesseract standard
        if ($tesseractResult['success'] && !empty($tesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $tesseractResult['plate_number'],
                'confidence' => $tesseractResult['confidence'],
                'method' => 'tesseract_standard',
                'score' => $this->calculatePlateScore($tesseractResult['plate_number'], $tesseractResult['confidence'])
            ];
        }
        
        // Ajouter le résultat Tesseract FE-Schrift (priorité haute)
        if ($feschriftTesseractResult && $feschriftTesseractResult['success'] && !empty($feschriftTesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $feschriftTesseractResult['plate_number'],
                'confidence' => $feschriftTesseractResult['confidence'],
                'method' => 'tesseract_feschrift',
                'score' => $this->calculatePlateScore($feschriftTesseractResult['plate_number'], $feschriftTesseractResult['confidence']) + 8 // Bonus FE-Schrift
            ];
        }
        
        // Ajouter le résultat Tesseract B/8 optimisé (priorité très haute)
        if ($b8TesseractResult && $b8TesseractResult['success'] && !empty($b8TesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $b8TesseractResult['plate_number'],
                'confidence' => $b8TesseractResult['confidence'],
                'method' => 'tesseract_b8_optimized',
                'score' => $this->calculatePlateScore($b8TesseractResult['plate_number'], $b8TesseractResult['confidence']) + 12 // Bonus B/8
            ];
        }
        
        // Ajouter le résultat Tesseract B/8 morphologique (priorité maximale)
        if ($b8MorphologyTesseractResult && $b8MorphologyTesseractResult['success'] && !empty($b8MorphologyTesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $b8MorphologyTesseractResult['plate_number'],
                'confidence' => $b8MorphologyTesseractResult['confidence'],
                'method' => 'tesseract_b8_morphology',
                'score' => $this->calculatePlateScore($b8MorphologyTesseractResult['plate_number'], $b8MorphologyTesseractResult['confidence']) + 22 // Bonus morphologique
            ];
        }
        
        // Trier par score et retourner le meilleur
        if (empty($candidates)) {
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'no_candidates',
                'candidates' => $candidates
            ];
        }
        
        usort($candidates, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        $best = $candidates[0];
        
        // Appliquer la validation morphologique finale
        $finalPlate = $this->validateB8Morphology($best['plate']);
        
        return [
            'success' => true,
            'plate_number' => $finalPlate,
            'confidence' => $best['confidence'],
            'method' => $best['method'],
            'score' => $best['score'],
            'candidates' => $candidates,
            'format_detected' => $this->detectPlateFormat($finalPlate),
            'feschrift_detected' => strpos($best['method'], 'feschrift') !== false,
            'b8_optimized' => strpos($best['method'], 'b8') !== false,
            'morphology_applied' => $finalPlate !== $best['plate']
        ];
    }

    /**
     * Prétraite l'image pour améliorer la précision OCR
     */
    private function preprocessImage($imagePath): string
    {
        try {
            $image = Image::make($imagePath);
            
            // Amélioration du contraste
            $image->contrast(35);
            
            // Réduction du bruit
            $image->sharpen(8);
            
            // Ajustement de la luminosité
            $image->brightness(12);
            
            // Conversion en niveaux de gris pour meilleure détection
            $image->greyscale();
            
            // Amélioration de la netteté
            $image->sharpen(10);
            
            // Redimensionnement optimal pour OCR
            $image->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Créer une version optimisée pour détecter le numéro de plaque
            $plateOptimized = $this->optimizeForPlateNumber($image);
            
            // Sauvegarder l'image traitée
            $processedPath = 'processed_' . uniqid() . '.jpg';
            Storage::disk('local')->put($processedPath, $plateOptimized->encode('jpg', 95));
            
            return Storage::disk('local')->path($processedPath);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du prétraitement de l\'image', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath
            ]);
            
            return $imagePath; // Retourner l'original en cas d'erreur
        }
    }

    /**
     * Optimise l'image pour détecter spécifiquement le numéro de plaque RDC
     */
    private function optimizeForPlateNumber($image)
    {
        // Créer une copie pour l'optimisation
        $optimized = clone $image;
        
        // Prétraitement spécifique pour plaques RDC
        $optimized->contrast(45);      // Contraste élevé pour faire ressortir les caractères
        $optimized->sharpen(15);       // Netteté maximale pour les bords des caractères
        $optimized->brightness(10);     // Luminosité équilibrée
        
        // Amélioration spécifique pour les caractères alphanumériques
        $optimized->brightness(-8);    // Assombrir légèrement pour améliorer le contraste
        $optimized->contrast(55);      // Contraste très élevé
        
        // Appliquer un filtre de netteté supplémentaire
        $optimized->sharpen(8);
        
        // Ajustement final pour l'OCR
        $optimized->brightness(3);
        $optimized->contrast(10);
        
        return $optimized;
    }

    /**
     * Optimisation spécialisée pour le style FE-Schrift (écriture anti-fraude)
     */
    private function optimizeForFESchrift($image)
    {
        // Créer une copie pour l'optimisation FE-Schrift
        $optimized = clone $image;
        
        // Augmenter le contraste pour faire ressortir les caractères discontinus
        $optimized->contrast(60);
        
        // Netteté très élevée pour les bords des caractères FE-Schrift
        $optimized->sharpen(20);
        
        // Ajustement de luminosité pour les caractères ouverts (4, 0, 9)
        $optimized->brightness(5);
        
        // Contraste extrême pour les filigranes de sécurité
        $optimized->contrast(70);
        
        // Réduction du bruit pour les hachures diagonales
        $optimized->sharpen(12);
        
        // Binarisation agressive pour les caractères discontinus
        $optimized->brightness(-10);
        $optimized->contrast(80);
        
        // Netteté finale pour les formes spécifiques FE-Schrift
        $optimized->sharpen(15);
        
        return $optimized;
    }

    /**
     * Optimisation spécialisée pour distinguer B et 8
     */
    private function optimizeForB8Distinction($image)
    {
        // Créer une copie pour l'optimisation B/8
        $optimized = clone $image;
        
        // Étape 1: Préparation pour détection des formes internes
        $optimized->contrast(40);      // Contraste modéré pour préserver les formes
        $optimized->brightness(5);      // Luminosité pour voir l'intérieur
        
        // Étape 2: Netteté extrême pour les contours
        $optimized->sharpen(25);       // Netteté très élevée pour les bords
        
        // Étape 3: Renforcement des formes internes
        $optimized->contrast(55);      // Contraste pour les formes internes
        $optimized->sharpen(15);       // Netteté pour les détails
        
        // Étape 4: Binarisation progressive pour distinguer les boucles
        $optimized->brightness(-8);    // Assombrir pour les contours
        $optimized->contrast(70);      // Contraste élevé pour les boucles
        
        // Étape 5: Détection des espaces internes (B a plus d'espace que 8)
        $optimized->sharpen(20);       // Netteté maximale pour les espaces
        $optimized->brightness(-3);    // Légère assombrissement
        $optimized->contrast(85);      // Contraste très élevé
        
        // Étape 6: Finalisation pour distinction morphologique
        $optimized->sharpen(12);       // Netteté finale
        $optimized->brightness(2);     // Ajustement final
        
        return $optimized;
    }

    /**
     * Optimisation morphologique pour distinguer B (2 courbes) de 8 (2 boucles)
     */
    private function optimizeForB8Morphology($image)
    {
        // Créer une copie pour l'analyse morphologique
        $optimized = clone $image;
        
        // Conversion en niveaux de gris pour analyse morphologique
        $optimized->greyscale();
        
        // Contraste extrême pour les formes
        $optimized->contrast(90);
        
        // Netteté maximale pour les contours
        $optimized->sharpen(30);
        
        // Binarisation agressive pour les formes internes
        $optimized->brightness(-15);
        $optimized->contrast(95);
        
        // Netteté finale pour les espaces internes
        $optimized->sharpen(25);
        
        return $optimized;
    }

    /**
     * Optimisation multi-niveaux pour B/8 avec différents traitements
     */
    private function createMultiLevelB8Optimization($image)
    {
        $versions = [];
        
        // Version 1: Contraste standard
        $v1 = clone $image;
        $v1->contrast(45);
        $v1->sharpen(20);
        $v1->brightness(5);
        $v1->contrast(65);
        $versions['contrast_standard'] = $v1;
        
        // Version 2: Contraste élevé
        $v2 = clone $image;
        $v2->contrast(70);
        $v2->sharpen(25);
        $v2->brightness(0);
        $v2->contrast(80);
        $versions['contrast_high'] = $v2;
        
        // Version 3: Morphologique
        $v3 = clone $image;
        $v3->greyscale();
        $v3->contrast(85);
        $v3->sharpen(30);
        $v3->brightness(-10);
        $v3->contrast(90);
        $versions['morphological'] = $v3;
        
        // Version 4: Détection d'espaces
        $v4 = clone $image;
        $v4->contrast(50);
        $v4->sharpen(22);
        $v4->brightness(-5);
        $v4->contrast(75);
        $v4->sharpen(18);
        $v4->brightness(-8);
        $v4->contrast(88);
        $versions['space_detection'] = $v4;
        
        // Version 5: Inversion pour voir les boucles
        $v5 = clone $image;
        $v5->invert();
        $v5->contrast(60);
        $v5->sharpen(20);
        $v5->brightness(10);
        $v5->contrast(70);
        $v5->invert();
        $versions['inverted_analysis'] = $v5;
        
        return $versions;
    }

    /**
     * Crée une version optimisée pour B/8 distinction
     */
    private function createB8OptimizedVersion($image)
    {
        // Version standard
        $standard = $this->optimizeForPlateNumber($image);
        
        // Version FE-Schrift spécialisée
        $feschrift = $this->optimizeForFESchrift($image);
        
        // Version B/8 optimisée
        $b8Optimized = $this->optimizeForB8Distinction($image);
        
        // Version morphologique B/8
        $b8Morphology = $this->optimizeForB8Morphology($image);
        
        // Versions multi-niveaux B/8
        $multiLevelB8 = $this->createMultiLevelB8Optimization($image);
        
        // Retourner toutes les versions pour analyse multiple
        return [
            'standard' => $standard,
            'feschrift' => $feschrift,
            'b8_optimized' => $b8Optimized,
            'b8_morphology' => $b8Morphology,
            'b8_multilevel' => $multiLevelB8
        ];
    }

    /**
     * Validation morphologique pour distinguer B de 8
     */
    private function validateB8Morphology($text): string
    {
        // Analyser chaque caractère pour distinguer B de 8
        $result = '';
        $length = strlen($text);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];
            
            // Si c'est B ou 8, faire une analyse morphologique
            if ($char === 'B' || $char === '8') {
                $result .= $this->analyzeB8Morphology($text, $i);
            } else {
                $result .= $char;
            }
        }
        
        return $result;
    }

    /**
     * Analyse morphologique d'un caractère B/8
     */
    private function analyzeB8Morphology($text, $position): string
    {
        // Règles morphologiques pour distinguer B de 8
        
        // Contexte autour du caractère
        $context = $this->getContextAroundPosition($text, $position, 3);
        
        // Règle 1: Si c'est au début et suivi d'une lettre, c'est probablement B
        if ($position === 0 && isset($text[$position + 1]) && ctype_alpha($text[$position + 1])) {
            return 'B';
        }
        
        // Règle 2: Si c'est entre deux lettres, c'est B
        if ($position > 0 && $position < strlen($text) - 1) {
            $prev = $text[$position - 1];
            $next = $text[$position + 1];
            if (ctype_alpha($prev) && ctype_alpha($next)) {
                return 'B';
            }
        }
        
        // Règle 3: Si c'est au milieu de chiffres, c'est 8
        if ($position > 0 && $position < strlen($text) - 1) {
            $prev = $text[$position - 1];
            $next = $text[$position + 1];
            if (ctype_digit($prev) && ctype_digit($next)) {
                return '8';
            }
        }
        
        // Règle 4: Si c'est après une lettre et avant un chiffre, c'est B
        if ($position > 0 && $position < strlen($text) - 1) {
            $prev = $text[$position - 1];
            $next = $text[$position + 1];
            if (ctype_alpha($prev) && ctype_digit($next)) {
                return 'B';
            }
        }
        
        // Règle 5: Si c'est après 2+ chiffres, c'est 8
        if ($position > 2) {
            $prev3 = substr($text, $position - 3, 3);
            if (ctype_digit($prev3)) {
                return '8';
            }
        }
        
        // Règle 6: Si c'est avant 2+ chiffres, c'est 8
        if ($position < strlen($text) - 3) {
            $next3 = substr($text, $position + 1, 3);
            if (ctype_digit($next3)) {
                return '8';
            }
        }
        
        // Règle 7: Analyse des patterns de plaque RDC
        if ($this->matchesRDCPlatePattern($text, $position)) {
            return $this->predictB8FromPattern($text, $position);
        }
        
        // Par défaut, préférer 8 dans les plaques RDC
        return '8';
    }

    /**
     * Vérifie si le texte correspond à un pattern de plaque RDC
     */
    private function matchesRDCPlatePattern($text, $position): bool
    {
        $length = strlen($text);
        
        // Patterns RDC standards
        $patterns = [
            '/^[A-Z]{2}[0-9]{3}[A-Z]{2}$/',  // AB123CD
            '/^[A-Z]{2}[0-9]{4}[A-Z]{2}$/',  // AB1234CD
            '/^[A-Z]{1,2}[0-9]{4,6}$/',      // A12345, AB123456
            '/^[A-Z]{3}[0-9]{3,4}[A-Z]$/',    // ABC123D
            '/^[A-Z]{2}[0-9]{3,4}[A-Z]$/',    // AB123C
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Prédit B ou 8 basé sur le pattern de plaque
     */
    private function predictB8FromPattern($text, $position): string
    {
        $length = strlen($text);
        
        // Pattern AB123CD - position 2-4 sont des chiffres
        if ($length === 7 && $position >= 2 && $position <= 4) {
            return '8';
        }
        
        // Pattern AB1234CD - position 2-5 sont des chiffres
        if ($length === 8 && $position >= 2 && $position <= 5) {
            return '8';
        }
        
        // Pattern A12345 - position 1-5 sont des chiffres
        if ($length >= 5 && $length <= 7 && $position >= 1 && $position <= 5) {
            return '8';
        }
        
        // Sinon, c'est probablement une lettre
        return 'B';
    }

    /**
     * Crée une version optimisée pour FE-Schrift
     */
    private function createFESchriftVersion($image)
    {
        // Version standard
        $standard = $this->optimizeForPlateNumber($image);
        
        // Version FE-Schrift spécialisée
        $feschrift = $this->optimizeForFESchrift($image);
        
        // Retourner les deux versions pour analyse multiple
        return [
            'standard' => $standard,
            'feschrift' => $feschrift
        ];
    }

    /**
     * Analyse l'image avec OpenAI Vision API
     */
    private function analyzeWithOpenAI($imagePath): array
    {
        if (empty($this->openaiApiKey)) {
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'openai_unavailable'
            ];
        }

        try {
            $base64Image = base64_encode(file_get_contents($imagePath));
            $dataUrl = 'data:image/jpeg;base64,' . $base64Image;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4-vision-preview',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $this->getOpenAIPrompt()
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $dataUrl
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 300,
                'temperature' => 0.1
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API Error: ' . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            return $this->parseOpenAIResponse($content);
            
        } catch (\Exception $e) {
            Log::error('Erreur OpenAI Vision', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'openai_error'
            ];
        }
    }

    /**
     * Analyse avec Tesseract comme fallback
     */
    private function analyzeWithTesseract($imagePath): array
    {
        try {
            // Utilisation de Tesseract.js côté client ou Tesseract PHP
            $command = "tesseract \"{$imagePath}\" stdout -l eng --psm 7 --oem 3 --dpi 300 -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            
            $output = shell_exec($command);
            $plateNumber = $this->normalizePlateNumber(trim($output));
            
            $confidence = $this->calculateTesseractConfidence($plateNumber);
            
            return [
                'success' => !empty($plateNumber),
                'plate_number' => $plateNumber,
                'confidence' => $confidence,
                'method' => 'tesseract',
                'raw_output' => $output
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur Tesseract', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'tesseract_error'
            ];
        }
    }

    /**
     * Fusionne et valide les résultats des différentes méthodes OCR
     */
    private function mergeAndValidateResults($openaiResult, $tesseractResult): array
    {
        $candidates = [];
        
        // Ajouter le résultat OpenAI
        if ($openaiResult['success'] && !empty($openaiResult['plate_number'])) {
            $candidates[] = [
                'plate' => $openaiResult['plate_number'],
                'confidence' => $openaiResult['confidence'],
                'method' => 'openai',
                'score' => $this->calculatePlateScore($openaiResult['plate_number'], $openaiResult['confidence'])
            ];
        }
        
        // Ajouter le résultat Tesseract
        if ($tesseractResult['success'] && !empty($tesseractResult['plate_number'])) {
            $candidates[] = [
                'plate' => $tesseractResult['plate_number'],
                'confidence' => $tesseractResult['confidence'],
                'method' => 'tesseract',
                'score' => $this->calculatePlateScore($tesseractResult['plate_number'], $tesseractResult['confidence'])
            ];
        }
        
        // Trier par score et retourner le meilleur
        if (empty($candidates)) {
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'no_candidates',
                'candidates' => $candidates
            ];
        }
        
        usort($candidates, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        $best = $candidates[0];
        
        return [
            'success' => true,
            'plate_number' => $best['plate'],
            'confidence' => $best['confidence'],
            'method' => $best['method'],
            'score' => $best['score'],
            'candidates' => $candidates,
            'format_detected' => $this->detectPlateFormat($best['plate'])
        ];
    }

    /**
     * Calcule un score de qualité pour une plaque détectée (optimisé RDC)
     */
    private function calculatePlateScore($plateNumber, $confidence): float
    {
        $score = 0;
        $length = strlen($plateNumber);
        
        // Score de base basé sur la confiance
        $score += $confidence * 0.4;
        
        // Détection rapide du format avec bonus selon priorité
        $format = $this->detectPlateFormatFast($plateNumber);
        if ($format !== 'UNKNOWN') {
            $priority = $this->plateFormats[$format]['priority'] ?? 10;
            $score += (30 - ($priority * 2)); // Plus la priorité est basse, plus le bonus est élevé
        }
        
        // Bonus pour les longueurs spécifiques RDC
        switch ($length) {
            case 7: // Format standard AB123CD
                $score += 20;
                break;
            case 8: // Format récent AB1234CD
                $score += 18;
                break;
            case 6: // Format moto AB123C
                $score += 15;
                break;
            case 5: // Format ancien A1234
                $score += 12;
                break;
            case 9: // Format commercial ABC1234D
                $score += 10;
                break;
        }
        
        // Bonus pour la structure RDC typique
        if (preg_match('/^[A-Z]{2}\d{3,4}[A-Z]{1,2}$/', $plateNumber)) {
            $score += 15; // Structure la plus commune
        }
        
        // Bonus pour la présence de chiffres et lettres
        if (preg_match('/\d/', $plateNumber) && preg_match('/[A-Z]/', $plateNumber)) {
            $score += 10;
        }
        
        // Bonus pour les séquences réalistes
        if ($this->hasRealisticSequence($plateNumber)) {
            $score += 8;
        }
        
        // Pénalités
        if (preg_match('/(.)\1{3,}/', $plateNumber)) {
            $score -= 25; // Trop de répétitions
        } elseif (preg_match('/(.)\1{2,}/', $plateNumber)) {
            $score -= 15; // Répétitions modérées
        }
        
        // Pénalité pour les séquences improbables
        if ($this->hasUnrealisticSequence($plateNumber)) {
            $score -= 10;
        }
        
        return max(0, min(100, $score));
    }

    /**
     * Vérifie si la séquence de plaque est réaliste
     */
    private function hasRealisticSequence($plateNumber): bool
    {
        // Vérifier les patterns réalistes pour les plaques RDC
        $realisticPatterns = [
            '/^[A-Z]{2}\d{3}[A-Z]{2}$/',    // AB123CD
            '/^[A-Z]{2}\d{4}[A-Z]{2}$/',    // AB1234CD
            '/^[A-Z]{1}\d{4,5}$/',         // A12345
            '/^[A-Z]{2}\d{3}[A-Z]{1}$/',    // AB123C
            '/^[A-Z]{3}\d{3,4}[A-Z]{1}$/',  // ABC123D
        ];
        
        foreach ($realisticPatterns as $pattern) {
            if (preg_match($pattern, $plateNumber)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Vérifie si la séquence de plaque est improbable
     */
    private function hasUnrealisticSequence($plateNumber): bool
    {
        // Séquences trop simples ou improbables
        $unrealisticPatterns = [
            '/^[A]\d+[A]$/',           // Seulement des A
            '/^[B]\d+[B]$/',           // Seulement des B
            '/^\d+$/',                 // Uniquement des chiffres
            '/^[A-Z]+$/',              // Uniquement des lettres
            '/^[A-Z]{2}0{3,}[A-Z]+$/', // Trop de zéros
            '/^[A-Z]{2}1{3,}[A-Z]+$/', // Trop de uns
        ];
        
        foreach ($unrealisticPatterns as $pattern) {
            if (preg_match($pattern, $plateNumber)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Détecte le format de plaque avec priorité optimisée
     */
    private function detectPlateFormat($plateNumber): ?string
    {
        // Trier les formats par priorité pour une détection rapide
        $sortedFormats = $this->plateFormats;
        uasort($sortedFormats, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        
        foreach ($sortedFormats as $format => $rules) {
            if (preg_match($rules['pattern'], $plateNumber)) {
                return $format;
            }
        }
        
        return 'UNKNOWN';
    }

    /**
     * Détecte rapidement le format de plaque (optimisé pour performance)
     */
    private function detectPlateFormatFast($plateNumber): ?string
    {
        $length = strlen($plateNumber);
        
        // Optimisation rapide basée sur la longueur
        switch ($length) {
            case 7: // Format standard: AB123CD
                if (preg_match('/^[A-Z]{2}\d{3}[A-Z]{2}$/', $plateNumber)) {
                    return 'RDC_STANDARD';
                }
                break;
                
            case 8: // Format récent: AB1234CD
                if (preg_match('/^[A-Z]{2}\d{4}[A-Z]{2}$/', $plateNumber)) {
                    return 'RDC_NEW';
                }
                break;
                
            case 6: // Format moto: AB123C
                if (preg_match('/^[A-Z]{2}\d{3,4}[A-Z]$/', $plateNumber)) {
                    return 'RDC_MOTO';
                }
                break;
                
            case 5: // Format court: A1234
                if (preg_match('/^[A-Z]{1}\d{4}$/', $plateNumber)) {
                    return 'RDC_OLD';
                }
                break;
                
            case 9: // Format long: ABC1234D
                if (preg_match('/^[A-Z]{3}\d{4}[A-Z]$/', $plateNumber)) {
                    return 'RDC_COMMERCIAL';
                }
                break;
        }
        
        // Fallback sur la détection complète
        return $this->detectPlateFormat($plateNumber);
    }

    /**
     * Normalise le numéro de plaque avec correction FE-Schrift
     */
    private function normalizePlateNumber($text): string
    {
        // Convertir en majuscules
        $text = strtoupper($text);
        
        // Filtrer les écritures superposées RDC
        $text = $this->filterPlateText($text);
        
        // Supprimer les espaces et caractères non alphanumériques
        $text = preg_replace('/[^A-Z0-9]/', '', $text);
        
        // Corrections FE-Schrift spécifiques
        $text = $this->applyFESchriftCorrections($text);
        
        // Corrections communes standards
        $text = str_replace([
            'O', 'I', 'S', 'Z', 'B', 'G'
        ], [
            '0', '1', '5', '2', '8', '6'
        ], $text);
        
        return $text;
    }

    /**
     * Applique les corrections spécifiques au style FE-Schrift
     */
    private function applyFESchriftCorrections($text): string
    {
        // Corrections pour caractères FE-Schrift spécifiques
        
        // Le 4 FE-Schrift est souvent mal lu comme 9 ou h
        $text = preg_replace('/H/', '4', $text);
        $text = preg_replace('/h/', '4', $text);
        
        // Le 0 FE-Schrift avec hachures peut être lu comme O ou Q
        $text = preg_replace('/Q/', '0', $text);
        
        // Le 9 FE-Schrift peut être lu comme g ou q
        $text = preg_replace('/g/', '9', $text);
        $text = preg_replace('/q/', '9', $text);
        
        // Le 6 FE-Schrift peut être lu comme b
        $text = preg_replace('/b/', '6', $text);
        
        // Correction B/8 avec analyse contextuelle
        $text = $this->correctB8Confusion($text);
        
        // Le 2 FE-Schrift peut être lu comme Z
        $text = preg_replace('/Z/', '2', $text);
        
        // Le 5 FE-Schrift peut être lu comme S
        $text = preg_replace('/S/', '5', $text);
        
        // Le 1 FE-Schrift peut être lu comme I ou l
        $text = preg_replace('/l/', '1', $text);
        $text = preg_replace('/I/', '1', $text);
        
        // Le 3 FE-Schrift peut être lu comme E
        $text = preg_replace('/E/', '3', $text);
        
        // Le 7 FE-Schrift peut être lu comme T
        $text = preg_replace('/T/', '7', $text);
        
        return $text;
    }

    /**
     * Corrige la confusion B/8 avec analyse contextuelle FE-Schrift
     */
    private function correctB8Confusion($text): string
    {
        // Analyser la position et le contexte pour distinguer B de 8
        
        // Patterns où B est plus probable que 8
        $bPatterns = [
            '/^B/',                    // B au début (probablement une lettre)
            '/B[0-9]/',               // B suivi d'un chiffre (format plaque)
            '/[0-9]B[0-9]/',         // B entre chiffres (format plaque)
            '/B[A-Z]/',               // B suivi d'une lettre
            '/[A-Z]B$/',              // B à la fin après une lettre
        ];
        
        // Patterns où 8 est plus probable que B
        $eightPatterns = [
            '/[0-9]8[0-9]/',         // 8 entre chiffres
            '/8[0-9]/',               // 8 suivi d'un chiffre
            '/[0-9]8/',               // 8 précédé d'un chiffre
            '/[0-9]{2}8/',           // 8 après 2+ chiffres
            '/8[0-9]{2}/',           // 8 avant 2+ chiffres
        ];
        
        // Correction contextuelle
        $correctedText = $text;
        
        // D'abord, identifier les positions ambiguës
        preg_match_all('/[B8]/', $text, $matches, PREG_OFFSET_CAPTURE);
        
        foreach ($matches[0] as $match) {
            $char = $match[0];
            $pos = $match[1];
            
            // Analyser le contexte autour du caractère
            $context = $this->getContextAroundPosition($text, $pos, 2);
            
            // Appliquer les règles contextuelles
            if ($this->isProbablyLetter($context, $pos)) {
                $correctedText = substr_replace($correctedText, 'B', $pos, 1);
            } elseif ($this->isProbablyNumber($context, $pos)) {
                $correctedText = substr_replace($correctedText, '8', $pos, 1);
            } else {
                // Utiliser les patterns par défaut
                if ($this->matchesPatterns($context, $bPatterns)) {
                    $correctedText = substr_replace($correctedText, 'B', $pos, 1);
                } elseif ($this->matchesPatterns($context, $eightPatterns)) {
                    $correctedText = substr_replace($correctedText, '8', $pos, 1);
                } else {
                    // Par défaut, préférer 8 dans les plaques RDC
                    $correctedText = substr_replace($correctedText, '8', $pos, 1);
                }
            }
        }
        
        return $correctedText;
    }

    /**
     * Extrait le contexte autour d'une position
     */
    private function getContextAroundPosition($text, $pos, $radius = 2): string
    {
        $start = max(0, $pos - $radius);
        $end = min(strlen($text), $pos + $radius + 1);
        return substr($text, $start, $end - $start);
    }

    /**
     * Détermine si le caractère est probablement une lettre
     */
    private function isProbablyLetter($context, $pos): bool
    {
        $relativePos = $pos - (strlen($context) - 1) / 2;
        
        // Vérifier les caractères autour
        $left = $relativePos > 0 ? $context[$relativePos - 1] : '';
        $right = $relativePos < strlen($context) - 1 ? $context[$relativePos + 1] : '';
        
        // Si entouré de lettres, c'est probablement une lettre
        if (ctype_alpha($left) && ctype_alpha($right)) {
            return true;
        }
        
        // Si au début et suivi d'une lettre
        if ($relativePos === 0 && ctype_alpha($right)) {
            return true;
        }
        
        // Si après une lettre et avant un chiffre (format plaque)
        if (ctype_alpha($left) && ctype_digit($right)) {
            return true;
        }
        
        return false;
    }

    /**
     * Détermine si le caractère est probablement un chiffre
     */
    private function isProbablyNumber($context, $pos): bool
    {
        $relativePos = $pos - (strlen($context) - 1) / 2;
        
        // Vérifier les caractères autour
        $left = $relativePos > 0 ? $context[$relativePos - 1] : '';
        $right = $relativePos < strlen($context) - 1 ? $context[$relativePos + 1] : '';
        
        // Si entouré de chiffres, c'est probablement un chiffre
        if (ctype_digit($left) && ctype_digit($right)) {
            return true;
        }
        
        // Si entre chiffres
        if (ctype_digit($left) && ctype_digit($right)) {
            return true;
        }
        
        // Si après 2+ chiffres
        if (ctype_digit($left) && ctype_digit($right)) {
            return true;
        }
        
        return false;
    }

    /**
     * Vérifie si le contexte correspond aux patterns
     */
    private function matchesPatterns($context, $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $context)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Filtre les écritures superposées et conserve uniquement le numéro de plaque
     */
    private function filterPlateText($text): string
    {
        // Patterns à ignorer (écritures au-dessus des plaques RDC)
        $ignorePatterns = [
            '/^RDC\s*/i',           // RDC au début
            '/^RD\s*CONGO\s*/i',    // RD CONGO au début
            '/^CONGO\s*/i',         // CONGO au début
            '/^REP\s*DEM\s*CONGO\s*/i', // REP DEM CONGO au début
            '/^RÉP\s*DEM\s*CONGO\s*/i', // RÉP DEM CONGO au début
            '/^DRC\s*/i',           // DRC au début
            '/^CD\s*/i',            // CD au début
            '/^KATANGA\s*/i',       // KATANGA au début
            '/^LUBUMBASHI\s*/i',    // LUBUMBASHI au début
            '/^\d{4}\s*/i',         // Années au début (ex: 2023)
            '/^[A-Z]{3,}\s*/i',     // 3+ lettres au début (probablement écritures)
        ];

        // Appliquer les filtres
        foreach ($ignorePatterns as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }

        // Nettoyer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // Si le texte commence par des mots communs à ignorer, les supprimer
        $ignoreWords = ['RDC', 'CONGO', 'DRC', 'CD', 'KATANGA', 'LUBUMBASHI', 'REP', 'DEM'];
        $words = explode(' ', $text);
        
        // Garder uniquement les parties qui pourraient être des numéros de plaque
        $filteredParts = [];
        foreach ($words as $word) {
            // Si le mot contient des chiffres et des lettres, c'est probablement le numéro
            if (preg_match('/[A-Z]/', $word) && preg_match('/\d/', $word)) {
                $filteredParts[] = $word;
            }
            // Si c'est uniquement des chiffres et lettres dans un format de plaque
            elseif (preg_match('/^[A-Z0-9]{3,}$/', $word)) {
                $filteredParts[] = $word;
            }
        }

        // Si on a trouvé des parties candidates, les retourner
        if (!empty($filteredParts)) {
            return implode('', $filteredParts);
        }

        // Sinon, retourner le texte original (déjà nettoyé)
        return $text;
    }

    /**
     * Calcule la confiance Tesseract
     */
    private function calculateTesseractConfidence($plateNumber): int
    {
        if (empty($plateNumber)) {
            return 0;
        }
        
        $confidence = 50; // Base
        
        // Bonus pour les formats valides
        foreach ($this->plateFormats as $format => $rules) {
            if (preg_match($rules['pattern'], $plateNumber)) {
                $confidence += 25;
                break;
            }
        }
        
        // Bonus pour la longueur
        $length = strlen($plateNumber);
        if ($length >= 6 && $length <= 9) {
            $confidence += 15;
        }
        
        return min(95, $confidence);
    }

    /**
     * Génère le prompt pour OpenAI Vision avec reconnaissance FE-Schrift
     */
    private function getOpenAIPrompt(): string
    {
        $formats = [];
        foreach ($this->plateFormats as $format => $rules) {
            $formats[] = "- {$rules['description']}: {$rules['pattern']} (ex: " . implode(', ', $rules['examples']) . ")";
        }
        
        return "Analyse cette image et identifie UNIQUEMENT le numéro de plaque d'immatriculation.

CONTEXTE CRUCIAL: Il s'agit d'un système de contrôle routier pour la République Démocratique du Congo. 
Les plaques RDC utilisent le style FE-Schrift (écriture anti-fraude) avec des caractères spécifiques.

STYLE FE-SCHRIFT IMPORTANT:
- Police anti-fraude allemande adaptée pour les plaques RDC
- Caractères discontinus et difficiles à falsifier
- Le chiffre 4 est ouvert en haut (pas de triangle supérieur)
- Le chiffre 0 a des hachures diagonales à l'intérieur
- Le chiffre 9 a une forme spécifique anti-fraude
- Tous les chiffres ont des coupures de sécurité
- Présence de filigranes/hachures dans les caractères noirs

FORMATS VALIDES:
" . implode("\n", $formats) . "

INSTRUCTIONS CRUCIALES:
1. IGNORE COMPLÈTEMENT les écritures au-dessus (RDC, CONGO, etc.)
2. Cherche UNIQUEMENT la combinaison ALPHANUMÉRIQUE au centre de la plaque
3. Le style FE-Schrift peut rendre les chiffres difficiles à lire - fais attention aux formes
4. Le 4 FE-Schrift est ouvert en haut (ressemble à un h minuscule)
5. Le 0 FE-Schrift a des hachures diagonales (peut ressembler à O ou Q)
6. Le 9 FE-Schrift a une forme spécifique (peut ressembler à g ou q)
7. Corrige les caractères FE-Schrift: H→4, Q→0, g→9, b→6, B→8, Z→2, S→5, l→1, E→3, T→7
8. Ignore les filigranes et hachures de sécurité dans l'analyse
9. Le numéro est TOUJOURS une combinaison selon les formats ci-dessus

EXEMPLES FE-SCHRIFT:
- Un '4' ouvert en haut doit être lu comme '4' (pas comme '9' ou 'h')
- Un '0' avec hachures doit être lu comme '0' (pas comme 'O' ou 'Q')
- Un '9' stylisé doit être lu comme '9' (pas comme 'g' ou 'q')
- Si tu vois 'RDC' en haut et 'AB123CD' en style FE-Schrift en dessous → 'AB123CD'

FORMAT DE RÉPONSE:
{
  \"plate_number\": \"AB123CD\",
  \"confidence\": 85,
  \"format_detected\": \"RDC\",
  \"notes\": \"Plaque FE-Schrift détectée, caractères anti-fraude reconnus\"
}

Si aucune plaque n'est détectée:
{
  \"plate_number\": null,
  \"confidence\": 0,
  \"format_detected\": null,
  \"notes\": \"Aucun numéro FE-Schrift valide détecté\"
}";
    }

    /**
     * Parse la réponse d'OpenAI
     */
    private function parseOpenAIResponse($content): array
    {
        try {
            // Extraire le JSON de la réponse
            if (preg_match('/\{[^}]+\}/', $content, $matches)) {
                $data = json_decode($matches[0], true);
                
                if (isset($data['plate_number'])) {
                    return [
                        'success' => !empty($data['plate_number']),
                        'plate_number' => $this->normalizePlateNumber($data['plate_number']),
                        'confidence' => $data['confidence'] ?? 70,
                        'method' => 'openai',
                        'format_detected' => $data['format_detected'] ?? null,
                        'notes' => $data['notes'] ?? ''
                    ];
                }
            }
            
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'openai_parse_error'
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur parsing OpenAI response', [
                'error' => $e->getMessage(),
                'content' => $content
            ]);
            
            return [
                'success' => false,
                'plate_number' => null,
                'confidence' => 0,
                'method' => 'openai_parse_error'
            ];
        }
    }

    /**
     * Retourne les formats de plaques supportés
     */
    public function getSupportedFormats(): array
    {
        return $this->plateFormats;
    }

    /**
     * Vérifie si une clé API OpenAI est configurée
     */
    public function isOpenAIConfigured(): bool
    {
        return !empty($this->openaiApiKey);
    }
}
