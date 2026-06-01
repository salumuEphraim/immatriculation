<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center text-white">
            <h2 class="h4 mb-0 fw-bold">
                <i class="bi bi-shield-shaded me-2 text-primary"></i>Poste de Contrôle : Lubumbashi
            </h2>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                <i class="bi bi-circle-fill me-2 small"></i>Agent Actif
            </span>
        </div>
    </x-slot>

    <style>
        /* Unification du fond pour éviter le conflit avec le layout global */
        .agent-dashboard {
            background: #0f172a; /* Bleu très sombre pro */
            min-height: 100vh;
            padding-bottom: 3rem;
            color: #f8fafc;
        }

        /* Cartes principales avec meilleur contraste */
        .card-main {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        /* Boutons d'actions rapides */
        .quick-action {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 20px;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 15px;
            height: 100%;
        }

        .quick-action:hover {
            transform: translateY(-5px);
            border-color: #dc2626; /* Rappel du rouge RoadShield */
            background: rgba(220, 38, 38, 0.1);
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.2);
        }

        .icon-box-circle {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.05);
        }

        /* Inputs pour Dark Mode */
        .input-custom {
            background: rgba(15, 23, 42, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            padding: 14px 18px !important;
        }

        .input-custom:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 38, 38, 0.25) !important;
        }

        .input-custom::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        .text-secondary-custom {
            color: #94a3b8 !important;
        }
    </style>

    <div class="agent-dashboard">
        <div class="container py-4">
            
            {{-- Alertes Flash --}}
            @if(session('success'))
                <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success shadow-lg mb-4 rounded-4 py-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-all fs-3 me-3"></i>
                        <div>
                            <strong>Succès !</strong> {{ session('success') }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- Section Identification --}}
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card-main p-4 p-md-5 text-center shadow-lg">
                        <div class="mb-5">
                            <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width:70px;height:70px;border-radius:20px;background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);">
                                <i class="bi bi-upc-scan text-primary fs-2"></i>
                            </div>
                            <h1 class="fw-bold text-white h3">Identification Véhicule</h1>
                            <p class="text-muted">Contrôle de conformité - Haut-Katanga</p>
                        </div>
                        
                        <div class="row g-4 justify-content-center">
                            {{-- Bouton Scanner --}}
                            <div class="col-md-5">
                                <a href="{{ route('agent.recherche') }}" class="btn btn-primary btn-lg w-100 py-4 rounded-4 shadow-lg border-0 d-flex flex-column align-items-center justify-content-center transition-all" style="background: linear-gradient(135deg, #dc2626, #991b1b);">
                                    <i class="bi bi-camera-fill mb-2 fs-2"></i>
                                    <span class="fw-bold">SCANNER LA PLAQUE</span>
                                </a>
                            </div>

                            {{-- Recherche Manuelle --}}
                            <div class="col-md-5">
                                <div class="p-3 rounded-4 border border-white border-opacity-10 h-100 d-flex flex-column justify-content-center" style="background: rgba(15, 23, 42, 0.4);">
                                    <form action="{{ route('shared.scan') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="text" name="plaque" class="form-control input-custom rounded-3" placeholder="Numéro de plaque..." required>
                                        </div>
                                        <button type="submit" class="btn btn-outline-light w-100 py-2 fw-bold rounded-3">
                                            <i class="bi bi-search me-2"></i>Recherche manuelle
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions Rapides --}}
            <div class="card-main p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h5 class="text-white mb-0 fw-bold">Actions de terrain</h5>
                    <span class="small text-muted">Lubumbashi, {{ date('H:i') }}</span>
                </div>

<div class="row g-3">
                    {{-- Contrôle Position --}}
                    <div class="col-12 col-md-4">
                        <button type="button" id="signalPosition" class="quick-action border-0 text-start w-100">
                            <div class="icon-box-circle text-success">
                                <i class="bi bi-geo-alt-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white">Signaler Position</div>
                                <div class="small text-muted">Carte OSM Live</div>
                            </div>
                        </button>
                    </div>

                    {{-- PV Digital --}}
                    <div class="col-12 col-md-4">
                        <button type="button" class="quick-action border-0 text-start w-100" data-bs-toggle="modal" data-bs-target="#infractionModal">
                            <div class="icon-box-circle text-danger">
                                <i class="bi bi-file-earmark-plus-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white">Signaler PV</div>
                                <div class="small text-muted">Établir un PV Digital</div>
                            </div>
                        </button>
                    </div>

                    {{-- Historique --}}
                    <div class="col-12 col-md-4">
                        <a class="quick-action" href="{{ route('agent.infractions.index') }}">
                            <div class="icon-box-circle text-primary">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white">Mes Contrôles</div>
                                <div class="small text-muted">Suivi des contrôles</div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- OSM Map Section --}}
                <div id="osmMapSection" class="mt-4 p-4 rounded-3" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-white mb-0 fw-bold">
                            <i class="bi bi-map-fill me-2"></i>Position Live (OpenStreetMap)
                        </h6>
                        <button type="button" id="closeMap" class="btn-close btn-close-white"></button>
                    </div>
                    <div id="map" style="height: 400px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1);"></div>
                    <div class="text-center mt-3">
                        <span id="positionStatus" class="small text-secondary-custom">Centrage sur votre position...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script GPS et UI --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au chargement
            const cards = document.querySelectorAll('.card-main, .quick-action');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });

            let map, marker;
            const latInput = sessionStorage.getItem('controle_lat') || -11.6646; // Lubumbashi default
            const lngInput = sessionStorage.getItem('controle_lng') || 27.4824;

            // Toggle OSM Map
            document.getElementById('signalPosition').addEventListener('click', function() {
                const mapSection = document.getElementById('osmMapSection');
                mapSection.style.display = mapSection.style.display === 'none' ? 'block' : 'none';
                if (mapSection.style.display === 'block') {
                    initMap();
                }
            });

            document.getElementById('closeMap').addEventListener('click', function() {
                document.getElementById('osmMapSection').style.display = 'none';
            });

            function initMap() {
                map = L.map('map').setView([latInput, lngInput], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                navigator.geolocation.getCurrentPosition(function(position) {
                    const pos = [position.coords.latitude, position.coords.longitude];
                    map.setView(pos, 18);
                    marker = L.marker(pos).addTo(map)
                        .bindPopup('<strong>Ma position</strong><br>Lat: ' + pos[0].toFixed(6) + '<br>Lng: ' + pos[1].toFixed(6))
                        .openPopup();

                    document.getElementById('positionStatus').innerHTML = '<i class="bi bi-geo-alt-fill text-success me-1"></i>Position GPS active';
                    document.getElementById('positionStatus').className = 'small text-success fw-bold';
                }, function() {
                    document.getElementById('positionStatus').innerHTML = '<i class="bi bi-x-circle text-warning me-1"></i>GPS indisponible';
                }, {enableHighAccuracy: true});
            }

            // Save position
            map?.on('click', function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng).addTo(map);
                sessionStorage.setItem('controle_lat', e.latlng.lat);
                sessionStorage.setItem('controle_lng', e.latlng.lng);
                document.getElementById('positionStatus').innerHTML = 'Position enregistrée ! <button onclick="clearPosition()" class="btn btn-sm btn-outline-light ms-2">Effacer</button>';
            });
        });

        function clearPosition() {
            sessionStorage.removeItem('controle_lat');
            sessionStorage.removeItem('controle_lng');
            document.getElementById('positionStatus').innerHTML = 'Position effacée';
        }
    </script>
</x-app-layout>