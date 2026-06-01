<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-white mb-0">
                <i class="bi bi-pencil-square me-2 text-danger"></i>Modifier le vehicule
            </h2>
            <a href="{{ route('admin.vehicules.index') }}" class="btn btn-outline-light">Retour</a>
        </div>
    </x-slot>

    <div class="py-4 px-3">
        <div class="container-fluid" style="max-width: 960px;">
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    Verifiez les champs du formulaire avant de continuer.
                </div>
            @endif

            <div class="card border-0 shadow-lg">
                <div class="card-header bg-dark text-white py-3">
                    <strong>{{ $vehicule->plaque_immatriculation }}</strong>
                </div>
                <div class="card-body bg-light p-4">
                    <form action="{{ route('admin.vehicules.update', $vehicule) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Proprietaire</label>
                                <select name="proprietaire_id" class="form-select @error('proprietaire_id') is-invalid @enderror" required>
                                    @foreach($proprietaires as $p)
                                        <option value="{{ $p->id }}" @selected(old('proprietaire_id', $vehicule->proprietaire_id) == $p->id)>{{ $p->nom }} {{ $p->prenom }} ({{ $p->telephone }})</option>
                                    @endforeach
                                </select>
                                @error('proprietaire_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Plaque Immatriculation</label>
                                <input type="text" name="plaque_immatriculation" class="form-control @error('plaque_immatriculation') is-invalid @enderror" required value="{{ old('plaque_immatriculation', $vehicule->plaque_immatriculation) }}">
                                @error('plaque_immatriculation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">VIN</label>
                                <input type="text" name="vin" class="form-control @error('vin') is-invalid @enderror" required value="{{ old('vin', $vehicule->vin) }}">
                                @error('vin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Marque</label>
                                <input type="text" name="marque" class="form-control @error('marque') is-invalid @enderror" required value="{{ old('marque', $vehicule->marque) }}">
                                @error('marque')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Modele</label>
                                <input type="text" name="modele" class="form-control @error('modele') is-invalid @enderror" required value="{{ old('modele', $vehicule->modele) }}">
                                @error('modele')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Couleur</label>
                                <input type="text" name="couleur" class="form-control @error('couleur') is-invalid @enderror" required value="{{ old('couleur', $vehicule->couleur) }}">
                                @error('couleur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Documents de bord possedes</label>
                                <div class="row g-2">
                                    @php
                                        $currentDocuments = old('documents', $selectedDocuments);
                                        $assuranceDocument = $vehicule->documents->firstWhere('type', 'assurance');
                                        $assuranceDuration = old('assurance_duration_months', $assuranceDocument?->data['assurance_duration_months'] ?? 12);
                                    @endphp
                                    @foreach($requiredDocumentTypes as $value => $label)
                                        <div class="col-md-6">
                                            <div class="form-check border rounded-3 bg-white px-3 py-2">
                                                <input class="form-check-input js-document-checkbox" type="checkbox" name="documents[]" value="{{ $value }}" id="doc-edit-{{ $value }}"
                                                    {{ in_array($value, $currentDocuments, true) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="doc-edit-{{ $value }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <label class="form-label fw-bold mt-4">Verification externe</label>
                                <div class="row g-2">
                                    @foreach($externalDocumentTypes as $value => $label)
                                        <div class="col-md-6">
                                            <div class="form-check border rounded-3 bg-white px-3 py-2">
                                                <input class="form-check-input js-document-checkbox js-external-document-checkbox" type="checkbox" name="documents[]" value="{{ $value }}" id="doc-edit-{{ $value }}"
                                                    {{ in_array($value, $currentDocuments, true) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="doc-edit-{{ $value }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3 js-assurance-duration" style="display: none;">
                                    <label for="assurance_duration_months" class="form-label fw-bold">Durée d'assurance</label>
                                    <select name="assurance_duration_months" id="assurance_duration_months" class="form-select">
                                        <option value="3" @selected($assuranceDuration == 3)>3 mois</option>
                                        <option value="6" @selected($assuranceDuration == 6)>6 mois</option>
                                        <option value="12" @selected($assuranceDuration == 12)>12 mois</option>
                                    </select>
                                </div>

                                @error('documents')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                                @error('documents.*')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Statut automatique</label>
                                <div class="border rounded-3 bg-white px-3 py-3 fw-semibold js-status-preview" data-total-documents="{{ count($requiredDocumentTypes) }}">
                                    Statut calcule automatiquement selon les documents coches.
                                </div>
                                <div class="small text-muted mt-2">Le vehicule est en regle uniquement si tous les documents locaux requis sont presentes.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.vehicules.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-danger">Mettre a jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var preview = document.querySelector('.js-status-preview');
            if (!preview) {
                return;
            }

            var checkboxes = Array.from(document.querySelectorAll('.js-document-checkbox'));
            var requiredCheckboxes = checkboxes.filter(function (checkbox) {
                return !checkbox.classList.contains('js-external-document-checkbox');
            });
            var totalDocuments = Number(preview.dataset.totalDocuments || requiredCheckboxes.length);
            var assuranceDurationGroup = document.querySelector('.js-assurance-duration');
            var assuranceCheckbox = checkboxes.find(function (checkbox) {
                return checkbox.value === 'assurance';
            });

            function updateStatusPreview() {
                var checked = requiredCheckboxes.filter(function (checkbox) { return checkbox.checked; }).length;
                var isEnRegle = checked === totalDocuments;

                preview.textContent = isEnRegle
                    ? 'En regle: tous les documents requis sont coches.'
                    : 'Pas en regle: il manque au moins un document requis.';

                preview.classList.toggle('text-success', isEnRegle);
                preview.classList.toggle('text-danger', !isEnRegle);
            }

            function updateAssuranceDurationVisibility() {
                if (!assuranceDurationGroup || !assuranceCheckbox) {
                    return;
                }

                assuranceDurationGroup.style.display = assuranceCheckbox.checked ? 'block' : 'none';
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    updateStatusPreview();
                    updateAssuranceDurationVisibility();
                });
            });

            updateStatusPreview();
            updateAssuranceDurationVisibility();
        });
    </script>
</x-app-layout>
