<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'agent@test.com'],
            [
                'name' => 'Agent Kabila',
                'password' => Hash::make('password'),
                'role' => 'agent',
            ]
        );

        $proprio = User::updateOrCreate(
            ['email' => 'proprio@test.com'],
            [
                'name' => 'Jean Dupont',
                'password' => Hash::make('password'),
                'role' => 'proprietaire',
            ]
        );

        $proprietaire = $proprio->proprietaire()->updateOrCreate(
            ['email' => 'proprio@test.com'],
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'telephone' => '0812345678',
                'numero_identite' => '123456789',
            ]
        );

        $vehicule = Vehicule::updateOrCreate(
            ['plaque_immatriculation' => 'ABC1234'],
            [
                'proprietaire_id' => $proprietaire->id,
                'marque' => 'Toyota',
                'modele' => 'Land Cruiser',
                'vin' => 'TOY789ABC123456',
                'couleur' => 'Blanc',
            ]
        );

        $vehicule->documents()->updateOrCreate(
            ['type' => 'plaque'],
            [
                'numero_plaque' => 'ABC-1234',
                'date_emission' => now()->subYear(),
                'date_expiration' => now()->addYear(),
            ]
        );

        $vehicule->documents()->updateOrCreate(
            ['type' => 'vignette'],
            [
                'date_emission' => now()->subMonth(),
                'date_expiration' => now()->addMonth(),
            ]
        );
    }
}
