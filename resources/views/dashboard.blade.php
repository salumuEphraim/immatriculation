<x-app-layout>
    <x-slot name="header">
        <div class="text-center">
            <h1 class="h2 fw-bold mb-4">
                <i class="bi bi-speedometer2 text-primary me-3"></i>
                Tableau de Bord
            </h1>
            <p class="lead text-muted mb-0">
                Bienvenue, <strong class="text-primary">{{ Auth::user()->name }}</strong>!
                <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1 ms-3">
                    {{ ucfirst(Auth::user()->role) }}
                </span>
            </p>
        </div>
    </x-slot>

    <div class="row g-4 mb-5">
        <div class="col-lg-4 col-md-6">
            <div class="card-custom h-100 glass-card">
                <div class="card-body d-flex align-items-center p-5">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 p-4 rounded-3">
                        <i class="bi bi-car-front-fill text-primary fs-1"></i>
                    </div>
                    <div class="ms-4">
                        <h6 class="text-muted small text-uppercase fw-bold mb-2">Véhicules enregistrés</h6>
                        <h3 class="fw-bold text-primary mb-0">{{ $vehiculesCount ?? '1,245' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card-custom h-100 glass-card">
                <div class="card-body d-flex align-items-center p-5">
                    <div class="flex-shrink-0 bg-danger bg-opacity-10 p-4 rounded-3">
                        <i class="bi bi-exclamation-octagon-fill text-danger fs-1"></i>
                    </div>
                    <div class="ms-4">
                        <h6 class="text-muted small text-uppercase fw-bold mb-2">Infractions en attente</h6>
                        <h3 class="fw-bold text-danger mb-0">{{ $infractionsCount ?? '28' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card-custom h-100 glass-card">
                <div class="card-body d-flex align-items-center p-5">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 p-4 rounded-3">
                        <i class="bi bi-people-fill text-success fs-1"></i>
                    </div>
                    <div class="ms-4">
                        <h6 class="text-muted small text-uppercase fw-bold mb-2">Agents actifs</h6>
                        <h3 class="fw-bold text-success mb-0">{{ $agentsCount ?? '12' }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card-custom glass-card h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="list-group list-group-flush rounded-bottom">
                    <a href="{{ route('agent.recherche') }}" class="list-group-item list-group-item-action px-4 py-4 border-end-0 border-start-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-15 p-3 rounded-3 me-4">
                                <i class="bi bi-camera2 fs-3 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Contrôle de plaque</h6>
                                <small class="text-muted d-block">Scanner OCR temps réel</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.infractions.index') }}" class="list-group-item list-group-item-action px-4 py-4 border-end-0 border-start-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-15 p-3 rounded-3 me-4">
                                <i class="bi bi-clipboard-check fs-3 text-danger"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Valider infractions</h6>
                                <small class="text-muted d-block">Revue signalements</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action px-4 py-4 border-end-0 border-start-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-15 p-3 rounded-3 me-4">
                                <i class="bi bi-person-gear fs-3 text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Gérer utilisateurs</h6>
                                <small class="text-muted d-block">Rôles & permissions</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card-custom glass-card h-100">
                <div class="card-header bg-transparent border-0 px-5 py-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Derniers Signalements</h5>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-pill px-4">Tout voir</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small fw-bold text-uppercase">
                                <th class="ps-5">Immatriculation</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th class="text-center">Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-5">
                                    <code class="badge bg-secondary-subtle text-dark px-3 py-2 fw-bold small">1234AB01</code>
                                </td>
                                <td>Excès de vitesse</td>
                                <td class="text-muted small">01/03/2026</td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning px-3 py-2 fw-semibold rounded-pill">En attente</span>
                                </td>
                                <td><i class="bi bi-eye text-muted"></i></td>
                            </tr>
                            <tr>
                                <td class="ps-5">
                                    <code class="badge bg-secondary-subtle text-dark px-3 py-2 fw-bold small">5678CD05</code>
                                </td>
                                <td>Défaut assurance</td>
                                <td class="text-muted small">01/03/2026</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 fw-semibold rounded-pill">Validé</span>
                                </td>
                                <td><i class="bi bi-check-circle text-success"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

