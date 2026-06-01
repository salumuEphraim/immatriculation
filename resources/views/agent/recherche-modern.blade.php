<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-primary">
            <i class="bi bi-camera2 me-2"></i>Scanner OCR Ultra-Moderne
        </h2>
    </x-slot>

    @php
        $resultatUrlTemplate = route('shared.resultat', ['plaque' => '__PLAQUE__']);
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <!-- Particules flottantes -->
    <div class="particles" id="particles"></div>

    <div class="scanner-modern-container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-9">
                <!-- Carte principale du scanner -->
                <div class="scanner-card glass-card fade-in">
                    <div class="scanner-header">
                        <div class="header-content">
                            <h5 class="mb-1">
                                <i class="bi bi-qr-code-scan me-2"></i>
                                Scanner OCR Intégral
                            </h5>
                            <p class="text-white-50 mb-0 small">
                                Intelligence artificielle + Tesseract pour une précision exceptionnelle
                            </p>
                        </div>
                        <div class="header-indicators">
                            <div class="indicator-item">
                                <div class="indicator-dot active"></div>
                                <span class="indicator-text">Tesseract</span>
                            </div>
                            <div class="indicator-item">
                                <div class="indicator-dot active"></div>
                                <span class="indicator-text">Auto-Scan</span>
                            </div>
                        </div>
                    </div>

                    <div class="scanner-body">
                        <!-- Zone de capture -->
                        <div class="scanner-frame-modern" id="scanner-frame">
                            <video id="video" autoplay playsinline muted></video>
                            <img id="photo-preview" alt="Aperçu photo" />
                            <div class="roi-guide-modern" aria-hidden="true">
                                <div class="roi-corners">
                                    <div class="corner corner-tl"></div>
                                    <div class="corner corner-tr"></div>
                                    <div class="corner corner-bl"></div>
                                    <div class="corner corner-br"></div>
                                </div>
                                <div class="roi-label">ZONE DE DÉTECTION</div>
                            </div>
                            <div class="scan-line-modern" aria-hidden="true">
                                <div class="scan-beam"></div>
                            </div>
                            <div class="scan-overlay"></div>
                        </div>
                        
                        <canvas id="canvas"></canvas>

                        <!-- Panneau de contrôle moderne -->
                        <div class="control-panel-modern">
                            <div class="control-row">
                                <div class="control-group">
                                    <label class="control-label">
                                        <i class="bi bi-lightning-charge me-2"></i>
                                        Mode Auto
                                    </label>
                                    <div class="switch-container">
                                        <input type="checkbox" id="auto-scan" class="switch-input" checked>
                                        <label for="auto-scan" class="switch-label">
                                            <span class="switch-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="control-row">
                                <div class="result-group">
                                    <label class="result-label">
                                        <i class="bi bi-qr-code me-2"></i>
                                        Plaque Détectée
                                    </label>
                                    <div class="result-display-modern">
                                        <input type="text" id="resultat-plaque" 
                                               class="result-input" readonly 
                                               placeholder="EN ATTENTE...">
                                        <div class="result-confidence" id="confidence-hint">
                                            <div class="confidence-bar">
                                                <div class="confidence-fill" id="confidence-fill"></div>
                                            </div>
                                            <span class="confidence-text">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="control-row">
                                <div class="button-group">
                                    <button type="button" id="btn-scan" 
                                            class="btn-modern btn-primary-modern">
                                        <i class="bi bi-camera-fill"></i>
                                        <span>Capturer</span>
                                    </button>
                                    <button type="button" id="btn-upload" 
                                            class="btn-modern btn-secondary-modern">
                                        <i class="bi bi-image"></i>
                                        <span>Importer</span>
                                    </button>
                                    <button type="button" id="btn-live" 
                                            class="btn-modern btn-success-modern">
                                        <i class="bi bi-broadcast"></i>
                                        <span>Scan Live</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <input type="file" id="photo-input" class="visually-hidden" 
                               accept="image/*" capture="environment">
                    </div>
                </div>
            </div>
        </div>

        <!-- Panneau de statut -->
        <div class="status-panel glass-card slide-up">
            <h6 class="status-title">
                <i class="bi bi-activity me-2"></i>
                État du Scanner
            </h6>
            <div class="status-content">
                <div class="status-item" id="status-item">
                    <div class="status-icon">
                        <div class="status-dot" id="status-dot"></div>
                    </div>
                    <div class="status-info">
                        <div class="status-text" id="status-text">Initialisation...</div>
                        <div class="status-subtitle" id="status-subtitle">Préparation du système</div>
                    </div>
                </div>
                
                <div class="metrics-grid">
                    <div class="metric-item">
                        <div class="metric-value" id="scan-count">0</div>
                        <div class="metric-label">Scans</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-value" id="success-rate">0%</div>
                        <div class="metric-label">Succès</div>
                    </div>
                    <div class="metric-item">
                        <div class="metric-value" id="avg-confidence">0%</div>
                        <div class="metric-label">Confiance</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ====== VARIABLES CSS ====== */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #13B497 0%, #0D9488 100%);
            --warning-gradient: linear-gradient(135deg, #FA8231 0%, #F5A623 100%);
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
            --text-primary: #ffffff;
            --text-secondary: #a0aec0;
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* ====== CONTAINER PRINCIPAL ====== */
        .scanner-modern-container {
            background: 
                radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(240, 147, 251, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
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
            width: 3px;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 50%;
            animation: float 25s infinite linear;
            opacity: 0.7;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.7; }
            90% { opacity: 0.7; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        /* ====== GLASS MORPHISM ====== */
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
            animation: shimmer 4s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* ====== CARTE SCANNER ====== */
        .scanner-card {
            border: none;
            transition: all 0.3s ease;
        }

        .scanner-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 25px 30px -5px rgba(0, 0, 0, 0.15),
                0 15px 15px -5px rgba(0, 0, 0, 0.08);
        }

        .scanner-header {
            background: var(--glass-bg);
            border-bottom: 1px solid var(--glass-border);
            border-radius: 24px 24px 0 0;
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h5 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-content p {
            color: var(--text-secondary);
            margin: 0;
        }

        .header-indicators {
            display: flex;
            gap: 1.5rem;
        }

        .indicator-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .indicator-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e53e3e;
            transition: all 0.3s ease;
        }

        .indicator-dot.active {
            background: var(--success-gradient);
            box-shadow: 0 0 10px rgba(19, 180, 151, 0.5);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
        }

        .indicator-text {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ====== ZONE DE SCAN ====== */
        .scanner-body {
            padding: 2rem;
        }

        .scanner-frame-modern {
            position: relative;
            border: 2px solid transparent;
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 
                0 0 0 1px rgba(102, 126, 234, 0.5),
                0 0 30px rgba(102, 126, 234, 0.3),
                0 0 60px rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .scanner-frame-modern::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: var(--primary-gradient);
            border-radius: 22px;
            z-index: -1;
            animation: borderPulse 3s ease-in-out infinite;
        }

        @keyframes borderPulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.02); }
        }

        #video, #photo-preview {
            width: 100%;
            border-radius: 18px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            object-fit: cover;
            min-height: 300px;
            max-height: min(60vh, 480px);
            transition: all 0.3s ease;
        }

        /* ROI Guide moderne */
        .roi-guide-modern {
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

        .roi-corners {
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
        }

        .corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(102, 126, 234, 0.8);
        }

        .corner-tl {
            top: 0;
            left: 0;
            border-right: none;
            border-bottom: none;
            border-radius: 16px 0 0 0;
        }

        .corner-tr {
            top: 0;
            right: 0;
            border-left: none;
            border-bottom: none;
            border-radius: 0 16px 0 0;
        }

        .corner-bl {
            bottom: 0;
            left: 0;
            border-right: none;
            border-top: none;
            border-radius: 0 0 0 16px;
        }

        .corner-br {
            bottom: 0;
            right: 0;
            border-left: none;
            border-top: none;
            border-radius: 0 0 16px 0;
        }

        .roi-label {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-gradient);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Ligne de scan moderne */
        .scan-line-modern {
            position: absolute;
            width: 100%;
            height: 4px;
            z-index: 5;
            animation: scan 2s ease-in-out infinite;
        }

        .scan-beam {
            height: 100%;
            background: var(--primary-gradient);
            box-shadow: 
                0 0 20px rgba(102, 126, 234, 0.8),
                0 0 40px rgba(102, 126, 234, 0.4);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }

        .scan-beam::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: beamShimmer 1s infinite;
        }

        @keyframes beamShimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        @keyframes scan {
            0%, 100% { top: 10%; opacity: 0.8; }
            50% { top: 85%; opacity: 1; }
        }

        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, 
                transparent 0%, 
                rgba(102, 126, 234, 0.05) 50%, 
                transparent 100%);
            pointer-events: none;
            animation: overlayPulse 3s ease-in-out infinite;
        }

        @keyframes overlayPulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.7; }
        }

        /* ====== PANNEAU DE CONTRÔLE ====== */
        .control-panel-modern {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .control-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .control-row:last-child {
            margin-bottom: 0;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .control-label {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }

        /* Switch moderne */
        .switch-container {
            position: relative;
        }

        .switch-input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-label {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 32px;
            cursor: pointer;
        }

        .switch-slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            border-radius: 34px;
        }

        .switch-slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 4px;
            bottom: 4px;
            background: white;
            transition: all 0.3s ease;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .switch-input:checked + .switch-label .switch-slider {
            background: var(--success-gradient);
        }

        .switch-input:checked + .switch-label .switch-slider:before {
            transform: translateX(28px);
        }

        /* Groupe de résultats */
        .result-group {
            flex: 1;
        }

        .result-label {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
        }

        .result-display-modern {
            position: relative;
        }

        .result-input {
            width: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 2px solid var(--glass-border);
            border-radius: 16px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 1.8rem;
            font-weight: bold;
            color: #67e8f9;
            text-align: center;
            letter-spacing: 4px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-shadow: 0 0 10px rgba(103, 232, 249, 0.3);
        }

        .result-input:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.8);
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
        }

        .result-confidence {
            margin-top: 1rem;
        }

        .confidence-bar {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            height: 8px;
            overflow: hidden;
            position: relative;
        }

        .confidence-fill {
            height: 100%;
            background: var(--success-gradient);
            transition: width 0.6s ease;
            border-radius: 12px;
            position: relative;
            width: 0%;
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

        .confidence-text {
            display: block;
            text-align: center;
            margin-top: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Groupe de boutons */
        .button-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-modern {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
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

        .btn-primary-modern {
            background: var(--primary-gradient);
        }

        .btn-secondary-modern {
            background: var(--secondary-gradient);
            box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            box-shadow: 0 4px 15px rgba(19, 180, 151, 0.3);
        }

        /* ====== PANNEAU DE STATUT ====== */
        .status-panel {
            position: fixed;
            top: 2rem;
            right: 2rem;
            width: 320px;
            max-width: calc(100vw - 4rem);
            z-index: 1000;
        }

        .status-title {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .status-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .status-icon {
            position: relative;
        }

        .status-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e53e3e;
            animation: statusPulse 2s ease-in-out infinite;
        }

        .status-dot.active {
            background: var(--success-gradient);
            box-shadow: 0 0 15px rgba(19, 180, 151, 0.5);
        }

        @keyframes statusPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        .status-info {
            flex: 1;
        }

        .status-text {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .status-subtitle {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        /* Métriques */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .metric-item {
            text-align: center;
            padding: 1rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
        }

        .metric-value {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .metric-label {
            color: var(--text-secondary);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ====== ANIMATIONS ====== */
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

        @keyframes roiPulse {
            0%, 100% { border-color: rgba(102, 126, 234, 0.6); }
            50% { border-color: rgba(240, 147, 251, 0.8); }
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 1200px) {
            .status-panel {
                position: static;
                width: 100%;
                margin-top: 2rem;
            }
        }

        @media (max-width: 768px) {
            .scanner-modern-container {
                padding: 1rem 0;
            }
            
            .scanner-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .control-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .button-group {
                justify-content: center;
            }
            
            .btn-modern {
                padding: 0.75rem 1rem;
                font-size: 0.8rem;
            }
            
            .result-input {
                font-size: 1.4rem;
                letter-spacing: 2px;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Variables globales
        let video, stream, worker = null;
        let isScanning = false, isLiveScanning = false;
        let scanCount = 0, successCount = 0, totalConfidence = 0;
        let liveScanInterval;
        
        // Éléments DOM
        const videoElement = document.getElementById('video');
        const photoPreview = document.getElementById('photo-preview');
        const btnScan = document.getElementById('btn-scan');
        const btnUpload = document.getElementById('btn-upload');
        const btnLive = document.getElementById('btn-live');
        const photoInput = document.getElementById('photo-input');
        const resultInput = document.getElementById('resultat-plaque');
        const confidenceFill = document.getElementById('confidence-fill');
        const confidenceText = document.querySelector('.confidence-text');
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const statusSubtitle = document.getElementById('status-subtitle');
        const autoScan = document.getElementById('auto-scan');
        
        // Constantes
        const STABLE_NEEDED = 2;
        const MIN_PLAQUE_LEN = 3;
        const AUTO_INTERVAL_MS = 2000;
        const MIN_CONFIDENCE_SOFT = 35;
        const RESULTAT_TEMPLATE = @json($resultatUrlTemplate);
        
        let lastStable = '';
        let stableCount = 0;
        let navigating = false;

        // Initialisation
        document.addEventListener('DOMContentLoaded', async () => {
            createParticles();
            await initializeCamera();
            setupEventListeners();
            updateStatus('success', 'Scanner prêt', 'Tesseract initialisé');
        });

        // Créer les particules flottantes
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            for (let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 25 + 's';
                particle.style.animationDuration = (20 + Math.random() * 15) + 's';
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
                
                // Initialiser Tesseract
                worker = await Tesseract.createWorker();
                await worker.loadLanguage('fra');
                await worker.initialize('fra');

            } catch (error) {
                console.error('Camera error:', error);
                updateStatus('error', 'Erreur caméra', 'Vérifier les permissions');
            }
        }

        // Configurer les écouteurs
        function setupEventListeners() {
            btnScan.addEventListener('click', captureAndAnalyze);
            btnUpload.addEventListener('click', () => photoInput.click());
            btnLive.addEventListener('click', toggleLiveScan);
            photoInput.addEventListener('change', handleFileUpload);
        }

        // Capturer et analyser
        async function captureAndAnalyze() {
            if (isScanning) return;

            isScanning = true;
            btnScan.disabled = true;
            updateStatus('scanning', 'Analyse en cours...', 'Traitement OCR...');
            scanCount++;

            try {
                const canvas = document.createElement('canvas');
                canvas.width = videoElement.videoWidth;
                canvas.height = videoElement.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(videoElement, 0, 0);

                // Prétraitement de l'image
                const imageData = canvas.toDataURL('image/jpeg', 0.9);
                
                // Analyse avec Tesseract
                const { data: { text, confidence } } = await worker.recognize(imageData, 'fra', {
                    tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                    tessedit_pagesegmode: '7'
                });

                const normalizedText = normalizePlaque(text);
                
                if (normalizedText.length >= MIN_PLAQUE_LEN) {
                    if (normalizedText === lastStable) {
                        stableCount++;
                    } else {
                        lastStable = normalizedText;
                        stableCount = 1;
                    }

                    if (stableCount >= STABLE_NEEDED) {
                        resultInput.value = normalizedText;
                        updateConfidence(confidence);
                        successCount++;
                        totalConfidence += confidence;
                        updateMetrics();
                        
                        if (confidence >= MIN_CONFIDENCE_SOFT && !navigating) {
                            navigating = true;
                            updateStatus('success', 'Plaque détectée!', 'Redirection...');
                            setTimeout(() => {
                                window.location.href = buildResultUrl(normalizedText);
                            }, 1500);
                        }
                    }
                }
                
            } catch (error) {
                console.error('OCR error:', error);
                updateStatus('error', 'Erreur OCR', 'Réessayer...');
            } finally {
                isScanning = false;
                btnScan.disabled = false;
            }
        }

        // Gérer l'upload de fichier
        async function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            isScanning = true;
            btnUpload.disabled = true;
            updateStatus('scanning', 'Analyse fichier...', 'Traitement OCR...');
            scanCount++;

            // Afficher l'aperçu
            const reader = new FileReader();
            reader.onload = (e) => {
                photoPreview.src = e.target.result;
                photoPreview.style.display = 'block';
                videoElement.style.display = 'none';
            };
            reader.readAsDataURL(file);

            try {
                const { data: { text, confidence } } = await worker.recognize(file, 'fra', {
                    tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                    tessedit_pagesegmode: '7'
                });

                const normalizedText = normalizePlaque(text);
                
                if (normalizedText.length >= MIN_PLAQUE_LEN) {
                    resultInput.value = normalizedText;
                    updateConfidence(confidence);
                    successCount++;
                    totalConfidence += confidence;
                    updateMetrics();
                    
                    if (confidence >= MIN_CONFIDENCE_SOFT && !navigating) {
                        navigating = true;
                        updateStatus('success', 'Fichier analysé!', 'Redirection...');
                        setTimeout(() => {
                            window.location.href = buildResultUrl(normalizedText);
                        }, 1500);
                    }
                }
                
            } catch (error) {
                console.error('File OCR error:', error);
                updateStatus('error', 'Erreur fichier', 'Réessayer...');
            } finally {
                isScanning = false;
                btnUpload.disabled = false;
            }
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
            btnLive.innerHTML = '<i class="bi bi-stop-circle"></i><span>Arrêter</span>';
            btnLive.className = 'btn-modern btn-secondary-modern';
            
            liveScanInterval = setInterval(async () => {
                if (!isScanning && autoScan.checked) {
                    await captureAndAnalyze();
                }
            }, AUTO_INTERVAL_MS);
            
            updateStatus('scanning', 'Scan live actif', 'Capture automatique...');
        }

        // Arrêter le scan live
        function stopLiveScan() {
            isLiveScanning = false;
            btnLive.innerHTML = '<i class="bi bi-broadcast"></i><span>Scan Live</span>';
            btnLive.className = 'btn-modern btn-success-modern';
            
            if (liveScanInterval) {
                clearInterval(liveScanInterval);
            }
            
            updateStatus('success', 'Scanner prêt', 'Mode manuel');
        }

        // Normaliser la plaque
        function normalizePlaque(raw) {
            let text = (raw || '').toUpperCase();
            text = text.replace(/\s+/g, '');
            text = text.replace(/[^A-Z0-9]/g, '');
            
            // Corrections intelligentes
            text = text.replace(/[OI]/g, function (char, index, source) {
                const prev = source[index - 1];
                const next = source[index + 1];
                const nearDigit = /\d/.test(prev || '') || /\d/.test(next || '');
                if (char === 'O' && nearDigit) return '0';
                if (char === 'I' && nearDigit) return '1';
                return char;
            });
            
            return text;
        }

        // Mettre à jour la confiance
        function updateConfidence(confidence) {
            const percentage = Math.round(confidence);
            confidenceFill.style.width = percentage + '%';
            confidenceText.textContent = percentage + '%';
        }

        // Mettre à jour le statut
        function updateStatus(type, text, subtitle) {
            statusDot.className = 'status-dot ' + (type === 'success' ? 'active' : '');
            statusText.textContent = text;
            statusSubtitle.textContent = subtitle;
        }

        // Mettre à jour les métriques
        function updateMetrics() {
            document.getElementById('scan-count').textContent = scanCount;
            document.getElementById('success-rate').textContent = 
                scanCount > 0 ? Math.round((successCount / scanCount) * 100) + '%' : '0%';
            document.getElementById('avg-confidence').textContent = 
                successCount > 0 ? Math.round(totalConfidence / successCount) + '%' : '0%';
        }

        // Construire l'URL de résultat
        function buildResultUrl(plaque) {
            return RESULTAT_TEMPLATE.split('__PLAQUE__').join(encodeURIComponent(plaque));
        }

        // Nettoyage
        window.addEventListener('beforeunload', async () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            if (worker) {
                await worker.terminate();
            }
            if (liveScanInterval) {
                clearInterval(liveScanInterval);
            }
        });
    </script>
</x-app-layout>
