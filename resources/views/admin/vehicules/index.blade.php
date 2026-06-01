<x-app-layout>
    <x-slot name="header">
        <div class="admin-modern-header">
            <div>
                <span class="admin-modern-eyebrow">Registre automobile</span>
                <h2 class="h3 mb-1 fw-bold text-white">
                    <i class="bi bi-car-front-fill me-2"></i>Vehicules
                </h2>
                <p class="mb-0 text-white-50">Ajout, recherche et suivi documentaire du parc automobile.</p>
            </div>
            <button class="admin-modern-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                <i class="bi bi-plus-circle"></i>
                Ajouter un vehicule
            </button>
        </div>
    </x-slot>

    @php
        $totalVehicules = $vehicules->total();
        $visibleEnRegle = $vehicules->getCollection()->filter(fn ($vehicule) => $vehicule->isEnRegle())->count();
        $visibleDefaut = $vehicules->count() - $visibleEnRegle;
    @endphp

    <div class="admin-modern-page">
        <section class="admin-modern-stats">
            <div class="admin-modern-stat">
                <span>Total registre</span>
                <strong>{{ $totalVehicules }}</strong>
                <i class="bi bi-car-front"></i>
            </div>
            <div class="admin-modern-stat is-proprietaire">
                <span>En regle visibles</span>
                <strong>{{ $visibleEnRegle }}</strong>
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="admin-modern-stat is-admin">
                <span>En defaut visibles</span>
                <strong>{{ $visibleDefaut }}</strong>
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="admin-modern-stat is-agent">
                <span>Proprietaires</span>
                <strong>{{ $proprietaires->count() }}</strong>
                <i class="bi bi-person-lines-fill"></i>
            </div>
        </section>

        @if (session('success'))
            <div class="admin-modern-alert is-success">
                <i class="bi bi-check-circle-fill"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="admin-modern-alert is-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>Verifiez les champs du formulaire vehicule.
            </div>
        @endif

        <section class="admin-modern-toolbar">
            <form action="{{ route('admin.vehicules.index') }}" method="GET" class="admin-modern-search">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher une plaque, un nom ou un proprietaire..." autocomplete="off">
                <button type="submit">Rechercher</button>
                @if(request('q') || request('status'))
                    <a href="{{ route('admin.vehicules.index') }}">Effacer</a>
                @endif
            </form>

            <div class="admin-modern-tabs">
                <a href="{{ route('admin.vehicules.index', ['status' => 'all', 'q' => request('q')]) }}" class="{{ (!request('status') || request('status') === 'all') ? 'is-active' : '' }}">Tous</a>
                <a href="{{ route('admin.vehicules.index', ['status' => 'regle', 'q' => request('q')]) }}" class="{{ request('status') === 'regle' ? 'is-active' : '' }}">En regle</a>
                <a href="{{ route('admin.vehicules.index', ['status' => 'defaut', 'q' => request('q')]) }}" class="{{ request('status') === 'defaut' ? 'is-active' : '' }}">En defaut</a>
            </div>
        </section>

        <section class="admin-modern-panel">
            <div class="admin-modern-panel-head">
                <div>
                    <h3>Liste des vehicules</h3>
                    <p>{{ $vehicules->count() }} vehicule(s) affiche(s) sur cette page</p>
                </div>
            </div>

            <div class="admin-vehicle-list">
                @forelse($vehicules as $vehicule)
                    <article class="admin-vehicle-item">
                        <div class="admin-vehicle-plate">
                            <span>{{ $vehicule->plaque_immatriculation }}</span>
                            <small>{{ $vehicule->marque }} {{ $vehicule->modele }}</small>
                        </div>

                        <div class="admin-vehicle-owner">
                            <h4>{{ $vehicule->proprietaire->nom ?? 'N/A' }} {{ $vehicule->proprietaire->prenom ?? '' }}</h4>
                            <p>
                                <i class="bi bi-telephone"></i>
                                {{ $vehicule->proprietaire->telephone ?? 'Telephone non renseigne' }}
                            </p>
                        </div>

                        <div class="admin-vehicle-docs">
                            @foreach($documentTypes as $type => $label)
                                <span class="{{ $vehicule->hasDocumentType($type) ? 'is-valid' : 'is-invalid' }}" title="{{ $label }}">
                                    {{ match($type) {
                                        'assurance' => 'ASS',
                                        'vignette' => 'VIG',
                                        'controle_technique' => 'CT',
                                        'carte_rose' => 'CR',
                                        default => strtoupper(substr($label, 0, 3)),
                                    } }}
                                </span>
                            @endforeach
                        </div>

                        <div class="admin-vehicle-status">
                            <span class="admin-status-pill {{ $vehicule->isEnRegle() ? 'is-ok' : 'is-danger' }}">
                                <i class="bi {{ $vehicule->isEnRegle() ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                {{ $vehicule->isEnRegle() ? 'En regle' : 'En defaut' }}
                            </span>
                            @if($vehicule->contraventions->count() > 0)
                                <small>{{ $vehicule->contraventions->count() }} infraction(s)</small>
                            @endif
                        </div>

                        <div class="admin-vehicle-actions">
                            <a href="{{ route('shared.resultat', ['plaque' => $vehicule->plaque_immatriculation]) }}" class="admin-modern-icon-btn" title="Details">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="{{ route('admin.vehicules.edit', $vehicule) }}" class="admin-modern-icon-btn is-warning" title="Modifier">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="admin-modern-empty">
                        <i class="bi bi-inbox"></i>
                        <p>Aucun vehicule trouve.</p>
                    </div>
                @endforelse
            </div>

            @if($vehicules->hasPages())
                <div class="admin-modern-pagination">
                    {{ $vehicules->links() }}
                </div>
            @endif
        </section>
    </div>

    <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content admin-modern-modal">
                <form action="{{ route('admin.vehicules.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">
                                <i class="bi bi-car-front-fill me-2"></i>Ajouter un vehicule
                            </h5>
                            <p class="mb-0">Renseignez le proprietaire, l'identification et les documents.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>

                    <div class="modal-body">
                        <div class="admin-modern-form-grid">
                            <div class="admin-modern-field is-wide">
                                <label for="proprietaire_id">Proprietaire</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-person"></i>
                                    <select name="proprietaire_id" id="proprietaire_id" required>
                                        <option value="" disabled @selected(!old('proprietaire_id'))>Choisir un proprietaire...</option>
                                        @foreach($proprietaires as $p)
                                            <option value="{{ $p->id }}" @selected(old('proprietaire_id') == $p->id)>
                                                {{ $p->nom }} {{ $p->prenom }} ({{ $p->telephone }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="plaque_immatriculation">Plaque</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-card-text"></i>
                                    <input type="text" name="plaque_immatriculation" id="plaque_immatriculation" value="{{ old('plaque_immatriculation') }}" required placeholder="Ex: 1234AB05">
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="vin">VIN</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-upc-scan"></i>
                                    <input type="text" name="vin" id="vin" value="{{ old('vin') }}" required placeholder="Numero de chassis">
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="marque">Marque</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-car-front"></i>
                                    <input type="text" name="marque" id="marque" value="{{ old('marque') }}" required placeholder="Toyota">
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="modele">Modele</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-gear"></i>
                                    <input type="text" name="modele" id="modele" value="{{ old('modele') }}" required placeholder="Land Cruiser">
                                </div>
                            </div>

                            <div class="admin-modern-field">
                                <label for="couleur">Couleur</label>
                                <div class="admin-modern-input">
                                    <i class="bi bi-palette"></i>
                                    <input type="text" name="couleur" id="couleur" value="{{ old('couleur') }}" required placeholder="Blanc">
                                </div>
                            </div>

                            <div class="admin-modern-doc-box is-wide">
                                <div>
                                    <h4>Documents de bord</h4>
                                    <p>Les documents locaux calculent le statut. Les documents externes completent la verification au scan.</p>
                                </div>

                                <div class="admin-modern-doc-grid">
                                    @foreach($requiredDocumentTypes as $value => $label)
                                        <label class="admin-modern-check">
                                            <input class="js-document-checkbox" type="checkbox" name="documents[]" value="{{ $value }}" @checked(in_array($value, old('documents', []), true))>
                                            <span>
                                                <i class="bi bi-file-earmark-check"></i>
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <h4>Verification externe</h4>
                                    <p>Pieces consultees par les services externes lors du controle.</p>
                                </div>

                                <div class="admin-modern-doc-grid">
                                    @foreach($externalDocumentTypes as $value => $label)
                                        <label class="admin-modern-check">
                                            <input class="js-document-checkbox js-external-document-checkbox" type="checkbox" name="documents[]" value="{{ $value }}" @checked(in_array($value, old('documents', []), true))>
                                            <span>
                                                <i class="bi bi-cloud-check"></i>
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="admin-modern-field js-assurance-duration" style="display: none;">
                                    <label for="assurance_duration_months">Durée d'assurance</label>
                                    <div class="admin-modern-input">
                                        <i class="bi bi-clock"></i>
                                        <select name="assurance_duration_months" id="assurance_duration_months" class="form-select">
                                            <option value="3" @selected(old('assurance_duration_months') == 3)>3 mois</option>
                                            <option value="6" @selected(old('assurance_duration_months', 12) == 6)>6 mois</option>
                                            <option value="12" @selected(old('assurance_duration_months', 12) == 12)>12 mois</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="admin-modern-status-preview js-status-preview" data-total-documents="{{ count($requiredDocumentTypes) }}">
                                    Statut calcule automatiquement.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="admin-modern-primary">
                            <i class="bi bi-check-circle"></i>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const preview = document.querySelector('.js-status-preview');
            const checkboxes = Array.from(document.querySelectorAll('.js-document-checkbox'));
            const requiredCheckboxes = checkboxes.filter((checkbox) => !checkbox.classList.contains('js-external-document-checkbox'));

            function updateStatusPreview() {
                if (!preview) return;

                const total = Number(preview.dataset.totalDocuments || requiredCheckboxes.length);
                const checked = requiredCheckboxes.filter((checkbox) => checkbox.checked).length;
                const missing = total - checked;
                const isOk = missing === 0;

                preview.classList.toggle('is-ok', isOk);
                preview.classList.toggle('is-danger', !isOk);
                preview.textContent = isOk
                    ? 'En regle : tous les documents requis sont coches.'
                    : 'En defaut : ' + missing + ' document(s) requis manquant(s).';
            }

            const assuranceDurationGroup = document.querySelector('.js-assurance-duration');
            const assuranceCheckbox = checkboxes.find((checkbox) => checkbox.value === 'assurance');

            function updateAssuranceDurationVisibility() {
                if (!assuranceDurationGroup || !assuranceCheckbox) {
                    return;
                }

                assuranceDurationGroup.style.display = assuranceCheckbox.checked ? 'block' : 'none';
            }

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    updateStatusPreview();
                    updateAssuranceDurationVisibility();
                });
            });

            updateStatusPreview();
            updateAssuranceDurationVisibility();

            @if($errors->any())
                new bootstrap.Modal(document.getElementById('addVehicleModal')).show();
            @endif
        });
    </script>
</x-app-layout>
