<x-app-layout>
    <x-slot name="header">
        <div class="agent-infractions-pro-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-1 fw-bold text-white">
                    <i class="bi bi-list-check me-2"></i>Mes signalements
                </h2>
                <p class="small mb-0 text-secondary">Historique des infractions enregistrées sur le terrain</p>
            </div>
            <a href="{{ route('agent.recherche') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-camera me-1"></i> Nouveau scan
            </a>
        </div>
    </x-slot>

    <style>
        .agent-infractions-pro {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.85) 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        /* Stats Cards */
        .agent-infractions-pro-stat {
            background: rgba(30, 41, 59, 0.60);
            border: 1px solid rgba(220, 38, 38, 0.25);
            border-radius: 14px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .agent-infractions-pro-stat:hover {
            background: rgba(220, 38, 38, 0.12);
            border-color: rgba(220, 38, 38, 0.50);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.20);
        }

        .agent-infractions-pro-stat span {
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .agent-infractions-pro-stat strong {
            font-size: 1.75rem;
            color: #ffffff;
            font-weight: 700;
        }

        /* Empty State */
        .agent-infractions-pro-empty {
            background: rgba(30, 41, 59, 0.60);
            border: 2px dashed rgba(220, 38, 38, 0.30);
            border-radius: 16px;
            color: #cbd5e1;
            padding: 3rem 2rem;
        }

        .agent-infractions-pro-empty i {
            color: rgba(220, 38, 38, 0.60);
        }

        .agent-infractions-pro-empty h3 {
            color: #ffffff;
        }

        /* Table Wrapper */
        .agent-infractions-pro-table-wrap {
            background: rgba(30, 41, 59, 0.60);
            border: 1px solid rgba(220, 38, 38, 0.15);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        /* Table Styling */
        .agent-infractions-pro-table {
            background: transparent;
            color: #e2e8f0;
        }

        .agent-infractions-pro-table thead {
            background: rgba(15, 23, 42, 0.80);
            border-bottom: 2px solid rgba(220, 38, 38, 0.30);
        }

        .agent-infractions-pro-table thead th {
            color: #cbd5e1 !important;
            background: rgba(15, 23, 42, 0.80) !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.70rem;
            letter-spacing: 0.10em;
            padding: 1.25rem;
            border: none !important;
        }

        .agent-infractions-pro-table tbody tr {
            background: rgba(30, 41, 59, 0.50);
            border-bottom: 1px solid rgba(220, 38, 38, 0.12);
            transition: all 0.2s ease;
        }

        .agent-infractions-pro-table tbody tr:hover {
            background: rgba(220, 38, 38, 0.10);
            border-bottom-color: rgba(220, 38, 38, 0.30);
        }

        .agent-infractions-pro-table tbody td {
            padding: 1rem 1.25rem;
            color: #e2e8f0;
            vertical-align: middle;
            border: none !important;
        }

        /* Type de l'infraction */
        .agent-infractions-pro-type {
            background: rgba(220, 38, 38, 0.15);
            color: #fca5a5;
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 700;
            border-left: 3px solid #dc2626;
            display: inline-block;
        }

        /* Statut Pills */
        .agent-infractions-pro-pill {
            padding: 0.5rem 0.875rem;
            border-radius: 999px;
            font-size: 0.70rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: inline-block;
            border: 1.5px solid transparent;
        }

        .agent-infractions-pro-pill.is-ok {
            background: rgba(16, 185, 129, 0.18);
            color: #86efac;
            border-color: rgba(16, 185, 129, 0.40);
        }

        .agent-infractions-pro-pill.is-wait {
            background: rgba(245, 158, 11, 0.18);
            color: #fcd34d;
            border-color: rgba(245, 158, 11, 0.40);
        }

        .agent-infractions-pro-pill.is-paid {
            background: rgba(59, 130, 246, 0.18);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, 0.40);
        }

        /* Header */
        .agent-infractions-pro-header {
            color: #ffffff;
        }

        .agent-infractions-pro-header h2 {
            color: #ffffff;
        }

        /* Responsive */
        .table-responsive {
            background: transparent;
        }
    </style>

    @php
        $countValidees = $infractions->where('statut', 'validee')->count();
        $countAttente = $infractions->where('statut', 'en_attente')->count();
        $countTotal = $infractions->count();
        $totalMontant = (float) $infractions->sum('montant');
    @endphp

    <div class="agent-infractions-pro py-2">
        @if($infractions->isEmpty())
            <div class="agent-infractions-pro-empty text-center py-5 px-4">
                <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                <h3 class="h5 fw-bold mb-2">Aucun signalement pour le moment</h3>
                <p class="small mb-4">Commencez un contrôle pour créer votre premier PV.</p>
                <a href="{{ route('agent.recherche') }}" class="btn btn-primary">
                    <i class="bi bi-camera2 me-1"></i> Scanner maintenant
                </a>
            </div>
        @else
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="agent-infractions-pro-stat">
                        <span>Validées</span>
                        <strong>{{ $countValidees }}</strong>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="agent-infractions-pro-stat">
                        <span>En attente</span>
                        <strong>{{ $countAttente }}</strong>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="agent-infractions-pro-stat">
                        <span>Total PV</span>
                        <strong>{{ $countTotal }}</strong>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="agent-infractions-pro-stat">
                        <span>Montant total</span>
                        <strong>{{ number_format($totalMontant, 0, ',', ' ') }} CDF</strong>
                    </div>
                </div>
            </div>

            <div class="agent-infractions-pro-table-wrap">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0 agent-infractions-pro-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Véhicule</th>
                                <th>Infraction</th>
                                <th>Statut admin</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($infractions as $infraction)
                                @php $status = $infraction->statut ?? 'en_attente'; @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-white">
                                            {{ $infraction->date_infraction?->format('d/m/Y') ?? $infraction->created_at->format('d/m/Y') }}
                                        </div>
                                        <div class="small text-slate-400">
                                            {{ $infraction->date_infraction?->format('H:i') ?? $infraction->created_at->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold font-monospace text-white">{{ $infraction->vehicule->plaque ?? 'N/A' }}</div>
                                        <div class="small text-slate-400">{{ $infraction->vehicule->marque ?? '' }} {{ $infraction->vehicule->modele ?? '' }}</div>
                                    </td>
                                    <td>
                                        <span class="agent-infractions-pro-type">{{ $infraction->type }}</span>
                                    </td>
                                    <td>
                                        @if($status === 'validee')
                                            <span class="agent-infractions-pro-pill is-ok">Validée</span>
                                        @elseif($status === 'en_attente')
                                            <span class="agent-infractions-pro-pill is-wait">En attente</span>
                                        @elseif($status === 'payee')
                                            <span class="agent-infractions-pro-pill is-paid">Payée</span>
                                        @else
                                            <span class="agent-infractions-pro-pill">{{ ucfirst($status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                            <a href="{{ route('agent.infractions.recu', $infraction->id) }}" class="btn btn-sm btn-outline-light">
                                                <i class="bi bi-eye me-1"></i> Reçu
                                            </a>
                                            <a href="{{ route('agent.infractions.pdf', $infraction->id) }}" class="btn btn-sm btn-success">
                                                <i class="bi bi-download me-1"></i> PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
