<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-primary">
            <i class="bi bi-robot me-2"></i>Scanner OCR Ultra-Modern
        </h2>
    </x-slot>

    <style>
        /* ====== DESIGN ULTRA-MODERNE ====== */
        
        /* Variables CSS */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #13B497 0%, #0D9488 100%);
            --warning-gradient: linear-gradient(135deg, #FA8231 0%, #F5A623 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #ffffff;
            --text-secondary: #a0aec0;
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Container principal */
        .scanner-container {
            background: 
                radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(240, 147, 251, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            padding: 2rem 0;
            position: relative;
            overflow: hidden;
        }

        /* Particules flottantes */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--primary-gradient);
            border-radius: 50%;
            animation: float 20s infinite linear;
            opacity: 0.6;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        /* Glass morphisme */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Scanner frame ultra-moderne */
        .scanner-frame {
            position: relative;
            border: 2px solid transparent;
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 
                0 0 0 1px rgba(102, 126, 234, 0.5),
                0 0 20px rgba(102, 126, 234, 0.3),
                0 0 40px rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }

        .scanner-frame:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 0 0 1px rgba(102, 126, 234, 0.8),
                0 0 30px rgba(102, 126, 234, 0.5),
                0 0 60px rgba(102, 126, 234, 0.2);
        }

        .scanner-frame::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: var(--primary-gradient);
            border-radius: 26px;
            z-index: -1;
            animation: borderPulse 3s ease-in-out infinite;
        }

        @keyframes borderPulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.02); }
        }

        /* Zone de vidéo/photo */
        #video, #photo-preview {
            width: 100%;
            border-radius: 20px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            object-fit: cover;
            min-height: 320px;
            max-height: min(65vh, 520px);
            transition: all 0.3s ease;
        }

        /* Guide ROI moderne */
        .roi-guide {
            position: absolute;
            top: 25%;
            left: 15%;
            width: 70%;
            height: 50%;
            border: 2px solid rgba(102, 126, 234, 0.6);
            border-radius: 16px;
            pointer-events: none;
            z-index: 10;
            background: rgba(102, 126, 234, 0.05);
            backdrop-filter: blur(2px);
            animation: roiPulse 2s ease-in-out infinite;
        }

        @keyframes roiPulse {
            0%, 100% { border-color: rgba(102, 126, 234, 0.6); }
            50% { border-color: rgba(240, 147, 251, 0.8); }
        }

        /* Ligne de scan futuriste */
        .scan-line {
            position: absolute;
            width: 100%;
            height: 4px;
            background: var(--primary-gradient);
            animation: scan 2s ease-in-out infinite;
            z-index: 5;
            box-shadow: 
                0 0 20px rgba(102, 126, 234, 0.8),
                0 0 40px rgba(102, 126, 234, 0.4);
            border-radius: 2px;
        }

        @keyframes scan {
            0%, 100% { top: 10%; opacity: 0.8; }
            50% { top: 85%; opacity: 1; }
        }

        /* Panneau de contrôle moderne */
        .control-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        /* Boutons ultra-modernes */
        .btn-modern {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-modern:active {
            transform: translateY(-1px) scale(1.02);
        }

        .btn-modern:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            box-shadow: 0 4px 15px rgba(19, 180, 151, 0.3);
        }

        .btn-warning-modern {
            background: var(--warning-gradient);
            box-shadow: 0 4px 15px rgba(250, 130, 49, 0.3);
        }

        /* Affichage des résultats */
        .result-display {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            font-family: 'Courier New', monospace;
            font-size: 1.4rem;
            font-weight: bold;
            color: #67e8f9;
            text-align: center;
            letter-spacing: 3px;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            text-shadow: 0 0 10px rgba(103, 232, 249, 0.5);
        }

        .result-display.success {
            background: var(--success-gradient);
            color: white;
            animation: successPulse 1s ease-in-out infinite;
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .result-display.error {
            background: var(--warning-gradient);
            color: white;
            animation: errorShake 0.5s ease-in-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Barre de confiance */
        .confidence-bar {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            height: 12px;
            overflow: hidden;
            position: relative;
            margin-top: 1rem;
        }

        .confidence-fill {
            height: 100%;
            background: var(--success-gradient);
            transition: width 0.6s ease;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }

        .confidence-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        /* Badge de méthode */
        .method-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        /* Indicateurs de statut */
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .status-indicator.success {
            background: var(--success-gradient);
            color: white;
        }

        .status-indicator.error {
            background: var(--warning-gradient);
            color: white;
        }

        .status-indicator.scanning {
            background: var(--primary-gradient);
            color: white;
            animation: statusPulse 1s ease-in-out infinite;
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }

        .pulse-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: currentColor;
            animation: dotPulse 1.5s ease-in-out infinite;
        }

        @keyframes dotPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
        }

        /* Cartes d'information */
        .info-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .engine-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--glass-border);
        }

        .engine-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .scanner-container {
                padding: 1rem 0;
            }
            
            .control-panel {
                padding: 1rem;
                margin-top: 1rem;
            }
            
            .btn-modern {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
            
            .result-display {
                font-size: 1.1rem;
                letter-spacing: 2px;
            }
        }

        /* Animations supplémentaires */
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-up {
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>

    <!-- Particules flottantes -->
    <div class="particles" id="particles"></div>

    <div class="container">
        <div class="row">
            <!-- Zone de scanner -->
            <div class="col-12 col-lg-8 mb-4">
                <div class="glass-card fade-in">
                    <div class="scanner-frame">
                        <video id="video" autoplay playsinline></video>
                        <img id="photo-preview" style="display: none;" alt="Photo preview">
                        <div class="roi-guide"></div>
                        <div class="scan-line"></div>
                    </div>
                    
                    <!-- Contrôles principaux -->
                    <div class="control-panel mt-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <button id="btn-capture" class="btn-modern w-100">
                                    <i class="bi bi-camera-fill me-2"></i>Capturer
                                </button>
                            </div>
                            <div class="col-12 col-md-4">
                                <button id="btn-upload" class="btn-modern btn-secondary-modern w-100">
                                    <i class="bi bi-upload me-2"></i>Importer
                                </button>
                            </div>
                            <div class="col-12 col-md-4">
                                <button id="btn-live-scan" class="btn-modern btn-success-modern w-100">
                                    <i class="bi bi-broadcast me-2"></i>Scan Live
                                </button>
                            </div>
                        </div>
                        
                        <input type="file" id="photo-input" accept="image/*" style="display: none;">
                    </div>
                </div>
            </div>

            <!-- Panneau de résultats et options -->
            <div class="col-12 col-lg-4">
                <!-- Résultats -->
                <div class="glass-card mb-4 slide-up">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-qr-code-scan me-2"></i>Résultat OCR
                    </h5>
                    <div class="result-display" id="result-display">EN ATTENTE</div>
                    <div class="confidence-bar">
                        <div class="confidence-fill" id="confidence-fill" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-white">
                            Confiance: <span id="confidence-text">0%</span>
                        </small>
                        <div class="method-badge" id="method-badge">
                            EN ATTENTE
                        </div>
                    </div>
                </div>

                <!-- Statut -->
                <div class="status-indicator mb-4" id="status-indicator">
                    <div class="pulse-dot"></div>
                    <span id="status-text">Scanner prêt</span>
                </div>

                <!-- Moteurs OCR -->
                <div class="info-card mb-4">
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

                <!-- Options avancées -->
                <div class="info-card">
                    <h6 class="text-white mb-3">
                        <i class="bi bi-gear me-2"></i>
                        Options
                    </h6>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="use-openai" checked>
                        <label class="form-check-label text-white" for="use-openai">
                            <i class="bi bi-robot me-2"></i>Utiliser OpenAI Vision
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="enhance-image" checked>
                        <label class="form-check-label text-white" for="enhance-image">
                            <i class="bi bi-magic me-2"></i>Améliorer l'image
                        </label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto-scan" checked>
                        <label class="form-check-label text-white" for="auto-scan">
                            <i class="bi bi-lightning me-2"></i>Scan automatique
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let video, stream, isScanning = false, isLiveScanning = false;
        let liveScanInterval;
        
        // Éléments DOM
        const videoElement = document.getElementById('video');
        const photoPreview = document.getElementById('photo-preview');
        const btnCapture = document.getElementById('btn-capture');
        const btnUpload = document.getElementById('btn-upload');
        const btnLiveScan = document.getElementById('btn-live-scan');
        const photoInput = document.getElementById('photo-input');
        const resultDisplay = document.getElementById('result-display');
        const confidenceFill = document.getElementById('confidence-fill');
        const confidenceText = document.getElementById('confidence-text');
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const methodBadge = document.getElementById('method-badge');
        const useOpenAI = document.getElementById('use-openai');
        const enhanceImage = document.getElementById('enhance-image');
        const autoScan = document.getElementById('auto-scan');

        // Initialisation
        document.addEventListener('DOMContentLoaded', async () => {
            createParticles();
            await initializeCamera();
            setupEventListeners();
            startAutoScan();
        });

        // Créer les particules flottantes
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Initialiser la caméra
        async function initializeCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });

                videoElement.srcObject = stream;
                videoElement.play();

                updateStatus('success', 'Scanner prêt');

            } catch (error) {
                console.error('Camera error:', error);
                updateStatus('error', 'Accès caméra refusé');
            }
        }

        // Configurer les écouteurs d'événements
        function setupEventListeners() {
            btnCapture.addEventListener('click', captureAndAnalyze);
            btnUpload.addEventListener('click', () => photoInput.click());
            btnLiveScan.addEventListener('click', toggleLiveScan);
            photoInput.addEventListener('change', handleFileUpload);
        }

        // Capturer et analyser
        async function captureAndAnalyze() {
            if (isScanning) return;

            isScanning = true;
            btnCapture.disabled = true;
            updateStatus('scanning', 'Analyse en cours...');

            try {
                const canvas = document.createElement('canvas');
                canvas.width = videoElement.videoWidth;
                canvas.height = videoElement.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(videoElement, 0, 0);

                canvas.toBlob(async (blob) => {
                    await sendToServer(blob, 'capture.jpg');
                }, 'image/jpeg', 0.9);

            } catch (error) {
                console.error('Capture error:', error);
                updateStatus('error', 'Erreur de capture');
            } finally {
                isScanning = false;
                btnCapture.disabled = false;
            }
        }

        // Gérer l'upload de fichier
        async function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            isScanning = true;
            btnUpload.disabled = true;
            updateStatus('scanning', 'Analyse en cours...');

            // Afficher l'aperçu
            const reader = new FileReader();
            reader.onload = (e) => {
                photoPreview.src = e.target.result;
                photoPreview.style.display = 'block';
                videoElement.style.display = 'none';
            };
            reader.readAsDataURL(file);

            try {
                await sendToServer(file, file.name);
            } catch (error) {
                console.error('Upload error:', error);
                updateStatus('error', 'Erreur d\'upload');
            } finally {
                isScanning = false;
                btnUpload.disabled = false;
            }
        }

        // Envoyer au serveur
        async function sendToServer(imageBlob, filename) {
            const formData = new FormData();
            formData.append('image', imageBlob, filename);
            formData.append('use_openai', useOpenAI.checked);
            formData.append('enhance_image', enhanceImage.checked);

            const response = await fetch('{{ route("agent.scanner.advanced") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
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
                
                // Redirection automatique
                setTimeout(() => {
                    window.location.href = result.redirect_url;
                }, 2000);
            } else {
                updateResult(null, 0, 'error', false);
                updateStatus('error', result.error || 'Échec de la détection');
            }
        }

        // Mettre à jour le statut
        function updateStatus(type, message) {
            statusIndicator.className = `status-indicator ${type}`;
            statusText.textContent = message;
        }

        // Mettre à jour le résultat
        function updateResult(plate, confidence, method, success = true) {
            resultDisplay.textContent = plate || 'NON DÉTECTÉ';
            resultDisplay.className = `result-display ${success ? 'success' : 'error'}`;
            
            confidenceFill.style.width = `${confidence}%`;
            confidenceText.textContent = `${Math.round(confidence)}%`;
            
            methodBadge.textContent = method.toUpperCase();
            methodBadge.className = 'method-badge';
        }

        // Toggle scan live
        function toggleLiveScan() {
            if (isLiveScanning) {
                stopLiveScan();
            } else {
                startLiveScan();
            }
        }

        // Démarrer le scan live
        function startLiveScan() {
            isLiveScanning = true;
            btnLiveScan.innerHTML = '<i class="bi bi-stop-circle me-2"></i>Arrêter';
            btnLiveScan.className = 'btn-modern btn-warning-modern w-100';
            
            liveScanInterval = setInterval(async () => {
                if (!isScanning && autoScan.checked) {
                    await captureAndAnalyze();
                }
            }, 3000);
            
            updateStatus('scanning', 'Scan live actif');
        }

        // Arrêter le scan live
        function stopLiveScan() {
            isLiveScanning = false;
            btnLiveScan.innerHTML = '<i class="bi bi-broadcast me-2"></i>Scan Live';
            btnLiveScan.className = 'btn-modern btn-success-modern w-100';
            
            if (liveScanInterval) {
                clearInterval(liveScanInterval);
            }
            
            updateStatus('success', 'Scanner prêt');
        }

        // Démarrer le scan automatique
        function startAutoScan() {
            if (autoScan.checked && !isLiveScanning) {
                setTimeout(() => {
                    if (autoScan.checked && !isScanning) {
                        captureAndAnalyze();
                    }
                }, 5000);
            }
        }

        // Nettoyage
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            if (liveScanInterval) {
                clearInterval(liveScanInterval);
            }
        });
    </script>
</x-app-layout>
