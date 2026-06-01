<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-primary">
            <i class="bi bi-robot me-2"></i>Scanner OCR Avancé avec IA
        </h2>
    </x-slot>

    <style>
        .scanner-container {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.85) 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .scanner-frame {
            position: relative;
            border: 3px solid #dc2626;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 8px 32px rgba(220, 38, 38, 0.3);
        }

        .scanner-frame::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #dc2626, #f59e0b, #dc2626);
            border-radius: 18px;
            z-index: -1;
            animation: borderGlow 2s linear infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }

        #video, #photo-preview {
            width: 100%;
            border-radius: 12px;
            background: #000;
            object-fit: cover;
            min-height: 280px;
            max-height: min(60vh, 480px);
        }

        .roi-guide {
            position: absolute;
            top: 30%;
            left: 10%;
            width: 80%;
            height: 40%;
            border: 2px dashed rgba(220, 38, 38, 0.8);
            border-radius: 8px;
            pointer-events: none;
            z-index: 10;
            background: rgba(220, 38, 38, 0.05);
        }

        .scan-line {
            position: absolute;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #dc2626, #f59e0b, transparent);
            animation: scan 2s ease-in-out infinite;
            z-index: 5;
            box-shadow: 0 0 10px rgba(220, 38, 38, 0.5);
        }

        @keyframes scan {
            0%, 100% { top: 10%; opacity: 0.6; }
            50% { top: 85%; opacity: 1; }
        }

        .control-panel {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(51, 65, 85, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 1.5rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .result-display {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(220, 38, 38, 0.3);
            border-radius: 12px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
            color: #67e8f9;
            text-align: center;
            letter-spacing: 2px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .result-display.success {
            border-color: rgba(16, 185, 129, 0.5);
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            animation: successPulse 0.5s ease;
        }

        .result-display.error {
            border-color: rgba(239, 68, 68, 0.5);
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            animation: shake 0.5s ease;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .confidence-meter {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .method-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .method-badge.openai {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .method-badge.tesseract {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .method-badge.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .format-info {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 1rem;
        }

        .format-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #e2e8f0;
        }

        .format-pattern {
            font-family: 'Courier New', monospace;
            background: rgba(0, 0, 0, 0.3);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            color: #f59e0b;
        }

        .enhancement-toggle {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.1));
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .btn-scan-primary {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.3);
        }

        .btn-scan-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(220, 38, 38, 0.4);
            background: linear-gradient(135deg, #b91c1c, #991b1b);
        }

        .btn-scan-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-scan-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-scan-secondary:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .status-indicator.scanning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #f59e0b;
        }

        .status-indicator.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }

        .status-indicator.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .pulse-dot.scanning {
            background: #f59e0b;
        }

        .pulse-dot.success {
            background: #10b981;
        }

        .pulse-dot.error {
            background: #ef4444;
        }
    </style>

    <div class="scanner-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <!-- Scanner Frame -->
                    <div class="scanner-frame mb-4">
                        <video id="video" autoplay playsinline muted></video>
                        <img id="photo-preview" alt="Aperçu photo" style="display: none;" />
                        <div class="roi-guide" aria-hidden="true">
                            <div style="text-align: center; color: rgba(220, 38, 38, 0.6); font-size: 0.75rem; margin-top: 35%;">
                                <i class="bi bi-camera-fill"></i> CADRE DE DÉTECTION
                            </div>
                        </div>
                        <div class="scan-line" aria-hidden="true"></div>
                    </div>

                    <!-- Control Panel -->
                    <div class="control-panel">
                        <!-- Status Indicator -->
                        <div id="status-indicator" class="status-indicator">
                            <div class="pulse-dot"></div>
                            <span id="status-text">Initialisation du scanner...</span>
                        </div>

                        <!-- Result Display -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-cyan-300 mb-2">
                                <i class="bi bi-qr-code-scan me-2"></i>Plaque Détectée
                            </label>
                            <div id="result-display" class="result-display">
                                EN ATTENTE DE SCAN...
                            </div>
                            <div class="confidence-meter">
                                <div id="confidence-fill" class="confidence-fill" style="width: 0%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    Confiance: <span id="confidence-text">0%</span>
                                </small>
                                <small id="method-badge" class="method-badge">
                                    EN ATTENTE
                                </small>
                            </div>
                        </div>

                        <!-- Moteurs OCR -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="info-card">
                                    <h6 class="text-white mb-3">
                                        <i class="bi bi-cpu me-2"></i>
                                        Moteurs OCR
                                    </h6>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="engine-status">
                                            <span class="engine-name">OpenAI Vision</span>
                                            <span class="badge {{ $openAIEnabled ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $openAIEnabled ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </div>
                                        <div class="engine-status">
                                            <span class="engine-name">Tesseract OCR</span>
                                            <span class="badge {{ $tesseractEnabled ? 'bg-success' : 'bg-danger' }}">
                                                {{ $tesseractEnabled ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="info-card">
                                    <h6 class="text-white mb-3">
                                        <i class="bi bi-gear me-2"></i>
                                        Options
                                    </h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="use_openai" checked>
                                        <label class="form-check-label small" for="use_openai">
                                            Utiliser OpenAI Vision
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="enhance_image" checked>
                                        <label class="form-check-label small" for="enhance_image">
                                            Améliorer l'image
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="info-card">
                                    <h6 class="text-white mb-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Statut OCR
                                    </h6>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="status-item">
                                            <span class="status-label">Hybrid OCR:</span>
                                            <span class="badge bg-info">Activé</span>
                                        </div>
                                        <div class="status-item">
                                            <span class="status-label">Fusion:</span>
                                            <span class="badge bg-success">Auto</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhancement Options -->
                        <div class="enhancement-toggle">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="use-openai" checked>
                                        <label class="form-check-label text-white" for="use-openai">
                                            <i class="bi bi-cpu me-2"></i>Utiliser OpenAI Vision (Recommandé)
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Analyse avancée avec IA pour meilleure précision
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enhance-image" checked>
                                        <label class="form-check-label text-white" for="enhance-image">
                                            <i class="bi bi-magic me-2"></i>Amélioration automatique
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Optimisation du contraste et netteté
                                    </small>
                                </div>
                            </div>
                            
                            <div class="row align-items-center mt-3">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="use-feschrift" checked>
                                        <label class="form-check-label text-white" for="use-feschrift">
                                            <i class="bi bi-shield-exclamation me-2"></i>Détection FE-Schrift (Anti-fraude)
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Reconnaissance spécialisée pour les caractères discontinus et filigranes de sécurité
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <button type="button" id="btn-capture" class="btn-scan-primary w-100" disabled>
                                    <i class="bi bi-camera-fill me-2"></i>Capturer
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="btn-upload" class="btn-scan-secondary w-100" disabled>
                                    <i class="bi bi-image me-2"></i>Importer Photo
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="btn-live-scan" class="btn-scan-secondary w-100" disabled>
                                    <i class="bi bi-broadcast me-2"></i>Scan Continu
                                </button>
                            </div>
                        </div>

                        <!-- Format Information -->
                        <div class="format-info">
                            <h6 class="text-cyan-300 mb-3">
                                <i class="bi bi-info-circle me-2"></i>Détection FE-Schrift - Style Anti-Fraude RDC
                            </h6>
                            
                            <div class="alert alert-warning" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #f59e0b; margin-bottom: 1rem;">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                <strong>Style FE-Schrift Reconnu</strong> - Le scanner détecte automatiquement les caractères discontinus et filigranes de sécurité des plaques RDC.
                            </div>
                            
                            <h6 class="text-warning mb-2">
                                <i class="bi bi-shield-check me-2"></i>Caractéristiques FE-Schrift Gérées
                            </h6>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-white">Chiffre 4 ouvert en haut (anti-transformation)</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-white">Chiffre 0 avec hachures diagonales (filigrane)</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-white">Caractères discontinus et coupures de sécurité</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-white">Stries diagonales dans les caractères noirs</span>
                            </div>
                            
                            <h6 class="text-info mb-2 mt-3">
                                <i class="bi bi-arrow-repeat me-2"></i>Corrections Automatiques FE-Schrift
                            </h6>
                            <div class="format-item">
                                <i class="bi bi-arrow-right-circle text-info me-2"></i>
                                <span class="text-white">H/h → 4 (chiffre 4 ouvert)</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-arrow-right-circle text-info me-2"></i>
                                <span class="text-white">Q → 0 (zéro avec hachures)</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-arrow-right-circle text-info me-2"></i>
                                <span class="text-white">g/q → 9 (chiffre 9 stylisé)</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-arrow-right-circle text-info me-2"></i>
                                <span class="text-white">b → 6, B → 8, l/I → 1</span>
                            </div>
                            
                            <h6 class="text-success mb-2 mt-3">
                                <i class="bi bi-speedometer2 me-2"></i>Double Analyse Optimisée
                            </h6>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-white">Analyse standard + Analyse FE-Schrift spécialisée</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-white">Prétraitement contrasté pour caractères discontinus</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="text-white">Bonus de score pour détection FE-Schrift</span>
                            </div>
                            <div class="format-item">
                                <i class="bi bi-check-circle-fill text-warning me-2"></i>
                                <span class="text-white">OpenAI + Tesseract optimisés pour anti-fraude</span>
                            </div>
                            
                            <div class="mt-3 pt-3 border-top border-secondary">
                                <h6 class="text-danger mb-2">
                                    <i class="bi bi-funnel me-2"></i>Filtres Intelligents Actifs
                                </h6>
                                <div class="format-item">
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    <span class="text-white">Ignore: RDC, CONGO, DRC, KATANGA, LUBUMBASHI</span>
                                </div>
                                <div class="format-item">
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    <span class="text-white">Ignore: Années, logos, drapeaux, décorations</span>
                                </div>
                                <div class="format-item">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span class="text-white">Focus: Numéro FE-Schrift au centre</span>
                                </div>
                            </div>
                        </div>

                        <!-- OpenAI Status -->
                        @if(!$openAIEnabled)
                            <div class="alert alert-warning mt-3" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #f59e0b;">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>OpenAI non configuré</strong> - Le scanner utilisera uniquement Tesseract. 
                                Pour des performances optimales, configurez la clé API OpenAI.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="file" id="photo-input" class="visually-hidden" accept="image/*" capture="environment">

    <script>
        (function () {
            const video = document.getElementById('video');
            const photoPreview = document.getElementById('photo-preview');
            const resultDisplay = document.getElementById('result-display');
            const confidenceFill = document.getElementById('confidence-fill');
            const confidenceText = document.getElementById('confidence-text');
            const methodBadge = document.getElementById('method-badge');
            const statusIndicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('status-text');
            const btnCapture = document.getElementById('btn-capture');
            const btnUpload = document.getElementById('btn-upload');
            const btnLiveScan = document.getElementById('btn-live-scan');
            const photoInput = document.getElementById('photo-input');
            const useOpenAI = document.getElementById('use-openai');
            const enhanceImage = document.getElementById('enhance-image');

            let stream = null;
            let isScanning = false;
            let isLiveScanning = false;
            let liveScanInterval = null;

            // Initialize camera
            async function initCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: 'environment' },
                            width: { ideal: 1920 },
                            height: { ideal: 1080 }
                        },
                        audio: false
                    });

                    video.srcObject = stream;
                    video.play();

                    updateStatus('success', 'Scanner prêt');
                    enableButtons(true);

                } catch (error) {
                    console.error('Camera error:', error);
                    updateStatus('error', 'Accès caméra refusé');
                    enableButtons(false);
                }
            }

            // Update status indicator
            function updateStatus(type, message) {
                statusIndicator.className = `status-indicator ${type}`;
                const pulseDot = statusIndicator.querySelector('.pulse-dot');
                pulseDot.className = `pulse-dot ${type}`;
                statusText.textContent = message;
            }

            // Enable/disable buttons
            function enableButtons(enabled) {
                btnCapture.disabled = !enabled;
                btnUpload.disabled = !enabled;
                btnLiveScan.disabled = !enabled;
            }

            // Update result display
            function updateResult(plate, confidence, method, success = true) {
                resultDisplay.textContent = plate || 'NON DÉTECTÉ';
                resultDisplay.className = `result-display ${success ? 'success' : 'error'}`;
                
                confidenceFill.style.width = `${confidence}%`;
                confidenceText.textContent = `${Math.round(confidence)}%`;
                
                methodBadge.textContent = method.toUpperCase();
                methodBadge.className = `method-badge ${method.toLowerCase()}`;
            }

            // Capture and analyze
            async function captureAndAnalyze() {
                if (isScanning) return;

                isScanning = true;
                btnCapture.disabled = true;
                updateStatus('scanning', 'Analyse en cours...');

                try {
                    // Capture frame
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0);

                    // Convert to blob
                    canvas.toBlob(async (blob) => {
                        const formData = new FormData();
                        formData.append('image', blob, 'scan.jpg');
                        formData.append('use_openai', useOpenAI.checked);
                        formData.append('enhance_image', enhanceImage.checked);

                        const response = await fetch('{{ route("agent.scanner.advanced") }}'), {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            updateResult(
                                result.plate_number,
                                result.confidence,
                                result.method,
                                true
                            );
                            updateStatus('success', 'Plaque détectée avec succès');
                            
                            // Redirect to vehicle details
                            setTimeout(() => {
                                window.location.href = result.redirect_url;
                            }, 1500);
                        } else {
                            updateResult(null, 0, 'error', false);
                            updateStatus('error', result.error || 'Échec de la détection');
                        }
                    }, 'image/jpeg', 0.9);

                } catch (error) {
                    console.error('Scan error:', error);
                    updateStatus('error', 'Erreur technique');
                } finally {
                    isScanning = false;
                    btnCapture.disabled = false;
                }
            }

            // Live scanning
            async function startLiveScan() {
                if (isLiveScanning) return;

                isLiveScanning = true;
                btnLiveScan.textContent = 'Arrêter Scan';
                btnLiveScan.innerHTML = '<i class="bi bi-stop-circle me-2"></i>Arrêter Scan';

                liveScanInterval = setInterval(async () => {
                    if (!isScanning) {
                        await captureAndAnalyze();
                    }
                }, 2000);
            }

            function stopLiveScan() {
                isLiveScanning = false;
                if (liveScanInterval) {
                    clearInterval(liveScanInterval);
                    liveScanInterval = null;
                }
                btnLiveScan.innerHTML = '<i class="bi bi-broadcast me-2"></i>Scan Continu';
            }

            // Upload image
            async function uploadImage(file) {
                if (!file) return;

                isScanning = true;
                btnUpload.disabled = true;
                updateStatus('scanning', 'Analyse de l\'image...');

                // Show preview
                const reader = new FileReader();
                reader.onload = async (e) => {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                    video.style.display = 'none';

                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('use_openai', useOpenAI.checked);
                    formData.append('enhance_image', enhanceImage.checked);

                    try {
                        const response = await fetch('{{ route("agent.scanner.advanced") }}'), {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            updateResult(
                                result.plate_number,
                                result.confidence,
                                result.method,
                                true
                            );
                            updateStatus('success', 'Plaque détectée avec succès');
                            
                            setTimeout(() => {
                                window.location.href = result.redirect_url;
                            }, 1500);
                        } else {
                            updateResult(null, 0, 'error', false);
                            updateStatus('error', result.error || 'Échec de la détection');
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                        updateStatus('error', 'Erreur technique');
                    } finally {
                        isScanning = false;
                        btnUpload.disabled = false;
                    }
                };
                reader.readAsDataURL(file);
            };

            // Event listeners
            btnCapture.addEventListener('click', captureAndAnalyze);

            btnUpload.addEventListener('click', () => {
                photoInput.click();
            });

            btnLiveScan.addEventListener('click', () => {
                if (isLiveScanning) {
                    stopLiveScan();
                } else {
                    startLiveScan();
                }
            });

            photoInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                uploadImage(file);
            });

            // Cleanup
            window.addEventListener('beforeunload', () => {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
                if (liveScanInterval) {
                    clearInterval(liveScanInterval);
                }
            });

            // Initialize
            initCamera();
        })();
    </script>
</x-app-layout>
