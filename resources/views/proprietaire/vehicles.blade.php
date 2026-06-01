<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 page-mes-vehicules-header">
            <div>
                <h2 class="h5 mb-0 fw-bold rs-ov-title">
                    {{ __('🚗 Mon Parc Automobile') }}
                </h2>
                <form action="{{ route('proprietaire.vehicules.testNotification') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        Tester l'alerte d'expiration
                    </button>
                </form>
            </div>
            <div class="small rs-ov-muted">
                {{ $vehiclesData->count() }} véhicule(s) enregistré(s)
            </div>
        </div>
    </x-slot>

    <style>
        .vehicle-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .vehicle-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .vehicle-card.critical {
            border-left: 4px solid #dc3545;
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        }
        
        .vehicle-card.warning {
            border-left: 4px solid #ffc107;
            background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
        }
        
        .vehicle-card.good {
            border-left: 4px solid #28a745;
            background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
        }
        
        .vehicle-card.no-documents {
            border-left: 4px solid #6c757d;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.critical {
            background: #dc3545;
            color: white;
        }
        
        .status-badge.warning {
            background: #ffc107;
            color: #212529;
        }
        
        .status-badge.good {
            background: #28a745;
            color: white;
        }
        
        .status-badge.no-documents {
            background: #6c757d;
            color: white;
        }
        
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .document-item:last-child {
            border-bottom: none;
        }
        
        .document-status {
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
        }
        
        .document-status.expired {
            background: #dc3545;
            color: white;
        }
        
        .document-status.expiring-soon {
            background: #ffc107;
            color: #212529;
        }
        
        .document-status.valid {
            background: #28a745;
            color: white;
        }
        
        .days-remaining {
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .days-remaining.critical {
            color: #dc3545;
        }
        
        .days-remaining.warning {
            color: #ff8f00;
        }
        
        .days-remaining.good {
            color: #28a745;
        }
        
        .alert-banner {
            background: linear-gradient(135deg, #fff3cd 0%, #ffffff 100%);
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.85rem;
        }
        
        .vehicle-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .plaque-display {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
            color: #495057;
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
    </style>

    <div class="page-mes-vehicules py-4 py-md-5">
        <div class="container-fluid px-3 px-lg-4" style="max-width: 1400px;">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($vehiclesData->isEmpty())
                <div class="text-center p-5 rounded-4 bg-light">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-car-front fs-1 text-muted"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2">Aucun véhicule trouvé</h3>
                    <p class="text-muted mb-0 small mx-auto" style="max-width: 420px;">
                        Si vous possédez un véhicule, assurez-vous qu'il est correctement lié à votre NIUU auprès de la DGI.
                    </p>
                </div>
            @else
                <!-- Statistiques globales -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-success fs-2 fw-bold">{{ $vehiclesData->where('overall_status', 'good')->count() }}</div>
                                <div class="text-muted small">Véhicules en règle</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-warning fs-2 fw-bold">{{ $vehiclesData->where('overall_status', 'warning')->count() }}</div>
                                <div class="text-muted small">Documents expirant bientôt</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-danger fs-2 fw-bold">{{ $vehiclesData->where('overall_status', 'critical')->count() }}</div>
                                <div class="text-muted small">Documents expirés</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-primary fs-2 fw-bold">{{ $vehiclesData->sum('urgent_count') }}</div>
                                <div class="text-muted small">Actions requises</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- État des documents en temps réel -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">Documents en temps réel</h5>
                                <p class="text-muted small mb-0">Suivi des expirations avec le nombre de jours restants.</p>
                            </div>
                            <div class="text-end">
                                <span class="text-muted small" id="owner-last-update">Dernière mise à jour: --</span>
                                <div class="mt-2">
                                    <button id="owner-refresh-toggle" class="btn btn-sm btn-outline-success">Auto-rafraîchissement</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Véhicule</th>
                                        <th>Document</th>
                                        <th>Date expir.</th>
                                        <th>Jours restants</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody id="owner-documents-tbody">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Chargement...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Chargement des statuts en temps réel...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Liste des véhicules -->
                <div class="row g-4">
                    @foreach($vehiclesData as $vehicleData)
                        @php
                            $vehicle = $vehicleData['vehicle'];
                            $summary = $vehicleData['summary'];
                            $documents = $vehicleData['documents_details'];
                            $overallStatus = $vehicleData['overall_status'];
                            $urgentCount = $vehicleData['urgent_count'];
                            $impayees = $vehicle->amendes_impayees_count ?? 0;
                        @endphp
                        
                        <div class="col-12 col-lg-6">
                            <div class="vehicle-card {{ $overallStatus }} h-100">
                                <!-- Header du véhicule -->
                                <div class="vehicle-header">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <p class="text-muted small mb-1">Plaque d'immatriculation</p>
                                            <div class="plaque-display">{{ $vehicle->plaque_immatriculation }}</div>
                                        </div>
                                        <span class="status-badge {{ $overallStatus }}">
                                            {{ match($overallStatus) {
                                                'critical' => 'URGENT',
                                                'warning' => 'ATTENTION',
                                                'good' => 'EN RÈGLE',
                                                'no-documents' => 'SANS DOCS',
                                                default => 'INCONNU'
                                            } }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Corps de la carte -->
                                <div class="card-body">
                                    <!-- Informations véhicule -->
                                    <div class="mb-3">
                                        <h6 class="fw-bold text-primary mb-2">{{ $vehicle->marque }} {{ $vehicle->modele }}</h6>
                                        <div class="row g-2 text-sm">
                                            <div class="col-6">
                                                <span class="text-muted">Couleur:</span>
                                                <span class="fw-semibold ms-1">{{ $vehicle->couleur }}</span>
                                            </div>
                                            <div class="col-6">
                                                <span class="text-muted">VIN:</span>
                                                <span class="fw-semibold font-monospace ms-1">{{ substr($vehicle->vin, 0, 8) }}...</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Alertes amendes -->
                                    @if($impayees > 0)
                                        <div class="alert-banner mb-3">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            <strong>{{ $impayees }}</strong> amende(s) à payer
                                        </div>
                                    @endif

                                    <!-- Statut des documents -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-bold">Documents ({{ $summary['total_documents'] }}/4)</h6>
                                            @if($urgentCount > 0)
                                                <span class="badge bg-danger rounded-pill">{{ $urgentCount }} urgent(s)</span>
                                            @endif
                                        </div>

                                        @if(empty($documents))
                                            <div class="text-center py-3 bg-light rounded">
                                                <i class="bi bi-file-earmark-x fs-3 text-muted d-block mb-2"></i>
                                                <small class="text-muted">Aucun document enregistré</small>
                                            </div>
                                        @else
                                            @foreach($documents as $document)
                                                <div class="document-item">
                                                    <div>
                                                        <span class="fw-semibold">{{ $document['type_formatted'] }}</span>
                                                        @if($document['expiration_date'])
                                                            <small class="text-muted ms-2">({{ $document['expiration_date'] }})</small>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="document-status {{ $document['status'] }}">
                                                            {{ match($document['status']) {
                                                                'expired' => 'Expiré',
                                                                'expiring_soon' => 'Bientôt',
                                                                'valid' => 'Valide',
                                                                default => 'Inconnu'
                                                            } }}
                                                        </span>
                                                        <div class="days-remaining {{ match($document['status']) {
                                                            'expired' => 'critical',
                                                            'expiring_soon' => 'warning',
                                                            'valid' => 'good',
                                                            default => ''
                                                        } }}">
                                                            {{ $document['formatted_days'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-grid gap-2 mt-auto">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <a href="{{ route('proprietaire.infractions') }}" class="btn btn-outline-secondary btn-sm w-100">
                                                    <i class="bi bi-card-list me-1"></i> Infractions
                                                </a>
                                            </div>
                                            <div class="col-6">
                                                <a href="{{ route('shared.resultat', $vehicle->plaque_immatriculation) }}" class="btn btn-primary btn-sm w-100">
                                                    <i class="bi bi-eye me-1"></i> Fiche
                                                </a>
                                            </div>
                                        </div>
                                        @if($impayees > 0)
                                            <a href="{{ route('proprietaire.infractions') }}" class="btn btn-warning btn-sm w-100 fw-semibold">
                                                <i class="bi bi-phone me-1"></i> Payer mes amendes
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Footer -->
            <div class="mt-5 p-4 rounded-4 bg-light">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center bg-white" style="width: 50px; height: 50px;">
                        <i class="bi bi-question-circle fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Un véhicule manque à l'appel ?</h6>
                        <p class="text-muted small mb-0">
                            Veuillez contacter le bureau de la DGI à Lubumbashi ou vérifier votre NIUU.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        let ownerAutoRefreshInterval = null;
        let ownerIsAutoRefresh = false;

        function loadOwnerRealtimeDocuments() {
            fetch('{{ route('proprietaire.vehicules.realtime') }}')
                .then(response => response.json())
                .then(data => {
                    renderOwnerDocumentsTable(data.vehicles);
                    updateOwnerLastUpdate(data.timestamp);
                })
                .catch(error => {
                    console.error('Erreur de chargement propriétaire:', error);
                    showOwnerRealtimeError('Impossible de charger les statuts en temps réel.');
                });
        }

        function renderOwnerDocumentsTable(vehicles) {
            const tbody = document.getElementById('owner-documents-tbody');

            const rows = vehicles.flatMap(vehicle =>
                vehicle.documents.map(document => {
                    const statusLabel = document.status === 'expired' ? 'Expiré' :
                        (document.status === 'expiring_soon' ? 'Bientôt' : 'Valide');

                    const statusClass = document.status === 'expired' ? 'text-danger' :
                        (document.status === 'expiring_soon' ? 'text-warning' : 'text-success');

                    return `
                        <tr>
                            <td>
                                <div class="fw-semibold">${vehicle.marque} ${vehicle.modele}</div>
                                <div class="text-muted small">${vehicle.plaque}</div>
                            </td>
                            <td>${document.type_formatted}</td>
                            <td>${document.expiration_date || 'N/A'}</td>
                            <td class="fw-bold ${statusClass}">${document.formatted_days}</td>
                            <td><span class="badge ${document.status === 'expired' ? 'bg-danger' : (document.status === 'expiring_soon' ? 'bg-warning text-dark' : 'bg-success')}">${statusLabel}</span></td>
                        </tr>
                    `;
                })
            );

            if (rows.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <p>Aucun document trouvé pour le moment.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = rows.join('');
        }

        function updateOwnerLastUpdate(timestamp) {
            const date = new Date(timestamp);
            document.getElementById('owner-last-update').textContent = `Dernière mise à jour: ${date.toLocaleString('fr-FR')}`;
        }

        function showOwnerRealtimeError(message) {
            const tbody = document.getElementById('owner-documents-tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                        <p>${message}</p>
                    </td>
                </tr>
            `;
        }

        function toggleOwnerAutoRefresh() {
            const button = document.getElementById('owner-refresh-toggle');

            if (ownerIsAutoRefresh) {
                clearInterval(ownerAutoRefreshInterval);
                ownerIsAutoRefresh = false;
                button.textContent = 'Auto-rafraîchissement';
                button.className = 'btn btn-sm btn-outline-success';
            } else {
                ownerAutoRefreshInterval = setInterval(loadOwnerRealtimeDocuments, 30000);
                ownerIsAutoRefresh = true;
                button.textContent = 'Arrêter';
                button.className = 'btn btn-sm btn-outline-danger';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadOwnerRealtimeDocuments();
            document.getElementById('owner-refresh-toggle').addEventListener('click', toggleOwnerAutoRefresh);
        });
    </script>
</x-app-layout>
