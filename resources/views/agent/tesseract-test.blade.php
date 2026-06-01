<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-primary">
            <i class="bi bi-cpu me-2"></i>Test Tesseract OCR
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Carte principale -->
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-upload me-2"></i>
                            Test d'analyse d'image
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Statut Tesseract -->
                        <div class="alert {{ $tesseractAvailable ? 'alert-success' : 'alert-danger' }} mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi {{ $tesseractAvailable ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-2"></i>
                                <div>
                                    <strong>Tesseract OCR:</strong> 
                                    {{ $tesseractAvailable ? 'Disponible' : 'Non disponible' }}
                                    @if($tesseractVersion)
                                        <br><small>Version: {{ $tesseractVersion }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire d'upload -->
                        <form id="ocrTestForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="image" class="form-label fw-semibold">
                                    <i class="bi bi-image me-1"></i>
                                    Sélectionner une image
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*"
                                       required>
                                <div class="form-text">
                                    Formats supportés: JPG, PNG, GIF (Max: 5MB)
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary" id="analyzeBtn">
                                    <i class="bi bi-search me-2"></i>
                                    Analyser l'image
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="demoBtn">
                                    <i class="bi bi-magic me-2"></i>
                                    Test avec démo
                                </button>
                            </div>
                        </form>

                        <!-- Aperçu de l'image -->
                        <div id="imagePreview" class="mt-4" style="display: none;">
                            <h6 class="mb-3">Aperçu de l'image:</h6>
                            <img id="previewImg" class="img-fluid rounded border" style="max-height: 300px;">
                        </div>
                    </div>
                </div>

                <!-- Résultats -->
                <div id="resultsSection" class="card border-0 shadow-lg mt-4" style="display: none;">
                    <div class="card-header bg-gradient-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-data me-2"></i>
                            Résultats de l'analyse
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="resultsContent"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-12 col-lg-4">
                <!-- Informations -->
                <div class="card border-0 shadow-lg mb-4">
                    <div class="card-header bg-gradient-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Informations
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <p class="mb-2">
                                <strong>Configuration testée:</strong><br>
                                • PSM: 7, 6, 8<br>
                                • OEM: 3, 1<br>
                                • Whitelist: A-Z, 0-9
                            </p>
                            <p class="mb-2">
                                <strong>Prétraitement:</strong><br>
                                • Niveaux de gris<br>
                                • Amélioration du contraste<br>
                                • Nettoyage automatique
                            </p>
                            <p class="mb-0">
                                <strong>Corrections:</strong><br>
                                • B → 8<br>
                                • S → 5<br>
                                • Z → 2
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-gradient-warning text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>
                            Performance
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="statsContent">
                            <div class="text-center text-muted">
                                <i class="bi bi-bar-chart fs-1"></i>
                                <p class="small mt-2 mb-0">Effectuez une analyse pour voir les statistiques</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        .bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }
        .bg-gradient-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }
        
        .result-item {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
        }
        
        .result-best {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .confidence-bar {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
        }
        
        .confidence-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .confidence-high { background: #28a745; }
        .confidence-medium { background: #ffc107; }
        .confidence-low { background: #dc3545; }
    </style>

    <script>
        $(document).ready(function() {
            const $form = $('#ocrTestForm');
            const $analyzeBtn = $('#analyzeBtn');
            const $demoBtn = $('#demoBtn');
            const $imageInput = $('#image');
            const $imagePreview = $('#imagePreview');
            const $previewImg = $('#previewImg');
            const $resultsSection = $('#resultsSection');
            const $resultsContent = $('#resultsContent');
            const $statsContent = $('#statsContent');

            // Aperçu de l'image
            $imageInput.on('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $previewImg.attr('src', e.target.result);
                        $imagePreview.show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Soumission du formulaire
            $form.on('submit', function(e) {
                e.preventDefault();
                analyzeImage();
            });

            // Test avec démo
            $demoBtn.on('click', function() {
                testWithDemo();
            });

            function analyzeImage() {
                const formData = new FormData($form[0]);
                
                $analyzeBtn.prop('disabled', true)
                          .html('<i class="bi bi-hourglass-split me-2"></i>Analyse en cours...');

                $.ajax({
                    url: '{{ route("tesseract.test") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        displayResults(response);
                        updateStats(response);
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Erreur lors de l\'analyse';
                        showError(error);
                    },
                    complete: function() {
                        $analyzeBtn.prop('disabled', false)
                                  .html('<i class="bi bi-search me-2"></i>Analyser l\'image');
                    }
                });
            }

            function testWithDemo() {
                $demoBtn.prop('disabled', true)
                         .html('<i class="bi bi-hourglass-split me-2"></i>Test en cours...');

                $.get('{{ route("tesseract.demo") }}', function(response) {
                    displayResults(response);
                    updateStats(response);
                })
                .fail(function(xhr) {
                    const error = xhr.responseJSON?.error || 'Erreur lors du test';
                    showError(error);
                })
                .always(function() {
                    $demoBtn.prop('disabled', false)
                             .html('<i class="bi bi-magic me-2"></i>Test avec démo');
                });
            }

            function displayResults(response) {
                if (!response.success) {
                    showError(response.error);
                    return;
                }

                const result = response.result;
                let html = '';

                // Meilleur résultat
                if (result.best_result) {
                    html += `
                        <div class="result-item result-best">
                            <h6 class="text-success mb-2">
                                <i class="bi bi-trophy-fill me-2"></i>
                                Meilleur résultat
                            </h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <strong>Texte:</strong> 
                                    <code class="fs-5">${result.best_result.text || 'N/A'}</code>
                                </div>
                                <div class="col-md-4">
                                    <strong>Confiance:</strong> 
                                    <span class="badge bg-${getConfidenceColor(result.best_result.confidence)}">
                                        ${result.best_result.confidence}%
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="confidence-bar">
                                    <div class="confidence-fill confidence-${getConfidenceLevel(result.best_result.confidence)}" 
                                         style="width: ${result.best_result.confidence}%"></div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Tous les résultats
                if (result.all_results && result.all_results.length > 1) {
                    html += `
                        <h6 class="mt-4 mb-3">
                            <i class="bi bi-list-ul me-2"></i>
                            Tous les résultats (${result.total_analyzed})
                        </h6>
                        <div class="row">
                    `;

                    result.all_results.forEach((res, index) => {
                        const isBest = index === 0;
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card ${isBest ? 'border-success' : ''}">
                                    <div class="card-body">
                                        <small class="text-muted">Config #${res.config_index}</small>
                                        <div class="mt-1">
                                            <code>${res.text || 'N/A'}</code>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-${getConfidenceColor(res.confidence)}">
                                                ${res.confidence}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                }

                $resultsContent.html(html);
                $resultsSection.show();
                
                // Scroll vers les résultats
                $resultsSection[0].scrollIntoView({ behavior: 'smooth' });
            }

            function updateStats(response) {
                const result = response.result;
                let html = `
                    <div class="small">
                        <div class="mb-2">
                            <strong>Analyses effectuées:</strong> ${result.total_analyzed}
                        </div>
                        <div class="mb-2">
                            <strong>Meilleure confiance:</strong> 
                            <span class="badge bg-${getConfidenceColor(result.best_result?.confidence || 0)}">
                                ${result.best_result?.confidence || 0}%
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Texte détecté:</strong><br>
                            <code>${result.best_result?.text || 'N/A'}</code>
                        </div>
                        <div class="mb-2">
                            <strong>Tesseract:</strong><br>
                            <span class="badge bg-${response.tesseract_info?.available ? 'success' : 'danger'}">
                                ${response.tesseract_info?.available ? 'Disponible' : 'Non disponible'}
                            </span>
                        </div>
                `;

                if (response.tesseract_info?.version) {
                    html += `
                        <div class="mb-0">
                            <strong>Version:</strong><br>
                            <code>${response.tesseract_info.version}</code>
                        </div>
                    `;
                }

                html += '</div>';

                $statsContent.html(html);
            }

            function showError(message) {
                $resultsContent.html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Erreur:</strong> ${message}
                    </div>
                `);
                $resultsSection.show();
            }

            function getConfidenceLevel(confidence) {
                if (confidence >= 70) return 'high';
                if (confidence >= 40) return 'medium';
                return 'low';
            }

            function getConfidenceColor(confidence) {
                const level = getConfidenceLevel(confidence);
                return {
                    high: 'success',
                    medium: 'warning',
                    low: 'danger'
                }[level];
            }
        });
    </script>
</x-app-layout>
