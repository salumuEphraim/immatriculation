<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proprietaire;
use App\Models\Vehicule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehiculeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vehicule::with(['proprietaire', 'documents', 'contraventions'])->latest();

        if ($request->filled('q')) {
            $search = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->q));

            $query->where(function ($q) use ($search) {
                $q->whereRaw('UPPER(REPLACE(REPLACE(plaque_immatriculation, "-", ""), "/", "")) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('proprietaire', function ($subQuery) use ($search) {
                        $subQuery->where('nom', 'LIKE', "%{$search}%")
                            ->orWhere('prenom', 'LIKE', "%{$search}%")
                            ->orWhere('postnom', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->status === 'regle') {
            $query->where('statut_reglementaire', 'en_regle');
        } elseif ($request->status === 'defaut') {
            $query->where('statut_reglementaire', 'pas_en_regle');
        }

        $vehicules = $query->paginate(15)->withQueryString();
        $proprietaires = Proprietaire::orderBy('nom')->orderBy('prenom')->get();
        $documentTypes = Vehicule::DOCUMENT_TYPES;
        $requiredDocumentTypes = Vehicule::REQUIRED_DOCUMENT_TYPES;
        $externalDocumentTypes = Vehicule::EXTERNAL_DOCUMENT_TYPES;
        return view('admin.vehicules.index', compact('vehicules', 'proprietaires', 'documentTypes', 'requiredDocumentTypes', 'externalDocumentTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateVehicule($request);
        $statutReglementaire = Vehicule::computeStatutFromDocuments($validated['documents'] ?? []);

        try {
            $vehicule = Vehicule::create([
                'plaque_immatriculation' => strtoupper(trim(preg_replace('/[^A-Z0-9]/', '', $validated['plaque_immatriculation']))),
                'vin' => strtoupper(trim($validated['vin'])),
                'marque' => trim($validated['marque']),
                'modele' => trim($validated['modele']),
                'couleur' => trim($validated['couleur']),
                'proprietaire_id' => $validated['proprietaire_id'],
                'statut_reglementaire' => $statutReglementaire,
            ]);

            $this->syncDocuments($vehicule, $validated['documents'] ?? [], $validated['assurance_duration_months'] ?? 12);

            return redirect()->route('admin.vehicules.index')
                ->with('success', 'Vehicule enregistre avec succes.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la creation du vehicule : ' . $e->getMessage());
        }
    }

    public function edit(Vehicule $vehicule): View
    {
        $vehicule->load(['documents', 'proprietaire']);

        $proprietaires = Proprietaire::orderBy('nom')->orderBy('prenom')->get();
        $documentTypes = Vehicule::DOCUMENT_TYPES;
        $requiredDocumentTypes = Vehicule::REQUIRED_DOCUMENT_TYPES;
        $externalDocumentTypes = Vehicule::EXTERNAL_DOCUMENT_TYPES;
        $selectedDocuments = $vehicule->documents
            ->pluck('type')
            ->intersect(array_keys($documentTypes))
            ->values()
            ->all();

        return view('admin.vehicules.edit', compact(
            'vehicule',
            'proprietaires',
            'documentTypes',
            'requiredDocumentTypes',
            'externalDocumentTypes',
            'selectedDocuments'
        ));
    }

    public function update(Request $request, Vehicule $vehicule): RedirectResponse
    {
        $validated = $this->validateVehicule($request, $vehicule);
        $statutReglementaire = Vehicule::computeStatutFromDocuments($validated['documents'] ?? []);

        $vehicule->update([
            'plaque_immatriculation' => strtoupper(trim(preg_replace('/[^A-Z0-9]/', '', $validated['plaque_immatriculation']))),
            'vin' => strtoupper(trim($validated['vin'])),
            'marque' => trim($validated['marque']),
            'modele' => trim($validated['modele']),
            'couleur' => trim($validated['couleur']),
            'proprietaire_id' => $validated['proprietaire_id'],
            'statut_reglementaire' => $statutReglementaire,
        ]);

        $this->syncDocuments($vehicule, $validated['documents'] ?? [], $validated['assurance_duration_months'] ?? 12);

        return redirect()->route('admin.vehicules.index')
            ->with('success', 'Vehicule mis a jour avec succes.');
    }

    private function validateVehicule(Request $request, ?Vehicule $vehicule = null): array
    {
        $documentKeys = implode(',', array_keys(Vehicule::DOCUMENT_TYPES));
        return $request->validate([
            'plaque_immatriculation' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicules', 'plaque_immatriculation')->ignore($vehicule?->id),
            ],
            'vin' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicules', 'vin')->ignore($vehicule?->id),
            ],
            'marque' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'couleur' => 'required|string|max:255',
            'proprietaire_id' => 'required|exists:proprietaires,id',
            'documents' => 'nullable|array',
            'documents.*' => 'in:' . $documentKeys,
            'assurance_duration_months' => [
                'nullable',
                'integer',
                Rule::in([3, 6, 12]),
                Rule::requiredIf(fn () => in_array('assurance', $request->input('documents', []), true)),
            ],
        ]);
    }

    private function syncDocuments(Vehicule $vehicule, array $documents, int $assuranceDurationMonths = 12): void
    {
        $selected = collect($documents)
            ->filter(fn ($type) => array_key_exists($type, Vehicule::DOCUMENT_TYPES))
            ->unique()
            ->values();

        $vehicule->documents()
            ->whereIn('type', array_keys(Vehicule::DOCUMENT_TYPES))
            ->whereNotIn('type', $selected->all())
            ->delete();

        foreach ($selected as $type) {
            $expirationDate = $this->getDefaultExpirationDate($type, $assuranceDurationMonths);
            $existingDocument = $vehicule->documents()->firstWhere('type', $type);

            if ($existingDocument) {
                $payload = ['data' => array_merge($existingDocument->data ?? [], [
                    'saisi_par_admin' => true,
                    'auto_generated_expiration' => true,
                    'assurance_duration_months' => $assuranceDurationMonths,
                ])];

                if ($type === 'assurance' && ($existingDocument->data['assurance_duration_months'] ?? 12) !== $assuranceDurationMonths) {
                    $payload['date_expiration'] = $expirationDate;
                }

                $existingDocument->update($payload);
            } else {
                $vehicule->documents()->create([
                    'type' => $type,
                    'date_emission' => now()->toDateString(),
                    'date_expiration' => $expirationDate,
                    'data' => ['saisi_par_admin' => true, 'auto_generated_expiration' => true, 'assurance_duration_months' => $assuranceDurationMonths],
                ]);
            }
        }
    }

    /**
     * Calcule la date d'expiration par défaut selon le type de document
     */
    private function getDefaultExpirationDate(string $type, int $assuranceDurationMonths = 12): string
    {
        $now = now();
        
        return match($type) {
            'assurance' => $now->copy()->addMonths($assuranceDurationMonths)->toDateString(),
            'vignette' => $now->copy()->addYear()->toDateString(),
            'controle_technique' => $now->copy()->addMonths(6)->toDateString(),
            'carte_rose' => $now->copy()->addYears(5)->toDateString(),
            'permis_conduire' => $now->copy()->addYears(5)->toDateString(),
            'plaque' => $now->copy()->addYears(5)->toDateString(),
            'immatriculation' => $now->copy()->addYears(5)->toDateString(),
            default => $now->copy()->addYear()->toDateString(),
        };
    }
}
