<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">
            Details du vehicule : <span class="text-primary">{{ $plaque->numero_plaque }}</span>
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid px-0">
            
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-200 rounded-lg shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="p-4 bg-white border-start border-4 {{ $plaque->statut == 'valide' ? 'border-success' : 'border-danger' }} shadow-sm rounded mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 fw-bold mb-1">Statut administratif : {{ strtoupper($plaque->statut) }}</h3>
                        <p class="text-muted mb-0">Expire le : {{ \Carbon\Carbon::parse($plaque->date_expiration)->format('d/m/Y') }}</p>
                    </div>
                    <i class="bi {{ $plaque->statut == 'valide' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} fs-1 d-none d-md-block"></i>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="p-4 bg-white shadow-sm rounded border">
                    <h3 class="text-primary fw-bold mb-3 border-bottom pb-2 d-flex align-items-center">
                        <i class="bi bi-car-front-fill me-2"></i> INFORMATIONS VÉHICULE
                    </h3>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><strong>Marque :</strong> <span>{{ $plaque->vehicule->marque }}</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Modele :</strong> <span>{{ $plaque->vehicule->modele }}</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Chassis (VIN) :</strong> <span>{{ $plaque->vehicule->vin }}</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Couleur :</strong> <span>{{ $plaque->vehicule->couleur }}</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Puissance :</strong> <span>{{ $plaque->vehicule->puissance_fiscale }} CV</span></li>
                    </ul>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-4 bg-white shadow-sm rounded border">
                    <h3 class="text-primary fw-bold mb-3 border-bottom pb-2 d-flex align-items-center">
                        <i class="bi bi-person-badge-fill me-2"></i> PROPRIÉTAIRE
                    </h3>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><strong>Nom complet :</strong> <span>{{ $plaque->vehicule->proprietaire->nom }} {{ $plaque->vehicule->proprietaire->prenom }}</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>NIUU :</strong> <span class="text-primary">{{ $plaque->vehicule->proprietaire->niuu }}</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Telephone :</strong> <span>{{ $plaque->vehicule->proprietaire->telephone }}</span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Adresse :</strong> <span class="text-end small">{{ $plaque->vehicule->proprietaire->adresse ?? 'Lubumbashi, Haut-Katanga' }}</span></li>
                    </ul>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white shadow-sm rounded border mb-4">
                <h3 class="text-secondary fw-bold mb-3 border-bottom pb-2 d-flex justify-content-between">
                    <span><i class="bi bi-card-checklist me-2"></i> INFRACTIONS RÉCENTES</span>
                    <span class="small text-muted">(Derniers 12 mois)</span>
                </h3>
                @forelse($plaque->vehicule->infractions as $infraction)
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom px-2">
                        <div>
                            <span class="d-block fw-semibold">{{ $infraction->type_infraction }}</span>
                            <span class="small text-muted">{{ \Carbon\Carbon::parse($infraction->created_at)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="d-block small fw-bold {{ $infraction->statut == 'payee' ? 'text-success' : 'text-danger' }}">
                                {{ strtoupper($infraction->statut) }}
                            </span>
                            <span class="small">{{ number_format($infraction->montant, 0, ',', ' ') }} FC</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <i class="bi bi-shield-check text-success fs-2"></i>
                        <p class="text-muted fst-italic mt-2">Aucune infraction enregistree pour ce vehicule.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2">
                @if(Auth::user()->role === 'agent')
                    <a href="{{ route('agent.infraction.create', ['vehicule_id' => $plaque->vehicule->id]) }}" 
                       class="btn btn-danger">
                        Signaler une infraction
                    </a>
                @endif

                <a href="{{ route('shared.search') }}" 
                   class="btn btn-dark">
                    <i class="bi bi-arrow-left me-2"></i> Nouvelle Recherche
                </a>
            </div>

        </div>
    </div>
</x-app-layout>