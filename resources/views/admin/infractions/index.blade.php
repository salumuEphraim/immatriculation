@php
    $pointsGps = $infractions->filter(function ($i) {
        return $i->latitude !== null && $i->longitude !== null
            && (float) $i->latitude != 0 && (float) $i->longitude != 0;
    })->map(function ($i) {
        return [
            'lat' => (float) $i->latitude,
            'lng' => (float) $i->longitude,
            'plaque' => $i->vehicule->plaque_immatriculation ?? 'N/A',
            'agent' => $i->agent->name ?? 'Agent',
            'type' => $i->type,
            'date' => $i->date_infraction?->format('d/m/Y H:i') ?? $i->created_at->format('d/m/Y H:i'),
        ];
    })->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-white">
            <i class="bi bi-clipboard-check me-2"></i>Validation des Infractions
        </h2>
    </x-slot>

    <style>
        .infractions-dashboard {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .infractions-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .infractions-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
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

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .stat-icon.waiting {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-icon.validated {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ffffff;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #67e8f9;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .stat-subtitle {
            color: #22d3ee;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .infraction-row {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .infraction-row:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(4px);
        }

        .plaque-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            display: inline-block;
        }

        .infraction-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .infraction-badge.en_attente {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .infraction-badge.validee {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .infraction-badge.rejetee {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .infraction-badge.payee {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .amount-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #10b981;
        }

        .agent-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
            color: white;
            flex-shrink: 0;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .gps-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .gps-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .status-select {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .status-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(139, 92, 246, 0.5);
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
        }

        .update-btn {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .update-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .action-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            display: block;
        }

        .action-card:hover {
            background: linear-gradient(135deg, #334155 0%, #475569 100%);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .action-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        .action-icon.report {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .action-icon.agents {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
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

        /* Style pour la carte */
        .map-container {
            border-radius: 0 0 16px 16px;
            overflow: hidden;
        }

        .map-header {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>

    <div class="infractions-dashboard page-fade-in">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="text-white mb-3 fw-bold">
                <i class="bi bi-clipboard-check me-3"></i>
                Validation des Infractions
            </h1>
            <p class="text-cyan-300 mb-0">
                Validez les procès-verbaux des agents en un clic
            </p>
        </div>

        <!-- Map Section -->
        <div class="infractions-card mb-4">
            <div class="map-header p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white mb-1 fw-bold">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            Positions des Agents
                        </h5>
                        <p class="text-cyan-300 small mb-0">
                            Carte automatique des signalements avec géolocalisation
                        </p>
                    </div>
                    <span class="badge bg-info bg-opacity-20 text-info px-3 py-1">
                        {{ $pointsGps->count() }} point(s) GPS
                    </span>
                </div>
            </div>
            <div class="map-container">
                <div id="map-admin-infractions" style="height: 400px;"></div>
                @if($pointsGps->isEmpty())
                    <div class="text-center py-4 text-cyan-300">
                        <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-50"></i>
                        <p class="mb-0">Aucune position enregistrée pour l'instant</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon waiting">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-number">{{ $infractions->where('statut', 'en_attente')->count() }}</div>
                    <div class="stat-label">En attente</div>
                    <div class="stat-subtitle">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Mise à jour temps réel
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon validated">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-number">{{ $infractions->where('statut', 'validee')->count() }}</div>
                    <div class="stat-label">Validées</div>
                    <div class="stat-subtitle">Prêtes au paiement</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <div class="stat-number">{{ $infractions->count() }}</div>
                    <div class="stat-label">Total</div>
                    <div class="stat-subtitle">Ce mois</div>
                </div>
            </div>
        </div>

        <!-- Infractions List -->
        <div class="infractions-card">
            <div class="p-4 border-bottom border-white border-opacity-10">
                <h5 class="text-white mb-0 fw-bold">
                    <i class="bi bi-list-ul me-2"></i>
                    Liste des Infractions
                </h5>
            </div>
            <div class="p-4">
                @forelse($infractions as $infraction)
                    <div class="infraction-row">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-4 flex-grow-1">
                                <div>
                                    <div class="plaque-badge mb-2">
                                        {{ $infraction->vehicule->plaque_immatriculation ?? 'N/A' }}
                                    </div>
                                    <div class="text-cyan-300 small">
                                        <i class="bi bi-calendar me-1"></i>
                                        {{ $infraction->date_infraction?->format('d/m/Y H:i') ?? $infraction->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <div class="infraction-badge en_attente mb-2">
                                        {{ $infraction->type }}
                                    </div>
                                </div>

                                <div class="text-center">
                                    <div class="amount-display">
                                        {{ number_format($infraction->montant, 0, ' ', ' ') }}
                                        <span class="text-cyan-400 fs-6">CDF</span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <div class="agent-avatar">
                                        {{ substr($infraction->agent->name ?? 'AI', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-white">{{ $infraction->agent->name ?? 'Agent inconnu' }}</div>
                                        <div class="text-cyan-300 small">ID: {{ $infraction->agent_id }}</div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    @if($infraction->latitude !== null && $infraction->longitude !== null)
                                        <a href="https://www.openstreetmap.org/?mlat={{ $infraction->latitude }}&mlon={{ $infraction->longitude }}#map=16/{{ $infraction->latitude }}/{{ $infraction->longitude }}"
                                           target="_blank" rel="noopener"
                                           class="gps-link">
                                            <i class="bi bi-pin-map-fill me-1"></i>
                                            Carte
                                        </a>
                                        <div class="text-cyan-300 small mt-1">
                                            {{ number_format((float) $infraction->latitude, 5) }}, {{ number_format((float) $infraction->longitude, 5) }}
                                        </div>
                                    @else
                                        <span class="text-cyan-300 small">—</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3">
                                <form action="{{ route('admin.infractions.updateStatut', $infraction->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="statut" class="status-select">
                                        <option value="en_attente" {{ $infraction->statut === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                        <option value="validee" {{ $infraction->statut === 'validee' ? 'selected' : '' }}>Validée</option>
                                        <option value="rejetee" {{ $infraction->statut === 'rejetee' ? 'selected' : '' }}>Rejetée</option>
                                        <option value="payee" {{ $infraction->statut === 'payee' ? 'selected' : '' }}>Payée</option>
                                    </select>
                                    <button type="submit" class="update-btn">
                                        <i class="bi bi-save me-1"></i>
                                        MAJ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-cyan-300">
                        <i class="bi bi-check-all fs-1 d-block mb-3 opacity-50"></i>
                        <h4 class="mb-2">Aucune infraction en attente</h4>
                        <p class="mb-0">Tous les PV sont à jour. Bravo !</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mt-4">
            <div class="col-md-6">
                <a href="{{ route('admin.rapports.infractions') }}" class="action-card">
                    <div class="action-icon report">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <h5 class="text-white mb-2">Rapport DGI</h5>
                    <p class="text-cyan-300 mb-0">Exporter statistiques complètes</p>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('admin.users.index') }}" class="action-card">
                    <div class="action-icon agents">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5 class="text-white mb-2">Gestion Agents</h5>
                    <p class="text-cyan-300 mb-0">Suivi performance agents</p>
                </a>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function () {
            var points = @json($pointsGps);
            var el = document.getElementById('map-admin-infractions');
            if (!el || typeof L === 'undefined') return;

            var lubumbashi = [-11.6642, 27.4794];
            var map = L.map('map-admin-infractions').setView(lubumbashi, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            var bounds = [];
            points.forEach(function (p) {
                var m = L.marker([p.lat, p.lng]).addTo(map);
                m.bindPopup(
                    '<strong style="color: white;">' + (p.plaque || '') + '</strong><br>' +
                    '<span style="color: #67e8f9;">' + (p.type || '') + '</span><br>' +
                    '<span style="color: #22d3ee;">Agent : ' + (p.agent || '') + '</span><br>' +
                    '<span style="color: #67e8f9;">' + (p.date || '') + '</span>'
                );
                bounds.push([p.lat, p.lng]);
            });

            if (bounds.length === 1) {
                map.setView(bounds[0], 15);
            } else if (bounds.length > 1) {
                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
            }
        })();
    </script>
</x-app-layout>
