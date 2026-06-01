<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs et le formulaire de création.
     */
    public function index()
    {
        // On trie par date de création pour voir les nouveaux en premier
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Création d'un nouvel utilisateur (Agent, Admin ou Propriétaire).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,agent,proprietaire'],
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "Le compte de {$request->name} a été créé avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', "Erreur lors de la création : " . $e->getMessage());
        }
    }

    /**
     * Mise à jour rapide du rôle (ex: transformer un citoyen en agent).
     */
    public function updateRole(Request $request, User $user)
    {
        // Sécurité : on empêche de changer son propre rôle pour ne pas se bloquer
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $request->validate([
            'role' => ['required', 'in:admin,agent,proprietaire'],
        ]);

        $user->update(['role' => $request->role]);

        return redirect()->back()->with('success', "Le rôle de {$user->name} est désormais : " . ucfirst($request->role));
    }

    /**
     * Suppression sécurisée d'un compte.
     */
    public function destroy(User $user)
    {
        // Empêcher l'auto-suppression
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Suppression impossible : c\'est votre propre compte.');
        }

        $user->delete();

        return redirect()->back()->with('success', "L'utilisateur a été retiré du système.");
    }
}