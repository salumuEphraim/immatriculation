<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use App\Services\BrokerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function index()
    {
        return view('agent.recherche');
    }

    public function manualSearch()
    {
        return view('agent.recherche-manuelle');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'plaque' => 'required|string|max:25',
        ]);

        $numeroPlaque = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $request->plaque));

        if ($numeroPlaque === '') {
            return redirect()->back()->with('error', 'La plaque détectée est illisible.');
        }

        return redirect()->route('shared.resultat', ['plaque' => $numeroPlaque]);
    }

    public function showResult($plaque)
    {
        $user = Auth::user();
        $numeroPlaque = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $plaque));

        $vehicule = Vehicule::with(['proprietaire.user', 'contraventions', 'documents'])
            ->get()
            ->first(function (Vehicule $vehicule) use ($numeroPlaque) {
                $current = strtoupper((string) preg_replace('/[^A-Z0-9]/', '', $vehicule->plaque_immatriculation ?? ''));

                return $current === $numeroPlaque;
            });

        if (!$vehicule) {
            Log::info('Recherche infructueuse pour la plaque', ['plaque' => $numeroPlaque]);

            if ($user->hasRole('proprietaire')) {
                return redirect()->route('proprietaire.vehicules')->with('error', 'Véhicule non répertorié.');
            }

            return redirect()->route('agent.recherche')->with('error', "Véhicule [$numeroPlaque] non répertorié.");
        }

        if ($user->hasRole('proprietaire') && !$vehicule->appartientA($user)) {
            Log::warning('Tentative d\'accès illégal', [
                'user_id' => $user->id,
                'vehicule_id' => $vehicule->id,
            ]);

            abort(403, 'Désolé, vous n\'êtes pas autorisé à consulter ce véhicule.');
        }

        $brokerData = app(BrokerService::class)->verifyAll($numeroPlaque);
        $plaqueNumero = $vehicule->plaque_immatriculation;

        return view('agent.recherche_resultat', compact('vehicule', 'plaqueNumero', 'brokerData'));
    }
}
