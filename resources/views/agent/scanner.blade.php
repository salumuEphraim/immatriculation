<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-primary">
            <i class="bi bi-camera2 me-2"></i>Scanner OCR
        </h2>
    </x-slot>

    <style>
        .scanner-page {
            margin-top: .25rem;
        }

        .scanner-panel {
            background: rgba(255, 255, 255, .96);
            border: 1px solid var(--rs-border);
            border-radius: 18px;
            box-shadow: var(--rs-shadow-md);
            overflow: hidden;
        }

        .scanner-toolbar {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            padding: 1rem 1.25rem;
            border-bottom: 3px solid var(--rs-primary);
        }

        .scanner-status {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            min-height: 2.25rem;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .16);
            color: #fff;
            font-size: .82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .scanner-status-dot {
            width: .55rem;
            height: .55rem;
            border-radius: 999px;
            background: #94a3b8;
        }

        .scanner-status.good .scanner-status-dot { background: var(--rs-success); }
        .scanner-status.busy .scanner-status-dot { background: #f59e0b; animation: rs-pulse 1.2s infinite; }
        .scanner-status.bad .scanner-status-dot { background: var(--rs-primary); }

        @keyframes rs-pulse {
            0%, 100% { opacity: .45; transform: scale(.9); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .camera-stage {
            position: relative;
            background: #020617;
            min-height: 440px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, .18);
        }

        #camera-preview,
        #camera-placeholder {
            width: 100%;
            height: min(58vh, 560px);
            min-height: 440px;
        }

        #camera-preview {
            object-fit: cover;
            background: #020617;
        }

        #camera-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            padding: 1.5rem;
        }

        .camera-empty-icon {
            width: 4rem;
            height: 4rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(220, 38, 38, .16);
            border: 1px solid rgba(239, 68, 68, .35);
            color: #fff;
            margin-bottom: 1rem;
            font-size: 1.7rem;
        }

        .scan-overlay {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            pointer-events: none;
        }

        .scan-overlay.active {
            display: flex;
        }

        .scan-frame {
            position: relative;
            width: min(760px, 86%);
            aspect-ratio: 4 / 1;
            border: 3px solid rgba(239, 68, 68, .95);
            border-radius: 12px;
            background: rgba(220, 38, 38, .06);
            box-shadow: 0 0 36px rgba(220, 38, 38, .28);
        }

        .scan-frame::before,
        .scan-frame::after {
            content: "";
            position: absolute;
            left: 8%;
            right: 8%;
            height: 2px;
            background: rgba(255, 255, 255, .72);
        }

        .scan-frame::before { top: 34%; }
        .scan-frame::after { bottom: 34%; }

        .scan-line {
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #fff, var(--rs-primary-2), #fff, transparent);
            box-shadow: 0 0 16px rgba(255, 255, 255, .8);
            display: none;
        }

        .scan-line.active {
            display: block;
            animation: scanLine 1.1s ease-in-out infinite;
        }

        @keyframes scanLine {
            0%, 100% { top: 18%; opacity: .45; }
            50% { top: 82%; opacity: 1; }
        }

        .control-card {
            border: 1px solid var(--rs-border);
            border-radius: 16px;
            background: #fff;
            box-shadow: var(--rs-shadow-sm);
            padding: 1rem;
        }

        .btn-scan-main {
            width: 100%;
            min-height: 58px;
            border: 0;
            border-radius: 14px;
            color: #fff;
            font-weight: 800;
            font-size: 1.02rem;
            background: linear-gradient(135deg, var(--rs-primary), var(--rs-primary-2));
            box-shadow: 0 14px 30px rgba(220, 38, 38, .32);
            transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
        }

        .btn-scan-main:hover:not(:disabled) {
            filter: brightness(1.04);
            transform: translateY(-1px);
            box-shadow: 0 18px 36px rgba(220, 38, 38, .38);
        }

        .btn-scan-main:disabled {
            opacity: .65;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-scan-secondary {
            width: 100%;
            min-height: 46px;
            border-radius: 12px;
            font-weight: 700;
        }

        .scanner-metric {
            border: 1px solid var(--rs-border);
            border-radius: 14px;
            padding: .9rem;
            background: #f8fafc;
        }

        .scanner-metric-label {
            color: var(--rs-muted);
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .scanner-metric-value {
            color: var(--rs-text);
            font-weight: 800;
            font-size: 1.2rem;
            line-height: 1.2;
            margin-top: .25rem;
        }

        .scanner-help {
            border-left: 4px solid var(--rs-primary);
            background: rgba(220, 38, 38, .06);
            border-radius: 12px;
            padding: .9rem 1rem;
            color: var(--rs-text);
            font-size: .9rem;
        }

        .scanner-loading {
            display: none;
            border: 1px solid rgba(220, 38, 38, .18);
            border-radius: 14px;
            background: rgba(220, 38, 38, .07);
            padding: .9rem;
        }

        .scanner-loading.active {
            display: block;
        }

        @media (max-width: 991.98px) {
            #camera-preview,
            #camera-placeholder,
            .camera-stage {
                min-height: 320px;
                height: 48vh;
            }
        }
    </style>

    <div class="scanner-page">
        <div class="scanner-panel">
            <div class="scanner-toolbar d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                <div>
                    <div class="h5 fw-bold mb-1">
                        <i class="bi bi-upc-scan me-2 text-primary"></i>Lecture automatique de plaque
                    </div>
                    <div class="small text-white-50">
                        Demarrez la camera, placez la plaque dans le cadre rouge, le resultat s'ouvre automatiquement.
                    </div>
                </div>
                <div id="scan-badge" class="scanner-status">
                    <span class="scanner-status-dot"></span>
                    <span>En attente</span>
                </div>
            </div>

            <div class="p-3 p-lg-4">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <form id="scanner-form" method="POST" action="{{ route('agent.scanner.process') }}" enctype="multipart/form-data">
                            @csrf
                            <input id="file" name="file" type="file" accept="image/*" capture="environment" class="d-none">

                            <div class="camera-stage">
                                <video id="camera-preview" class="d-none" autoplay muted playsinline></video>

                                <div id="camera-placeholder">
                                    <div>
                                        <div class="camera-empty-icon">
                                            <i class="bi bi-camera-video"></i>
                                        </div>
                                        <div class="fw-bold fs-5">Camera inactive</div>
                                        <div class="small text-white-50 mt-1">Cliquez sur Demarrer le scan pour ouvrir la camera du PC.</div>
                                    </div>
                                </div>

                                <div id="scan-overlay" class="scan-overlay">
                                    <div class="scan-frame">
                                        <div id="scan-line" class="scan-line"></div>
                                    </div>
                                </div>

                                <canvas id="capture-canvas" class="d-none"></canvas>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-4">
                        <div class="control-card h-100 d-flex flex-column gap-3">
                            <div>
                                <div class="fw-bold text-dark">Controle du scan</div>
                                <div id="camera-message" class="small text-muted mt-1">
                                    Une seule action suffit: demarrez la camera, puis le scan continue automatiquement.
                                </div>
                            </div>

                            <button id="start-camera" type="button" class="btn-scan-main">
                                <i class="bi bi-camera-video me-2"></i>Demarrer le scan
                            </button>

                            <div class="row g-2">
                                <div class="col-12">
                                    <button id="capture-photo" type="button" disabled class="btn btn-outline-primary btn-scan-secondary">
                                        <i class="bi bi-crosshair me-2"></i>Verifier maintenant
                                    </button>
                                </div>
                                <div class="col-12">
                                    <button id="open-file" type="button" class="btn btn-light border btn-scan-secondary">
                                        <i class="bi bi-image me-2"></i>Importer une image
                                    </button>
                                </div>
                            </div>

                            <div id="spinner" class="scanner-loading">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                                    <div>
                                        <div class="fw-bold text-primary">Analyse en cours</div>
                                        <div class="small text-muted">Recherche d'une plaque lisible.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="scanner-metric">
                                        <div class="scanner-metric-label">Tentatives</div>
                                        <div id="attempt-count" class="scanner-metric-value">0</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="scanner-metric">
                                        <div class="scanner-metric-label">Statut</div>
                                        <div id="last-status" class="scanner-metric-value fs-6">Pret</div>
                                    </div>
                                </div>
                            </div>

                            <label class="d-flex align-items-center justify-content-between gap-3 border rounded-4 p-3">
                                <span>
                                    <span class="d-block fw-bold">Detection continue</span>
                                    <span class="d-block small text-muted">Capture toutes les 3 secondes.</span>
                                </span>
                                <input id="auto-scan" type="checkbox" checked class="form-check-input m-0">
                            </label>

                            <div class="scanner-help mt-auto">
                                <strong>Conseil:</strong> gardez la camera stable, evitez les reflets et placez la plaque au centre du cadre.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('scanner-form');
            const csrfToken = form.querySelector('input[name="_token"]').value;
            const processUrl = @json(route('agent.scanner.process-json'));
            const startCameraBtn = document.getElementById('start-camera');
            const capturePhotoBtn = document.getElementById('capture-photo');
            const openFileBtn = document.getElementById('open-file');
            const fileInput = document.getElementById('file');
            const spinner = document.getElementById('spinner');
            const video = document.getElementById('camera-preview');
            const canvas = document.getElementById('capture-canvas');
            const placeholder = document.getElementById('camera-placeholder');
            const message = document.getElementById('camera-message');
            const badge = document.getElementById('scan-badge');
            const overlay = document.getElementById('scan-overlay');
            const scanLine = document.getElementById('scan-line');
            const autoScan = document.getElementById('auto-scan');
            const attemptCount = document.getElementById('attempt-count');
            const lastStatus = document.getElementById('last-status');

            let stream = null;
            let timer = null;
            let inFlight = false;
            let attempts = 0;
            let locked = false;

            function setStatus(text, tone) {
                lastStatus.textContent = text;
                badge.className = 'scanner-status' + (tone ? ' ' + tone : '');
                badge.innerHTML = '<span class="scanner-status-dot"></span><span>' + text + '</span>';
            }

            function setScanning(active) {
                spinner.classList.toggle('active', active);
                scanLine.classList.toggle('active', active);
            }

            function stopCamera() {
                if (!stream) return;
                stream.getTracks().forEach(function (track) {
                    track.stop();
                });
                stream = null;
            }

            function stopAutoScan() {
                if (timer) {
                    window.clearInterval(timer);
                    timer = null;
                }
            }

            function makeFrameBlob(callback) {
                if (!video.videoWidth || !video.videoHeight) {
                    callback(null);
                    return;
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(callback, 'image/jpeg', 0.9);
            }

            async function sendFrame(blob, manual) {
                if (!blob || inFlight || locked) return;

                inFlight = true;
                attempts += 1;
                attemptCount.textContent = String(attempts);
                setScanning(true);
                setStatus(manual ? 'Verification' : 'Analyse', 'busy');

                const payload = new FormData();
                payload.append('_token', csrfToken);
                payload.append('file', new File([blob], 'plaque-camera.jpg', { type: 'image/jpeg' }));

                try {
                    const response = await fetch(processUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: payload
                    });

                    const data = await response.json().catch(function () {
                        return {};
                    });

                    if (response.ok && data.success && data.redirect_url) {
                        locked = true;
                        stopAutoScan();
                        stopCamera();
                        setStatus(data.plate, 'good');
                        message.textContent = 'Plaque detectee. Ouverture du resultat...';
                        window.location.href = data.redirect_url;
                        return;
                    }

                    setStatus(response.status >= 500 ? 'Erreur OCR' : 'Recherche', response.status >= 500 ? 'bad' : 'busy');
                    message.textContent = data.error || 'Aucune plaque fiable pour le moment. Gardez la plaque dans le cadre.';
                } catch (error) {
                    setStatus('OCR indisponible', 'bad');
                    message.textContent = 'Impossible de contacter le service OCR. Verifiez le microservice Python.';
                } finally {
                    inFlight = false;
                    setScanning(false);
                }
            }

            function scanOnce(manual) {
                makeFrameBlob(function (blob) {
                    sendFrame(blob, manual);
                });
            }

            function startAutoScan() {
                stopAutoScan();
                if (!autoScan.checked) return;
                timer = window.setInterval(function () {
                    if (!autoScan.checked || locked) return;
                    scanOnce(false);
                }, 3000);
                window.setTimeout(function () {
                    if (autoScan.checked && !locked) scanOnce(false);
                }, 1200);
            }

            async function startCamera() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    message.textContent = 'Camera non disponible dans ce navigateur. Utilisez l import image.';
                    setStatus('Camera indisponible', 'bad');
                    return;
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: 'environment' },
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    });

                    video.srcObject = stream;
                    await video.play();
                    video.classList.remove('d-none');
                    placeholder.classList.add('d-none');
                    overlay.classList.add('active');
                    capturePhotoBtn.disabled = false;
                    startCameraBtn.disabled = true;
                    setStatus('Camera active', 'good');
                    message.textContent = 'Placez la plaque dans le cadre rouge. Le scan est automatique.';
                    startAutoScan();
                } catch (error) {
                    setStatus('Camera refusee', 'bad');
                    message.textContent = 'Autorisez la camera dans le navigateur ou utilisez l import image.';
                }
            }

            startCameraBtn.addEventListener('click', startCamera);
            capturePhotoBtn.addEventListener('click', function () {
                scanOnce(true);
            });

            autoScan.addEventListener('change', function () {
                if (autoScan.checked && stream) {
                    setStatus('Auto active', 'good');
                    startAutoScan();
                } else {
                    stopAutoScan();
                    setStatus(stream ? 'Auto pause' : 'En attente', '');
                }
            });

            openFileBtn.addEventListener('click', function () {
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
                if (!fileInput.files || !fileInput.files.length) return;
                stopAutoScan();
                stopCamera();
                setScanning(true);
                form.submit();
            });
        })();
    </script>
</x-app-layout>
