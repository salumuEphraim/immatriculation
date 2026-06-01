<x-app-layout>
    @php
        $plateValue = $plaqueNumero ?? $vehicule->plaque_immatriculation ?? '';
        $requiredDocumentMeta = [
            'assurance' => [
                'label' => 'Assurance',
                'detail' => 'Document local',
                'icon' => 'bi-shield-check',
            ],
            'vignette' => [
                'label' => 'Vignette fiscale',
                'detail' => 'Document local',
                'icon' => 'bi-receipt',
            ],
            'controle_technique' => [
                'label' => 'Controle technique',
                'detail' => 'Document local',
                'icon' => 'bi-tools',
            ],
            'carte_rose' => [
                'label' => 'Carte rose',
                'detail' => 'Document local',
                'icon' => 'bi-file-earmark-text',
            ],
        ];
        $documentStatus = collect($requiredDocumentMeta)->map(function ($meta, $type) use ($vehicule) {
            $document = $vehicule->documents->firstWhere('type', $type);
            $meta['valid'] = $document && (! $document->date_expiration || $document->date_expiration->isFuture() || $document->date_expiration->isToday());
            return $meta;
        })->values()->all();

        $invalidDocuments = collect($documentStatus)->filter(fn ($document) => ! $document['valid']);
        $isCompliant = $invalidDocuments->isEmpty();
        $externalCount = $brokerData['summary']['total_brokers_queried'] ?? 0;
        $externalStatus = $brokerData['summary']['global_status'] ?? null;
        $externalDocumentLabels = \App\Models\Vehicule::EXTERNAL_DOCUMENT_TYPES;
        $externalLocalDocuments = $vehicule->documents->whereIn('type', array_keys($externalDocumentLabels));
    @endphp

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 text-start">
            <div>
                <p class="text-white-50 small text-uppercase fw-bold mb-1">Controle routier</p>
                <h2 class="h4 mb-0 fw-bold text-white">
                    <i class="bi bi-shield-shaded me-2 text-danger"></i>
                    Resultat du scan
                </h2>
            </div>
            <a href="{{ Auth::user()->hasRole('proprietaire') ? route('dashboard') : route($backRoute ?? 'agent.recherche') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ Auth::user()->hasRole('proprietaire') ? 'Tableau de bord' : ($backLabel ?? 'Nouvelle recherche') }}
            </a>
        </div>
    </x-slot>

    <style>
        .scan-result-shell {
            display: grid;
            gap: 1rem;
        }

        .scan-verdict {
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
        }

        .scan-verdict-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            color: #fff;
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }

        .scan-verdict-head.is-alert {
            background: linear-gradient(135deg, #7f1d1d, #dc2626);
        }

        .scan-verdict-status {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .42rem .7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .14);
            font-size: .78rem;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .scan-plate {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 11rem;
            padding: .55rem 1.15rem;
            border: 1px solid rgba(15, 23, 42, .18);
            border-radius: 6px;
            background: #fff;
            color: #0f172a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: clamp(1.65rem, 4vw, 2.4rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: .04em;
            box-shadow: inset 0 -2px 0 rgba(15, 23, 42, .06);
        }

        .scan-summary {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(17rem, .9fr);
            gap: 1.25rem;
            padding: 1.25rem;
        }

        .scan-identity {
            display: grid;
            align-content: start;
            gap: .8rem;
        }

        .scan-identity-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .scan-field {
            padding: .8rem .9rem;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 8px;
            background: #f8fafc;
        }

        .scan-field span {
            display: block;
            margin-bottom: .18rem;
            color: #64748b;
            font-size: .78rem;
            font-weight: 700;
        }

        .scan-field strong {
            color: #0f172a;
            font-size: .95rem;
        }

        .document-list {
            display: grid;
            gap: .55rem;
        }

        .document-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            padding: .72rem .8rem;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 8px;
            background: #fff;
        }

        .document-name {
            display: flex;
            align-items: center;
            gap: .65rem;
            min-width: 0;
        }

        .document-icon {
            display: inline-grid;
            place-items: center;
            width: 2rem;
            height: 2rem;
            border-radius: 8px;
            background: rgba(100, 116, 139, .12);
            color: #334155;
            flex: 0 0 auto;
        }

        .document-row.is-valid .document-icon {
            background: rgba(16, 185, 129, .14);
            color: #047857;
        }

        .document-row.is-invalid .document-icon {
            background: rgba(220, 38, 38, .12);
            color: #b91c1c;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .32rem;
            padding: .34rem .55rem;
            border-radius: 999px;
            font-size: .68rem;
            font-weight: 900;
            line-height: 1;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-pill.is-valid {
            background: #047857;
            color: #fff;
        }

        .status-pill.is-invalid {
            background: #dc2626;
            color: #fff;
        }

        .external-panel {
            border: 1px solid rgba(15, 23, 42, .1);
            border-radius: 8px;
            background: #f8fafc;
        }

        .external-panel summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .85rem .95rem;
            cursor: pointer;
            color: #0f172a;
            font-weight: 800;
            list-style: none;
        }

        .external-panel summary::-webkit-details-marker {
            display: none;
        }

        .external-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem;
            padding: 0 .95rem .95rem;
        }

        .external-item {
            padding: .62rem .7rem;
            border-radius: 8px;
            background: #fff;
            color: #475569;
            font-size: .82rem;
        }

        .owner-note {
            padding: .8rem .9rem;
            border: 1px solid rgba(220, 38, 38, .12);
            border-radius: 8px;
            background: rgba(220, 38, 38, .05);
            color: #334155;
        }

        .action-panel {
            display: grid;
            gap: .75rem;
            padding: 1rem;
            border: 1px solid rgba(220, 38, 38, .16);
            border-radius: 8px;
            background: rgba(220, 38, 38, .06);
        }

        @media (max-width: 991.98px) {
            .scan-summary,
            .scan-identity-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .scan-verdict-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .external-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="py-4 scan-result-shell">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-0">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="scan-verdict">
            <div class="scan-verdict-head {{ $isCompliant ? '' : 'is-alert' }}">
                <div>
                    <p class="small text-white-50 text-uppercase fw-bold mb-1">Verdict principal</p>
                    <h3 class="h5 fw-bold mb-0">
                        {{ $isCompliant ? 'Vehicule en regle' : 'Vehicule en infraction documentaire' }}
                    </h3>
                </div>
                <span class="scan-verdict-status">
                    <i class="bi {{ $isCompliant ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
                    {{ $isCompliant ? 'Autorise' : $invalidDocuments->count().' anomalie(s)' }}
                </span>
            </div>

            <div class="scan-summary">
                <div class="scan-identity">
                    <div class="text-center text-lg-start">
                        <div class="scan-plate">{{ $plateValue }}</div>
                        <p class="text-muted small mb-0 mt-2">Immatriculation officielle RDC</p>
                    </div>

                    <div class="scan-identity-grid">
                        <div class="scan-field">
                            <span>Marque et modele</span>
                            <strong>{{ $vehicule->marque }} {{ $vehicule->modele }}</strong>
                        </div>
                        <div class="scan-field">
                            <span>N chassis (VIN)</span>
                            <strong>{{ $vehicule->vin ?: 'Non renseigne' }}</strong>
                        </div>
                        <div class="scan-field">
                            <span>Couleur</span>
                            <strong>{{ $vehicule->couleur ?: 'Non renseignee' }}</strong>
                        </div>
                        <div class="scan-field">
                            <span>Proprietaire</span>
                            <strong>{{ $vehicule->proprietaire->nom ?? 'Non identifie' }}</strong>
                        </div>
                        <div class="scan-field">
                            <span>Telephone</span>
                            <strong>{{ $vehicule->proprietaire->telephone ?: 'Non renseigne' }}</strong>
                        </div>
                        <div class="scan-field">
                            <span>Adresse domicile</span>
                            <strong>{{ $vehicule->proprietaire->adresse ?: 'Non renseignee' }}</strong>
                        </div>
                    </div>

                    <div class="owner-note">
                        <div class="fw-bold text-dark mb-1">
                            <i class="bi bi-person-vcard me-2 text-danger"></i>Identite proprietaire
                        </div>
                        <div class="small">
                            Sexe: {{ ucfirst($vehicule->proprietaire->sexe ?? 'Non renseigne') }} ·
                            Nationalite: {{ $vehicule->proprietaire->nationalite ?: 'Non renseignee' }} ·
                            Commune: {{ $vehicule->proprietaire->commune ?: 'Non renseignee' }}
                        </div>
                    </div>
                </div>

                <div class="document-list">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <h4 class="h6 fw-bold text-dark mb-0">
                            <i class="bi bi-folder-check me-2 text-danger"></i>Documents controles
                        </h4>
                        <span class="text-muted small">{{ collect($documentStatus)->where('valid', true)->count() }}/{{ count($documentStatus) }} valides</span>
                    </div>

                    @foreach($documentStatus as $document)
                        <div class="document-row {{ $document['valid'] ? 'is-valid' : 'is-invalid' }}">
                            <div class="document-name">
                                <span class="document-icon"><i class="bi {{ $document['icon'] }}"></i></span>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate">{{ $document['label'] }}</div>
                                    <div class="small text-muted">{{ $document['detail'] }}</div>
                                </div>
                            </div>
                            <span class="status-pill {{ $document['valid'] ? 'is-valid' : 'is-invalid' }}">
                                <i class="bi {{ $document['valid'] ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                {{ $document['valid'] ? 'Valide' : 'Manquant' }}
                            </span>
                        </div>
                    @endforeach

                    @if($externalCount > 0)
                        <details class="external-panel mt-2">
                            <summary>
                                <span><i class="bi bi-cloud-check me-2 text-danger"></i>Verification externe</span>
                                <span class="small text-muted">{{ $externalCount }} service(s)</span>
                            </summary>
                            <div class="px-3 pb-2 small text-muted">
                                Source de controle complementaire, separee du verdict principal local.
                                @if($externalStatus)
                                    Statut global:
                                    <span class="status-pill {{ $externalStatus === 'ok' ? 'is-valid' : 'is-invalid' }}">
                                        {{ ucfirst($externalStatus) }}
                                    </span>
                                @endif
                            </div>
                            <div class="external-grid">
                                @foreach($externalDocumentLabels as $type => $label)
                                    @php
                                        $localExternalDocument = $externalLocalDocuments->firstWhere('type', $type);
                                        $localExternalValid = $localExternalDocument && (! $localExternalDocument->date_expiration || $localExternalDocument->date_expiration->isFuture() || $localExternalDocument->date_expiration->isToday());
                                    @endphp
                                    <div class="external-item">
                                        <div class="fw-bold text-dark mb-1">{{ $label }}</div>
                                        <span class="status-pill {{ $localExternalValid ? 'is-valid' : 'is-invalid' }}">
                                            {{ $localExternalValid ? 'Enregistre' : 'Non ajoute' }}
                                        </span>
                                    </div>
                                @endforeach
                                @foreach(($brokerData['statuses'] ?? []) as $type => $brokers)
                                    @if(count($brokers) > 0)
                                        <div class="external-item">
                                            <div class="fw-bold text-dark mb-1">{{ ucfirst(str_replace('_', ' ', $type)) }}</div>
                                            @foreach($brokers as $name => $status)
                                                <span class="badge bg-{{ $status === 'valid' ? 'success' : ($status === 'offline' ? 'secondary' : 'danger') }} me-1 mb-1">
                                                    {{ strtoupper(substr($status, 0, 3)) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </details>
                    @endif

                    @if(Auth::user()->hasRole('agent') || Auth::user()->hasRole('admin'))
                        @if(! $isCompliant)
                            <form id="form-contravention-scan" action="{{ route('agent.infractions.store') }}" method="POST" class="action-panel mt-2">
                                @csrf
                                <input type="hidden" name="vehicule_id" value="{{ $vehicule->id }}">
                                <input type="hidden" name="plaque" value="{{ $plateValue }}">
                                <input type="hidden" name="type" value="Defaut de documents">
                                <input type="hidden" name="lieu" value="Lubumbashi - Controle mobile">
                                <input type="hidden" name="latitude" id="geo_lat_scan" value="">
                                <input type="hidden" name="longitude" id="geo_lng_scan" value="">
                                <p class="small text-muted mb-0" id="geo_status_scan">
                                    <i class="bi bi-geo-alt me-1"></i>Position GPS : localisation en cours...
                                </p>
                                <button type="submit" class="btn btn-danger btn-lg fw-bold py-3">
                                    <i class="bi bi-file-earmark-plus me-2"></i>Etablir une contravention
                                </button>
                            </form>
                            <script>
                                (function () {
                                    const latEl = document.getElementById('geo_lat_scan');
                                    const lngEl = document.getElementById('geo_lng_scan');
                                    const statusEl = document.getElementById('geo_status_scan');

                                    if (!latEl || !('geolocation' in navigator)) {
                                        if (statusEl) {
                                            statusEl.innerHTML = '<i class="bi bi-geo-alt me-1"></i>GPS indisponible, la contravention sera enregistree sans position.';
                                        }
                                        return;
                                    }

                                    navigator.geolocation.getCurrentPosition(
                                        function (position) {
                                            latEl.value = position.coords.latitude;
                                            lngEl.value = position.coords.longitude;
                                            if (statusEl) {
                                                statusEl.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i>Position GPS enregistree.';
                                            }
                                        },
                                        function () {
                                            if (statusEl) {
                                                statusEl.innerHTML = '<i class="bi bi-exclamation-triangle text-warning me-1"></i>GPS refuse, activez la localisation pour le suivi sur carte.';
                                            }
                                        },
                                        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                                    );
                                })();
                            </script>
                        @else
                            <div class="text-center p-3 rounded-3 border border-success-subtle bg-success-subtle text-success mt-2">
                                <i class="bi bi-shield-check fs-2"></i>
                                <p class="mt-2 fw-bold mb-0">Circulation autorisee</p>
                            </div>
                        @endif
                    @else
                        @if(! $isCompliant)
                            <div class="alert alert-danger mb-0 mt-2">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Action requise :</strong> veuillez regulariser vos documents aupres des services concernes a Lubumbashi.
                            </div>
                        @else
                            <div class="alert alert-success mb-0 mt-2">
                                <i class="bi bi-check-all me-2"></i>Votre vehicule est en regle. Bonne route.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
