<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">
            Signaler une infraction : {{ $vehicule->marque }} ({{ $vehicule->vin }})
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid px-0">
            <div class="bg-white overflow-hidden shadow-sm rounded p-4">
                <form action="{{ route('agent.infraction.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="vehicule_id" value="{{ $vehicule->id }}">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Type d'infraction</label>
                            <select name="type_infraction" class="form-select" required>
                                <option value="Excès de vitesse">Excès de vitesse</option>
                                <option value="Défaut d'assurance">Défaut d'assurance</option>
                                <option value="Stationnement interdit">Stationnement interdit</option>
                                <option value="Plaque non conforme">Plaque non conforme</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Montant de l'amende (CDF)</label>
                            <input type="number" name="montant" class="form-control" placeholder="Ex: 50000" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Details / Observations</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Precisez les circonstances..."></textarea>
                        </div>

                        <div class="col-12 d-flex justify-content-end mt-2">
                            <button type="submit" class="btn btn-danger">
                                Enregistrer le signalement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>