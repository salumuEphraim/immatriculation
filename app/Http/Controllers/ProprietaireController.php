<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Infraction;
use App\Models\User; // Importation du modèle User
use Illuminate\Support\Facades\Auth;

class ProprietaireController extends Controller
{
    /**
     * Afficher les véhicules appartenant à l'utilisateur connecté.
     */
    public function mesVehicules()
    {
        /** @var User $user */
        $user = Auth::user();

        // Le rouge sur vehicules() devrait disparaître ici
        $vehicules = $user->vehicules()->with('plaque')->get();
        
        return view('owner.vehicles', compact('vehicules'));
    }

    /**
     * Afficher les infractions validées pour les véhicules de l'utilisateur.
     */
    public function mesInfractions()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // On récupère les IDs des véhicules
        $vehiculeIds = $user->vehicules->pluck('id');

        // Sécurité : Si l'utilisateur n'a pas de véhicules, on renvoie une collection vide
        if ($vehiculeIds->isEmpty()) {
            $infractions = collect();
        } else {
            $infractions = Infraction::whereIn('vehicule_id', $vehiculeIds)
                ->where('statut', 'validee')
                ->with('vehicule.plaque')
                ->latest()
                ->get();
        }

        return view('owner.infractions', compact('infractions'));
    }
}