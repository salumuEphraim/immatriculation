<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Plaque;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    /**
     * Afficher la page de recherche (Formulaire + Scanner).
     */
    public function searchForm()
    {
        return view('agent.recherche');
    }

    /**
     * Traiter la recherche de plaque.
     */
    public function search(Request $request)
    {
        // 1. Validation stricte
        $request->validate([
            'numero_plaque' => 'required|string|max:20'
        ]);

        // 2. Nettoyage de la saisie (Supprime les espaces et met en majuscules)
        // Très important pour que "abc 1234" ou "abc-1234" correspondent à "ABC-1234"
        $numeroSaisi = strtoupper(trim($request->numero_plaque));

        // 3. Recherche avec Eager Loading pour la performance
        $plaque = Plaque::with(['vehicule.proprietaire', 'vehicule.infractions.agent'])
            ->where('numero_plaque', $numeroSaisi)
            ->first();

        // 4. Gestion de l'erreur si non trouvé
        if (!$plaque) {
            return back()
                ->withInput() // Garde le texte saisi dans le champ
                ->with('error', "Le véhicule avec la plaque « $numeroSaisi » est introuvable.");
        }

        // 5. Redirection vers la vue détails
        return view('agent.details', compact('plaque'));
    }
}