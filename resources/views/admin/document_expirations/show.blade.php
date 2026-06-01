<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 mb-0 fw-bold text-white">
                <i class="bi bi-truck me-2 text-info"></i>
                Détails du véhicule
            </h2>
            <a href="{{ route('admin.expirations.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Retour
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <!-- Informations véhicule -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Informations du véhicule</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted w-50">Plaque</td>
                                <td class="fw-bold">{{ $vehicule->plaque_immatriculation }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Marque/Modèle</td>
                                <td class="fw-bold">{{ $vehicule->marque }} {{ $vehicule->modele }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">VIN</td>
                                <td class="fw-bold">{{ $vehicule->vin }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted w-50">Propriétaire</td>
                                <td class="fw-bold">{{ $vehicule->proprietaire->nom ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td class="fw-bold">{{ $vehicule->proprietaire->user->email ?? 'Pas d\'email' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Couleur</td>
                                <td class="fw-bold">{{ $vehicule->couleur }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé des documents -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="fs-2 fw-bold text-secondary">{{ $summary['total_documents'] }}</div>
                        <div class="text-muted small">Total documents</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-danger">
                    <div class="card-body text-center">
                        <div class="fs-2 fw-bold text-danger">{{ $summary['expired'] }}</div>
                        <div class="text-muted small">Expirés</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-warning">
                    <div class="card-body text-center">
                        <div class="fs-2 fw-bold text-warning">{{ $summary['expiring_soon'] }}</div>
                        <div class="text-muted small">Expirant bientôt</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 border-success">
                    <div class="card-body text-center">
                        <div class="fs-2 fw-bold text-success">{{ $summary['valid'] }}</div>
                        <div class="text-muted small">Valides</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détail des documents -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Documents du véhicule
                </h5>
            </div>
            <div class="card-body">
                @if($summary['total_documents'] === 0)
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        <p>Ce véhicule n'a aucun document enregistré.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Type de document</th>
                                    <th>Date d'émission</th>
                                    <th>Date d'expiration</th>
                                    <th>Jours restants</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary['details'] as $detail)
                                    <tr>
                                        <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $detail['type'])) }}</td>
                                        <td>-</td>
                                        <td>{{ $detail['expiration_date'] ?? 'N/A' }}</td>
                                        <td>
                                            @if($detail['days_remaining'] < 0)
                                                <span class="text-danger">Expiré depuis {{ abs($detail['days_remaining']) }} jours</span>
                                            @elseif($detail['days_remaining'] === 0)
                                                <span class="text-warning">Expire aujourd'hui</span>
                                            @else
                                                <span class="text-success">{{ $detail['days_remaining'] }} jours</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($detail['status'] === 'expired')
                                                <span class="badge bg-danger">Expiré</span>
                                            @elseif($detail['status'] === 'expiring_soon')
                                                <span class="badge bg-warning">Expirant bientôt</span>
                                            @else
                                                <span class="badge bg-success">Valide</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
