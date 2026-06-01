<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-white">
            <i class="bi bi-graph-up-arrow me-2"></i>Rapports et Statistiques
        </h2>
    </x-slot>

    <style>
        .reports-dashboard {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .report-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .report-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .revenue-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .revenue-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(139, 92, 246, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .revenue-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .revenue-amount {
            font-size: 3rem;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .revenue-label {
            color: #94a3b8;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: relative;
            z-index: 1;
        }

        .stats-table {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        .stats-table thead {
            background: rgba(139, 92, 246, 0.1);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }

        .stats-table th {
            color: #e2e8f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            padding: 1rem;
        }

        .stats-table td {
            color: #cbd5e1;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stats-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .agent-performance {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .agent-performance:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(4px);
        }

        .agent-rank {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
        }

        .agent-rank.gold {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
        }

        .agent-rank.silver {
            background: linear-gradient(135deg, #e5e7eb 0%, #9ca3af 100%);
            color: white;
        }

        .agent-rank.bronze {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
        }

        .agent-rank.other {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .metric-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
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
    </style>

    <div class="reports-dashboard page-fade-in">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="text-white mb-3 fw-bold">
                <i class="bi bi-graph-up-arrow me-3"></i>
                Rapport Global RoadShield
            </h1>
            <p class="text-muted">
                Statistiques complètes du système d'immatriculation de Lubumbashi
            </p>
        </div>

        <!-- Revenue Card -->
        <div class="revenue-card mb-5">
            <div class="revenue-icon">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="revenue-amount">
                {{ number_format($recettesTotales, 0, ',', ' ') }} CDF
            </div>
            <div class="revenue-label">
                Recettes Totales Générées
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="row g-4">
            <!-- Répartition par Nature -->
            <div class="col-lg-7">
                <div class="report-card">
                    <div class="p-4 border-bottom border-white border-opacity-10">
                        <h5 class="text-white mb-0 fw-bold">
                            <i class="bi bi-pie-chart me-2"></i>
                            Répartition par Nature d'Infractions
                        </h5>
                    </div>
                    <div class="p-4">
                        <div class="stats-table">
                            <table class="table table-dark table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Type d'Infraction</th>
                                        <th>Nombre</th>
                                        <th>Recettes</th>
                                        <th>% Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalInfractions = $statsType->sum('total'); @endphp
                                    @foreach($statsType as $stat)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                                                    {{ $stat->type }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-20 text-info">
                                                    {{ $stat->total }}
                                                </span>
                                            </td>
                                            <td class="text-success fw-semibold">
                                                {{ number_format($stat->recettes, 0, ',', ' ') }} CDF
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-gradient" role="progressbar" 
                                                         style="width: {{ $totalInfractions > 0 ? ($stat->total / $totalInfractions * 100) : 0 }}%; background: linear-gradient(90deg, #8b5cf6, #7c3aed);">
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    {{ $totalInfractions > 0 ? round($stat->total / $totalInfractions * 100, 1) : 0 }}%
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Agents -->
            <div class="col-lg-5">
                <div class="report-card">
                    <div class="p-4 border-bottom border-white border-opacity-10">
                        <h5 class="text-white mb-0 fw-bold">
                            <i class="bi bi-trophy me-2"></i>
                            Top Agents de Contrôle
                        </h5>
                    </div>
                    <div class="p-4">
                        @foreach($performanceAgents as $index => $perf)
                            <div class="agent-performance">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="agent-rank {{ $index == 0 ? 'gold' : ($index == 1 ? 'silver' : ($index == 2 ? 'bronze' : 'other')) }}">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-white">
                                                <i class="bi bi-person-badge me-2"></i>
                                                {{ $perf->agent->name ?? 'Agent inconnu' }}
                                            </div>
                                            <div class="text-muted small">
                                                Agent de terrain
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="metric-badge">
                                            {{ $perf->total }} PV
                                        </div>
                                        <div class="text-muted small mt-1">
                                            dressés
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <div class="report-card text-center">
                    <div class="p-4">
                        <i class="bi bi-car-front fs-1 text-primary mb-3"></i>
                        <h6 class="text-white mb-2">Véhicules Contrôlés</h6>
                        <div class="h4 fw-bold text-primary">
                            {{ $statsType->sum('total') }}
                        </div>
                        <div class="text-muted small">
                            Total des contrôles
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="report-card text-center">
                    <div class="p-4">
                        <i class="bi bi-people fs-1 text-success mb-3"></i>
                        <h6 class="text-white mb-2">Agents Actifs</h6>
                        <div class="h4 fw-bold text-success">
                            {{ $performanceAgents->count() }}
                        </div>
                        <div class="text-muted small">
                            Sur le terrain
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="report-card text-center">
                    <div class="p-4">
                        <i class="bi bi-calculator fs-1 text-warning mb-3"></i>
                        <h6 class="text-white mb-2">Moyenne par PV</h6>
                        <div class="h4 fw-bold text-warning">
                            {{ $statsType->sum('total') > 0 ? number_format($recettesTotales / $statsType->sum('total'), 0, ',', ' ') : 0 }} CDF
                        </div>
                        <div class="text-muted small">
                            Par infraction
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>