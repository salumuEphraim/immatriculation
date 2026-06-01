<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-white">
            <i class="bi bi-speedometer2 me-2"></i>Tableau de Bord
        </h2>
    </x-slot>

    <style>
        .dashboard-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .vehicle-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }
        
        .vehicle-card.en_regle {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
        }
        
        .vehicle-card.en_defaut {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        }
        
        .vehicle-card.attention {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
        }
        
        .vehicle-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .plaque-badge {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: #495057;
            color: white;
            border: 2px solid #343a40;
        }
        
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.success {
            background: #28a745;
            color: white;
        }
        
        .status-badge.danger {
            background: #dc3545;
            color: white;
        }
        
        .status-badge.warning {
            background: #ffc107;
            color: #212529;
        }
        
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.3rem 0;
            font-size: 0.8rem;
        }
        
        .document-status {
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .document-status.missing {
            background: #dc3545;
            color: white;
        }
        
        .document-status.expired {
            background: #dc3545;
            color: white;
        }
        
        .document-status.expiring {
            background: #ffc107;
            color: #212529;
        }
        
        .document-status.valid {
            background: #28a745;
            color: white;
        }
        
        .alert-card {
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-card.danger {
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
            border: 1px solid #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .alert-card.success {
            background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
            border: 1px solid #d1e7dd;
            border-left: 4px solid #28a745;
        }
        
        .alert-card.warning {
            background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
            border: 1px solid #fff3cd;
            border-left: 4px solid #ffc107;
        }
        
        .metric-number {
            font-size: 2rem;
            font-weight: bold;
            line-height: 1;
        }
        
        .metric-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>

    <div class="container-fluid py-4">
        <!-- En-tête avec statistiques -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="metric-number text-success">{{ $vehiclesData->count() }}</div>
                    <div class="metric-label">Véhicules</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="metric-number text-success">{{ $vehiclesData->where('status', 'en_regle')->count() }}</div>
                    <div class="metric-label">En règle</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="metric-number text-danger">{{ $vehiclesData->where('status', 'en_defaut')->count() }}</div>
                    <div class="metric-label">En défaut</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="metric-number text-warning">{{ $vehiclesData->where('status', 'attention')->count() }}</div>
                    <div class="metric-label">Attention</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Section véhicules -->
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-car-front me-2 text-primary"></i>
                                Mes Véhicules
                            </h5>
                            <span class="badge bg-primary rounded-pill">{{ $vehiclesData->count() }} véhicule(s)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse($vehiclesData as $vehicleData)
                            @php
                                $vehicle = $vehicleData['vehicle'];
                                $status = $vehicleData['status'];
                                $statusText = $vehicleData['status_text'];
                                $statusColor = $vehicleData['status_color'];
                                $statusIcon = $vehicleData['status_icon'];
                                $missingDocs = $vehicleData['missing_documents'];
                                $summary = $vehicleData['summary'];
                            @endphp
                            
                            <div class="vehicle-card {{ $status }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <div class="plaque-badge">{{ $vehicle->plaque_immatriculation }}</div>
                                            <span class="status-badge {{ $statusColor }}">
                                                <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $statusText }}
                                            </span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <h6 class="fw-bold text-primary mb-1">{{ $vehicle->marque }} {{ $vehicle->modele }}</h6>
                                            <div class="text-muted small">Couleur: {{ $vehicle->couleur }} | VIN: {{ substr($vehicle->vin, 0, 8) }}...</div>
                                        </div>

                                        <!-- Documents status -->
                                        @if(!empty($missingDocs) || $summary['expired'] > 0 || $summary['expiring_soon'] > 0)
                                            <div class="mt-3">
                                                <div class="small text-muted mb-2 fw-bold">Documents requis:</div>
                                                @php
                                                    $requiredDocuments = [
                                                        'assurance' => 'Assurance',
                                                        'vignette' => 'Vignette', 
                                                        'controle_technique' => 'Contrôle technique',
                                                        'carte_rose' => 'Carte rose'
                                                    ];
                                                @endphp
                                                @foreach($requiredDocuments as $type => $label)
                                                    @php
                                                        $isMissing = in_array($type, $missingDocs);
                                                        $isExpired = false;
                                                        $isExpiring = false;
                                                        
                                                        if (isset($summary['details'])) {
                                                            $docDetail = collect($summary['details'])->firstWhere('type', $type);
                                                            if ($docDetail) {
                                                                $isExpired = $docDetail['status'] === 'expired';
                                                                $isExpiring = $docDetail['status'] === 'expiring_soon';
                                                            }
                                                        }
                                                        
                                                        $isValid = !$isMissing && !$isExpired && !$isExpiring;
                                                    @endphp
                                                    <div class="document-item">
                                                        <span>{{ $label }}</span>
                                                        @if($isMissing)
                                                            <span class="document-status missing">Manquant</span>
                                                        @elseif($isExpired)
                                                            <span class="document-status expired">Expiré</span>
                                                        @elseif($isExpiring)
                                                            <span class="document-status expiring">Bientôt</span>
                                                        @else
                                                            <span class="document-status valid">Valide</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="text-end">
                                        <a href="{{ route('shared.resultat', $vehicle->plaque_immatriculation) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>Détails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bi bi-car-front fs-1 text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted mb-2">Aucun véhicule enregistré</h5>
                                <p class="text-muted small">Contactez la DGI de Lubumbashi si vous pensez qu'il y a une erreur.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Section alertes et actions -->
            <div class="col-lg-4">
                <!-- Alertes amendes -->
                @if($infractionsImpayees->count() > 0)
                    <div class="alert-card danger">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-2"></i>
                            <h6 class="mb-0 fw-bold text-danger">Alertes Critiques</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            <strong>{{ $infractionsImpayees->count() }}</strong> amende(s) impayée(s)
                        </p>
                        @foreach($infractionsImpayees->take(2) as $infraction)
                            <div class="mb-2 p-2 bg-white rounded">
                                <div class="fw-bold small">{{ $infraction->type }}</div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>{{ $infraction->vehicule->plaque_immatriculation ?? 'Véhicule' }}</span>
                                    <span class="text-danger fw-semibold">{{ number_format($infraction->montant, 0, ',', ' ') }} CDF</span>
                                </div>
                            </div>
                        @endforeach
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('proprietaire.infractions') }}" class="btn btn-danger btn-sm fw-bold">
                                <i class="bi bi-phone me-1"></i>Payer maintenant
                            </a>
                            <a href="{{ route('proprietaire.infractions') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-list-ul me-1"></i>Voir tout
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Alertes documents -->
                @php
                    $vehiclesWithIssues = $vehiclesData->filter(function($v) {
                        return $v['status'] !== 'en_regle';
                    });
                @endphp
                @if($vehiclesWithIssues->count() > 0)
                    <div class="alert-card warning">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-file-earmark-text text-warning fs-4 me-2"></i>
                            <h6 class="mb-0 fw-bold text-warning">Documents à régulariser</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            <strong>{{ $vehiclesWithIssues->count() }}</strong> véhicule(s) nécessitent votre attention
                        </p>
                        @foreach($vehiclesWithIssues->take(2) as $vehicleData)
                            <div class="mb-2 p-2 bg-white rounded">
                                <div class="fw-bold small">{{ $vehicleData['vehicle']->plaque_immatriculation }}</div>
                                <div class="text-muted small">
                                    @if($vehicleData['status'] === 'en_defaut')
                                        Documents manquants ou expirés
                                    @else
                                        Documents expirant bientôt
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <a href="{{ route('proprietaire.vehicules') }}" class="btn btn-warning btn-sm w-100 mt-3">
                            <i class="bi bi-arrow-right me-1"></i>Voir mes véhicules
                        </a>
                    </div>
                @endif

                <!-- Situation normale -->
                @if($infractionsImpayees->count() === 0 && $vehiclesWithIssues->count() === 0)
                    <div class="alert-card success">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-shield-check text-success fs-4 me-2"></i>
                            <h6 class="mb-0 fw-bold text-success">Situation Excellente</h6>
                        </div>
                        <p class="small text-muted mb-3">
                            Tous vos véhicules sont en règle et vous n'avez aucune amende impayée.
                        </p>
                        <div class="d-grid gap-2">
                            <a href="{{ route('proprietaire.infractions') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-clock-history me-1"></i>Historique
                            </a>
                            <a href="{{ route('proprietaire.vehicules') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-car-front me-1"></i>Mes véhicules
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Actions rapides -->
                <div class="dashboard-card">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-lightning me-2 text-primary"></i>Actions Rapides
                        </h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('proprietaire.vehicules') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-car-front me-1"></i>Gérer mes véhicules
                            </a>
                            <a href="{{ route('proprietaire.infractions') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-receipt me-1"></i>Mes amendes
                            </a>
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-person me-1"></i>Mon profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
