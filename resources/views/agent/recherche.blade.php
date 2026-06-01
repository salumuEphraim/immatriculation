<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-primary">
            <i class="bi bi-camera2 me-2"></i>Scanner intelligent de plaques
        </h2>
    </x-slot>

    @php
        $resultatUrlTemplate = route('shared.resultat', ['plaque' => '__PLAQUE__']);
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <div class="recherche-container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-8">
                <div class="card card-custom glass-card border-0">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="mb-1 fw-bold">Contrôle OCR fluide</h5>
                        <p class="text-muted mb-0 small">Import image, caméra live et correction intelligente des caractères.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="scanner-frame mb-3 scan-pulse" id="scanner-frame">
                            <video id="video" autoplay playsinline muted></video>
                            <img id="photo-preview" alt="Aperçu photo importée" />
                            <div class="roi-guide" aria-hidden="true"></div>
                            <div class="scan-line" aria-hidden="true"></div>
                        </div>
                        <canvas id="canvas"></canvas>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="auto-scan" checked>
                            <label class="form-check-label small" for="auto-scan">Scan automatique intelligent</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-1">Numéro détecté</label>
                            <input type="text" id="resultat-plaque" class="form-control form-control-lg text-center fw-bold font-monospace" readonly>
                            <div id="confidence-hint" class="small text-muted mt-1 font-monospace"></div>
                        </div>

                        <input type="file" id="photo-input" class="visually-hidden" accept="image/*" capture="environment">

                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <button type="button" id="btn-scan" class="btn btn-primary w-100">
                                    <i class="bi bi-camera-fill me-2"></i>Capturer maintenant
                                </button>
                            </div>
                            <div class="col-12 col-md-6">
                                <button type="button" id="btn-upload" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-image me-2"></i>Importer une image
                                </button>
                            </div>
                        </div>
                        <div id="status" class="text-center mt-3 small text-muted font-monospace">Préparation...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .recherche-container {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.85) 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .recherche-container .card-header {
            background: transparent;
            border: none;
        }
        .recherche-container .text-muted {
            color: #94a3b8 !important;
        }
        #video, #photo-preview {
            width: 100%;
            border-radius: 12px;
            background: #000;
            object-fit: cover;
            min-height: 220px;
            max-height: min(55vh, 420px);
        }
        #photo-preview, #canvas {
            display: none;
        }
        .scanner-frame {
            position: relative;
            border: 3px solid #dc2626;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
        }
        .roi-guide {
            position: absolute;
            top: 32%;
            left: 8%;
            width: 84%;
            height: 36%;
            border: 2px dashed rgba(220, 38, 38, 0.9);
            border-radius: 6px;
            pointer-events: none;
            z-index: 10;
        }
        .scan-line {
            position: absolute;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #dc2626, transparent);
            animation: scan 2.2s ease-in-out infinite;
            z-index: 5;
        }
        @keyframes scan {
            0%, 100% { top: 8%; opacity: .5; }
            50% { top: 88%; opacity: 1; }
        }
    </style>

    <script>
        (function () {
            const video = document.getElementById('video');
            const photoPreview = document.getElementById('photo-preview');
            const canvas = document.getElementById('canvas');
            const resultInput = document.getElementById('resultat-plaque');
            const btnScan = document.getElementById('btn-scan');
            const btnUpload = document.getElementById('btn-upload');
            const photoInput = document.getElementById('photo-input');
            const statusEl = document.getElementById('status');
            const autoScanEl = document.getElementById('auto-scan');
            const confHint = document.getElementById('confidence-hint');
            const RESULTAT_TEMPLATE = @json($resultatUrlTemplate);

            let stream = null;
            let worker = null;
            let ocrInitPromise = null;
            let isScanning = false;
            let navigating = false;
            let autoTimer = null;
            let previewUrl = null;
            let scanCooldownUntil = 0;

            const STABLE_NEEDED = 2;
            const MIN_PLAQUE_LEN = 3;
            const AUTO_INTERVAL_MS = 1600;
            const MIN_CONFIDENCE_SOFT = 35;
            const OUT_W = 960;
            const OUT_H = 420;

            let lastStable = '';
            let stableCount = 0;

            function setStatus(text, cls) {
                statusEl.textContent = text;
                statusEl.className = 'text-center mt-3 small font-monospace ' + (cls || 'text-secondary');
            }

            function buildResultUrl(plaque) {
                return RESULTAT_TEMPLATE.split('__PLAQUE__').join(encodeURIComponent(plaque));
            }

            function cleanupPreview() {
                if (previewUrl) {
                    try { URL.revokeObjectURL(previewUrl); } catch (_) {}
                    previewUrl = null;
                }
            }

            function showVideoMode() {
                cleanupPreview();
                photoPreview.removeAttribute('src');
                photoPreview.style.display = 'none';
                video.style.display = '';
            }

            function showPhotoMode(objectUrl) {
                cleanupPreview();
                previewUrl = objectUrl;
                video.style.display = 'none';
                photoPreview.style.display = '';
                photoPreview.src = objectUrl;
            }

            function normalizePlaque(raw) {
                let text = (raw || '').toUpperCase();
                text = text.replace(/\s+/g, '');
                text = text.replace(/[^A-Z0-9]/g, '');
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

            function aiScorePlateCandidate(candidate, confidence) {
                if (!candidate) return 0;
                let score = (confidence || 0) * 0.6;
                if (/^[A-Z0-9]{6,10}$/.test(candidate)) score += 18;
                if (/\d/.test(candidate) && /[A-Z]/.test(candidate)) score += 14;
                if (candidate.length >= 6 && candidate.length <= 9) score += 10;
                if (/(.)\1\1/.test(candidate)) score -= 18;
                return score;
            }

            function aiFixCommonConfusions(candidate) {
                if (!candidate) return candidate;
                let value = candidate;
                const rules = [
                    [/B/g, '8'],
                    [/S/g, '5'],
                    [/Z/g, '2'],
                ];
                for (const [pattern, replacement] of rules) {
                    const test = value.replace(pattern, replacement);
                    if (/\d{2,}/.test(test)) value = test;
                }
                return value;
            }

            function drawSourceToCanvas(source, width, height) {
                const sx = width * 0.08;
                const sy = height * 0.32;
                const sw = width * 0.84;
                const sh = height * 0.36;

                canvas.width = OUT_W;
                canvas.height = OUT_H;
                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(source, sx, sy, sw, sh, 0, 0, OUT_W, OUT_H);
                return ctx;
            }

            function preprocessBinary(ctx, w, h) {
                const imageData = ctx.getImageData(0, 0, w, h);
                const data = imageData.data;
                const total = w * h;
                const gray = new Float32Array(total);
                let min = 255;
                let max = 0;

                for (let i = 0, p = 0; i < data.length; i += 4, p++) {
                    const g = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                    gray[p] = g;
                    if (g < min) min = g;
                    if (g > max) max = g;
                }

                const range = (max - min) || 1;
                for (let i = 0, p = 0; i < data.length; i += 4, p++) {
                    const v = ((gray[p] - min) / range) * 255;
                    const bin = v >= 118 ? 255 : 0;
                    data[i] = bin;
                    data[i + 1] = bin;
                    data[i + 2] = bin;
                }

                ctx.putImageData(imageData, 0, 0);
            }

            function cloneCanvasToTemp(invert) {
                const tmp = document.createElement('canvas');
                tmp.width = canvas.width;
                tmp.height = canvas.height;
                const tctx = tmp.getContext('2d');
                tctx.drawImage(canvas, 0, 0);

                if (invert) {
                    const imageData = tctx.getImageData(0, 0, tmp.width, tmp.height);
                    const data = imageData.data;
                    for (let i = 0; i < data.length; i += 4) {
                        data[i] = 255 - data[i];
                        data[i + 1] = 255 - data[i + 1];
                        data[i + 2] = 255 - data[i + 2];
                    }
                    tctx.putImageData(imageData, 0, 0);
                }

                return tmp;
            }

            async function runOcrVariant(invert) {
                const source = cloneCanvasToTemp(invert);
                const { data } = await worker.recognize(source);
                const text = normalizePlaque(data.text || '');
                const confidence = typeof data.confidence === 'number' ? data.confidence : 0;

                return { text, confidence };
            }

            async function runOcrBest() {
                const primary = await runOcrVariant(false);
                const fixedPrimary = aiFixCommonConfusions(primary.text);
                const primaryScore = aiScorePlateCandidate(fixedPrimary, primary.confidence);

                if (primary.confidence >= 58 && fixedPrimary.length >= 5) {
                    return { text: fixedPrimary, conf: primary.confidence };
                }

                const secondary = await runOcrVariant(true);
                const candidates = [
                    { text: fixedPrimary, conf: primary.confidence, score: primaryScore },
                    {
                        text: aiFixCommonConfusions(secondary.text),
                        conf: secondary.confidence,
                        score: aiScorePlateCandidate(aiFixCommonConfusions(secondary.text), secondary.confidence),
                    },
                ];

                candidates.sort((a, b) => b.score - a.score);
                return candidates[0];
            }

            function registerStableRead(plaque, conf) {
                if (plaque.length < MIN_PLAQUE_LEN) {
                    lastStable = '';
                    stableCount = 0;
                    return false;
                }

                const needStable = plaque.length <= 4 ? STABLE_NEEDED + 1 : STABLE_NEEDED;
                if (plaque === lastStable) {
                    stableCount++;
                } else {
                    lastStable = plaque;
                    stableCount = 1;
                }

                confHint.textContent = conf > 0 ? ('Confiance OCR ~ ' + Math.round(conf) + '% · stabilité ' + stableCount + '/' + needStable) : '';
                return stableCount >= needStable;
            }

            function redirectToPlaque(plaque) {
                navigating = true;
                if (autoTimer) {
                    clearInterval(autoTimer);
                    autoTimer = null;
                }
                setStatus('PLAQUE LUE - REDIRECTION...', 'ok');
                resultInput.value = plaque;
                window.location.href = buildResultUrl(plaque);
            }

            async function captureAndAnalyze(options) {
                const manual = options && options.manual;
                if (navigating || isScanning || Date.now() < scanCooldownUntil) return;
                if (!worker) {
                    setStatus('OCR non prêt.', '');
                    return;
                }
                if (!video.videoWidth || !video.videoHeight) {
                    setStatus('Caméra en chargement...', '');
                    return;
                }

                isScanning = true;
                scanCooldownUntil = Date.now() + 700;
                btnScan.disabled = true;
                setStatus(manual ? 'CAPTURE MANUELLE...' : 'ANALYSE AUTO...', 'scanning');

                try {
                    if (ocrInitPromise) await ocrInitPromise;

                    const ctx = drawSourceToCanvas(video, video.videoWidth, video.videoHeight);
                    preprocessBinary(ctx, canvas.width, canvas.height);

                    const { text: plaqueNettoyee, conf } = await runOcrBest();

                    if (plaqueNettoyee.length >= MIN_PLAQUE_LEN) {
                        resultInput.value = plaqueNettoyee;
                        const longEnough = plaqueNettoyee.length >= 5 && conf >= MIN_CONFIDENCE_SOFT;
                        const stableOk = registerStableRead(plaqueNettoyee, conf);
                        const shouldRedirect = manual || longEnough || (autoScanEl.checked && stableOk);

                        if (shouldRedirect) {
                            redirectToPlaque(plaqueNettoyee);
                            return;
                        }

                        setStatus('Détecté : ' + plaqueNettoyee + ' - maintenez stable pour validation auto', 'scanning');
                    } else {
                        lastStable = '';
                        stableCount = 0;
                        confHint.textContent = '';
                        resultInput.value = '';
                        setStatus('Texte insuffisant - placez la plaque dans le cadre rouge', '');
                    }
                } catch (error) {
                    console.error(error);
                    setStatus('ERREUR TECHNIQUE - réessayez', '');
                    resultInput.value = '';
                } finally {
                    isScanning = false;
                    if (!navigating) btnScan.disabled = !worker;
                }
            }

            async function analyzeUploadedFile(file) {
                if (!file || navigating || isScanning) return;
                if (ocrInitPromise) await ocrInitPromise;
                if (!worker) {
                    setStatus('OCR indisponible - impossible d’analyser la photo.', '');
                    return;
                }

                isScanning = true;
                btnScan.disabled = true;
                btnUpload.disabled = true;
                setStatus('CHARGEMENT PHOTO...', 'scanning');

                try {
                    const img = new Image();
                    img.decoding = 'async';
                    const url = URL.createObjectURL(file);
                    showPhotoMode(url);

                    await new Promise((resolve, reject) => {
                        img.onload = resolve;
                        img.onerror = reject;
                        img.src = url;
                    });

                    const ctx = drawSourceToCanvas(img, img.naturalWidth || img.width, img.naturalHeight || img.height);
                    preprocessBinary(ctx, canvas.width, canvas.height);

                    const { text: plaqueNettoyee, conf } = await runOcrBest();
                    if (plaqueNettoyee.length >= MIN_PLAQUE_LEN) {
                        resultInput.value = plaqueNettoyee;
                        confHint.textContent = conf > 0 ? ('Confiance OCR ~ ' + Math.round(conf) + '%') : '';
                        redirectToPlaque(plaqueNettoyee);
                        return;
                    }

                    lastStable = '';
                    stableCount = 0;
                    confHint.textContent = '';
                    resultInput.value = '';
                    setStatus('Photo illisible - essayez une photo plus proche et nette', '');
                } catch (error) {
                    console.error(error);
                    setStatus('ERREUR PHOTO - réessayez', '');
                } finally {
                    isScanning = false;
                    if (!navigating) {
                        btnScan.disabled = !worker;
                        btnUpload.disabled = !worker;
                        showVideoMode();
                    }
                    photoInput.value = '';
                }
            }

            btnScan.disabled = true;
            setStatus('PRÉPARATION OCR...', 'scanning');

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            }).then(function (mediaStream) {
                stream = mediaStream;
                video.srcObject = mediaStream;
                video.playsInline = true;
                video.muted = true;
                return video.play();
            }).catch(function () {
                setStatus('Accès caméra refusé ou indisponible. Utilisez l’import d’image.', '');
            });

            const initOCR = async function () {
                try {
                    worker = await Tesseract.createWorker('eng', 1, { logger: () => {} });
                    await worker.setParameters({
                        tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                        tessedit_pageseg_mode: '7',
                        preserve_interword_spaces: '0',
                    });
                    setStatus('Prêt - cadrez la plaque', 'ok');
                    btnScan.disabled = false;
                    btnUpload.disabled = false;

                    autoTimer = setInterval(function () {
                        if (!autoScanEl.checked || navigating || document.hidden) return;
                        captureAndAnalyze({ manual: false });
                    }, AUTO_INTERVAL_MS);
                } catch (error) {
                    console.error(error);
                    setStatus('OCR indisponible (Tesseract).', '');
                    btnUpload.disabled = false;
                }
            };

            ocrInitPromise = initOCR();

            btnScan.addEventListener('click', function () {
                captureAndAnalyze({ manual: true });
            });

            btnUpload.addEventListener('click', function () {
                setStatus('Sélectionnez une photo...', 'scanning');
                photoInput.click();
            });

            photoInput.addEventListener('change', function () {
                const file = photoInput.files && photoInput.files[0];
                analyzeUploadedFile(file);
            });

            autoScanEl.addEventListener('change', function () {
                lastStable = '';
                stableCount = 0;
                confHint.textContent = '';
                setStatus(autoScanEl.checked ? 'Scan auto activé' : 'Scan auto désactivé - utilisez le bouton', 'ok');
            });

            window.addEventListener('beforeunload', function () {
                if (autoTimer) clearInterval(autoTimer);
                try {
                    if (stream) stream.getTracks().forEach(track => track.stop());
                } catch (_) {}
                try {
                    if (worker) worker.terminate();
                } catch (_) {}
                cleanupPreview();
            });
        })();
    </script>
</x-app-layout>
