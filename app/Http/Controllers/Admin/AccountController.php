<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proprietaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:agent,proprietaire,admin',
            'telephone' => 'nullable|string|max:30',
            'adresse' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'postnom' => 'nullable|string|max:255',
            'numero_identite' => 'nullable|string|max:255|unique:proprietaires,numero_identite',
            'sexe' => 'nullable|in:masculin,feminin,autre',
            'date_naissance' => 'nullable|date|before:today',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'commune' => 'nullable|string|max:255',
            'quartier' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'telephone' => $request->telephone,
                ]);

                if ($request->role === 'proprietaire') {
                    Proprietaire::create([
                        'user_id' => $user->id,
                        'nom' => $request->name,
                        'postnom' => $request->postnom,
                        'prenom' => $request->prenom ?? '',
                        'email' => $request->email,
                        'telephone' => $request->telephone,
                        'adresse' => $request->adresse,
                        'commune' => $request->commune,
                        'quartier' => $request->quartier,
                        'numero_identite' => $request->numero_identite ?? 'PROPRIO-' . time(),
                        'sexe' => $request->sexe,
                        'date_naissance' => $request->date_naissance,
                        'lieu_naissance' => $request->lieu_naissance,
                        'nationalite' => $request->nationalite,
                        'profession' => $request->profession,
                    ]);
                }
            });

            return redirect()->route('admin.users.index')->with('success', 'Le compte ' . $request->role . ' a ete cree avec succes.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur SQL a Lubumbashi : ' . $e->getMessage());
        }
    }
}
