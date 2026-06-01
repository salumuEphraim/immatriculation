<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vehicule;
use App\Models\Plaque;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer l'Admin (disabled - use AdminUserSeeder instead)
        /*
        User::create([
            'name' => 'Admin Système',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        */

        // 2. Créer l'Agent
        User::create([
            'name' => 'Agent Kabila',
            'email' => 'agent@test.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
        ]);

        // 3. Créer le Propriétaire
$proprio = User::create([
            'name' => 'Jean Dupont',
            'email' => 'proprio@test.com',
            'password' => Hash::make('password'),
            'role' => 'proprietaire',
        ]);

        // Create proprietaire linked
        $proprio->proprietaire()->create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'proprio@test.com',
            'telephone' => '0812345678',
            'numero_identite' => '123456789',
        ]);

        // 4. Lui ajouter un véhicule de démo
        $vehicule = Vehicule::create([
            'proprietaire_id' => $proprio->proprietaire->id,
            'plaque_immatriculation' => 'ABC1234',
            'marque' => 'Toyota',
            'modele' => 'Land Cruiser',
            'vin' => 'TOY789ABC123456',
            'couleur' => 'Blanc',
        ]);

        // Add sample documents
        $vehicule->documents()->create([
            'type' => 'plaque',
            'numero_plaque' => 'ABC-1234',
            'date_emission' => now()->subYear(),
            'date_expiration' => now()->addYear(),
        ]);

        $vehicule->documents()->create([
            'type' => 'vignette',
            'date_emission' => now()->subMonth(),
            'date_expiration' => now()->addMonth(),
        ]);
    }
}