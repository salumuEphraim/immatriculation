<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-white">
            <i class="bi bi-speedometer2 me-2"></i>Tableau de Bord Administrateur
        </h2>
    </x-slot>

    <style>
        .admin-dashboard {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .dashboard-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .metric-icon.vehicles {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .metric-icon.agents {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .metric-icon.controls {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .metric-icon.infractions {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .metric-icon.revenue {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .metric-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ffffff;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            color: #67e8f9;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .metric-subtitle {
            color: #22d3ee;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .activity-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .activity-icon.scan {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .activity-icon.infraction {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .activity-icon.user {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .tool-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            display: block;
        }

        .tool-card:hover {
            background: linear-gradient(135deg, #334155 0%, #475569 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-indicator.online {
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        .welcome-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .page-fade-in {
            animation: pageFadeIn 0.5s ease-out;
        }

        @keyframes pageFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Classes de couleur pour la lisibilité */
        .text-cyan-300 {
            color: #67e8f9 !important;
        }

        .text-cyan-400 {
            color: #22d3ee !important;
        }
    </style>

    <div class="admin-dashboard page-fade-in">
        <!-- Header Welcome -->
        <div class="welcome-header">
            <h1 class="text-white mb-3 fw-bold">
                <i class="bi bi-shield-check me-3"></i>
                Bienvenue dans RoadShield Admin
            </h1>
            <p class="text-cyan-300 mb-0">
                Panneau de contrôle du système d'immatriculation de Lubumbashi
            </p>
        </div>

        <!-- Statistics Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="metric-icon vehicles">
                        <i class="bi bi-car-front"></i>
                    </div>
                    <div class="metric-number">{{ number_format($totalVehicules) }}</div>
                    <div class="metric-label">Véhicules</div>
                    <div class="metric-subtitle">Enregistrés en RDC</div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="metric-icon agents">
                        <i class="bi bi-badge"></i>
                    </div>
                    <div class="metric-number">{{ $totalAgents }}</div>
                    <div class="metric-label">Agents</div>
                    <div class="metric-subtitle">Actifs sur le terrain</div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="metric-icon controls">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div class="metric-number">{{ $totalControles ?? 0 }}</div>
                    <div class="metric-label">Contrôles</div>
                    <div class="metric-subtitle">{{ $totalControlesToday ?? 0 }} aujourd'hui, {{ $totalControlesThisWeek ?? 0 }} cette semaine</div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="metric-icon infractions">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <div class="metric-number">{{ $totalInfractions }}</div>
                    <div class="metric-label">Infractions</div>
                    <div class="metric-subtitle">{{ $infractionsToday ?? 0 }} aujourd'hui, validées</div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="stat-card">
                    <div class="metric-icon revenue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="metric-number">
                        {{ $totalRecettes >= 1000000 ? number_format($totalRecettes / 1000000, 1) . 'M' : number_format($totalRecettes, 0, ',', ' ') }}
                        <small class="text-muted">CDF</small>
                    </div>
                    <div class="metric-label">Recettes Réelles</div>
                    <div class="metric-subtitle">{{ ($recettesToday ?? 0) >= 1000000 ? number_format(($recettesToday ?? 0) / 1000000, 1) . 'M' : number_format($recettesToday ?? 0, 0, ',', ' ') }} CDF aujourd'hui, {{ ($recettesThisMonth ?? 0) >= 1000000 ? number_format(($recettesThisMonth ?? 0) / 1000000, 1) . 'M' : number_format($recettesThisMonth ?? 0, 0, ',', ' ') }} CDF ce mois (payées)</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Activities -->
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="p-4 border-bottom border-white border-opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-white mb-0 fw-bold">
                                <i class="bi bi-activity me-2"></i>
                                Activités Récentes
                            </h5>
                            <span class="badge bg-success bg-opacity-20 text-success px-3 py-1">
                                <i class="bi bi-circle-fill me-2"></i>Temps Réel
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        @forelse($recentActivities as $activity)
                            <div class="activity-item">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="activity-icon {{ $activity['type'] }}">
                                        @if($activity['type'] == 'scan')
                                            <i class="bi bi-camera-fill"></i>
                                        @elseif($activity['type'] == 'infraction')
                                            <i class="bi bi-shield-exclamation"></i>
                                        @else
                                            <i class="bi bi-person-plus"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        @if($activity['type'] == 'scan')
                                            <div class="fw-semibold text-white">Contrôle routier</div>
                                            <div class="text-cyan-300 small">Plaque <strong>{{ $activity['plaque'] }}</strong> à {{ $activity['lieu'] }} par {{ $activity['agent'] }}</div>
                                        @elseif($activity['type'] == 'infraction')
                                            <div class="fw-semibold text-white">Infraction enregistrée</div>
                                            <div class="text-cyan-300 small">{{ $activity['description'] }} - Plaque {{ $activity['plaque'] }} par {{ $activity['agent'] }}</div>
                                            @if(isset($activity['montant']))
                                                <div class="text-warning small">💰 {{ number_format($activity['montant'], 0, ',', '.') }} CDF</div>
                                            @endif
                                        @else
                                            <div class="fw-semibold text-white">Nouvel agent</div>
                                            <div class="text-cyan-300 small">{{ $activity['description'] }}</div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <div class="text-cyan-300 small">{{ $activity['created_at']->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-cyan-300">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                <p class="mb-0">Aucune activité récente</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="p-3 text-center border-top border-white border-opacity-10">
                        <a href="#" class="text-primary text-decoration-none small">
                            <i class="bi bi-arrow-right me-1"></i>Voir tout l'historique
                        </a>
                    </div>
                </div>
            </div>

            <!-- Management Tools & System Status -->
            <div class="col-lg-4">
                <!-- Management Tools -->
                <div class="dashboard-card mb-4">
                    <div class="p-4">
                        <h5 class="text-white mb-4 fw-bold">
                            <i class="bi bi-tools me-2"></i>
                            Outils de Gestion
                        </h5>
                        <div class="d-grid gap-3">
                            <a href="{{ route('admin.users.index') }}" class="tool-card">
                                <i class="bi bi-people-fill me-2"></i>
                                <div class="fw-semibold">Gérer les Utilisateurs</div>
                                <div class="small text-cyan-300">Administration des comptes</div>
                            </a>
                            <a href="{{ route('admin.infractions.index') }}" class="tool-card">
                                <i class="bi bi-check2-circle me-2"></i>
                                <div class="fw-semibold">Valider Infractions</div>
                                <div class="small text-cyan-300">Approbation des PV</div>
                            </a>
                            <a href="{{ route('admin.expirations.index') }}" class="tool-card">
                                <i class="bi bi-clock-history me-2"></i>
                                <div class="fw-semibold">Expirations</div>
                                <div class="small text-cyan-300">Suivi des documents</div>
                            </a>
                            <a href="#" class="tool-card">
                                <i class="bi bi-file-earmark-pdf me-2"></i>
                                <div class="fw-semibold">Exporter Rapport</div>
                                <div class="small text-cyan-300">Rapport DGI complet</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="dashboard-card">
                    <div class="p-4">
                        <h6 class="text-white small text-uppercase fw-bold mb-4">
                            <i class="bi bi-cpu me-2"></i>
                            Statut du Système
                        </h6>
                        <div class="space-y-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="status-indicator online me-2"></span>
                                    <span class="text-white small">Base de données</span>
                                </div>
                                <span class="text-success small fw-semibold">En ligne</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="status-indicator online me-2"></span>
                                    <span class="text-white small">Service OCR</span>
                                </div>
                                <span class="text-success small fw-semibold">Opérationnel</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="status-indicator online me-2"></span>
                                    <span class="text-white small">Notifications</span>
                                </div>
                                <span class="text-success small fw-semibold">Actives</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="status-indicator online me-2"></span>
                                    <span class="text-white small">API Externe</span>
                                </div>
                                <span class="text-success small fw-semibold">Connectée</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>