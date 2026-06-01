<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-white mb-0">
                <i class="bi bi-receipt me-2 text-warning"></i>Confirmation de Contravention
            </h2>
            <div class="d-flex gap-2 d-print-none">
                {{-- Formulaire d'envoi d'email --}}
                <form action="{{ route('agent.infractions.email', $contravention->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i> Envoyer au propriétaire
                    </button>
                </form>

                <a href="{{ route('agent.infractions.pdf', $contravention->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i> Télécharger PDF
                </a>

                <button onclick="window.print()" class="btn btn-light btn-sm">
                    <i class="bi bi-printer me-1"></i> Imprimer le reçu
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-5">
        <div class="container">
            {{-- Messages flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg border-0 bg-white text-dark" style="border-radius: 0; border-top: 8px solid #dc3545 !important;">
                        <div class="card-body p-5">
                            {{-- En-tête --}}
                            <div class="text-center mb-4">
                                <h3 class="fw-bold mb-0">ROADSHIELD</h3>
                                <p class="text-muted small">Province du Haut-Katanga<br>Ville de Lubumbashi</p>
                                <div class="py-2 border-top border-bottom my-3">
                                    <h5 class="mb-0 fw-bold">PROVINCIAUX DES TRANSPORTS</h5>
                                </div>
                            </div>

                            {{-- Détails --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Référence :</span>
                                    <span class="fw-bold">{{ $contravention->code_unique }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Date :</span>
                                    <span>{{ $contravention->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Lieu :</span>
                                    <span>{{ $contravention->lieu }}</span>
                                </div>
                            </div>

                            <hr style="border-style: dashed;">

                            {{-- Véhicule --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-uppercase small mb-3">Véhicule Contrôlé</h6>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Plaque :</span>
                                    <span class="fw-bold text-primary">{{ $contravention->vehicule->plaque_immatriculation }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Marque :</span>
                                    <span>{{ $contravention->vehicule->marque }} {{ $contravention->vehicule->modele }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Propriétaire :</span>
                                    <span>{{ $contravention->vehicule->proprietaire->nom ?? 'Inconnu' }}</span>
                                </div>
                            </div>

                            <hr style="border-style: dashed;">

                            {{-- Amende --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-uppercase small mb-3">Détails de l'amende</h6>
                                <p class="mb-1 fw-bold text-danger">{{ $contravention->type }}</p>
                                <div class="bg-light p-3 rounded d-flex justify-content-between align-items-center mt-3">
                                    <span class="h5 mb-0 fw-bold">TOTAL À PAYER</span>
                                    <span class="h4 mb-0 fw-bold text-dark">{{ number_format($contravention->montant, 0, ',', '.') }} FC</span>
                                </div>
                            </div>

                            {{-- Pied --}}
                            <div class="text-center mt-5">
                                <div class="mb-3">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/verifier/' . $contravention->code_unique) }}" 
                                         alt="QR Code de vérification" 
                                         class="mx-auto d-block border p-1 bg-white shadow-sm">
                                </div>
                                <p class="small text-muted">
                                    Agent verbalisateur : {{ Auth::user()->name }}<br>
                                    <strong>Scannez pour vérifier l'authenticité</strong><br>
                                    Merci de régulariser votre situation sous 48h.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4 d-print-none">
                        <a href="{{ route('agent.recherche') }}" class="btn btn-outline-light border-0">
                            <i class="bi bi-arrow-left me-1"></i> Retour au scanner
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .navbar, .d-print-none, header, footer, .alert { display: none !important; }
            body { background: white !important; margin: 0; padding: 0; }
            .py-5 { padding: 0 !important; }
            .container { width: 100% !important; max-width: 100% !important; padding: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; width: 100%; }
            .text-white { color: black !important; }
        }
    </style>
</x-app-layout>
