<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 text-white">
            <i class="bi bi-search me-2"></i>Recherche Manuelle de Plaque
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid px-0">
            <div class="bg-dark overflow-hidden rounded p-4 border border-light border-opacity-10">
                <form action="{{ route('shared.scan') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="plaque" class="form-label fw-bold">
                            Saisir le numéro de plaque
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white">
                                <i class="bi bi-reception-4"></i>
                            </span>
                            <input type="text" name="plaque" id="plaque" 
                                   class="form-control bg-dark border-secondary text-white text-uppercase" 
                                   placeholder="Ex: 1234AB/05" required>
                        </div>
                        <p class="text-muted small mt-2">
                            Utilisez ce formulaire si la plaque est trop sale ou illisible pour l'OCR.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 shadow">
                        <i class="bi bi-check2-circle me-2"></i> Vérifier le statut
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>