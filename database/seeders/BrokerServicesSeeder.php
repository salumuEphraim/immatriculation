<?php

namespace Database\Seeders;

use App\Models\BrokerService;
use Illuminate\Database\Seeder;

class BrokerServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brokers = [
            [
                'name' => 'DGI Lubumbashi - Vignette Fiscale',
                'endpoint' => 'https://httpbin.org/anything/vignette', // Mock API
                'api_key' => 'mock_dgi_key_123',
                'doc_types' => ['vignette'],
                'active' => true,
                'timeout' => 3,
                'description' => 'Direction Générale des Impôts - Vérification vignette fiscale RDC Lubumbashi',
            ],
            [
                'name' => 'SONAS Lubumbashi - Carte Rose',
                'endpoint' => 'https://httpbin.org/anything/carte_rose',
                'api_key' => 'mock_sonas_key_456',
                'doc_types' => ['carte_rose'],
                'active' => true,
                'timeout' => 4,
                'description' => 'Service National d\'Immatriculation des Vehicules - Carte grise Lubumbashi',
            ],
            [
                'name' => 'CTR Lubumbashi - Contrôle Technique',
                'endpoint' => 'https://httpbin.org/anything/controle_technique',
                'api_key' => 'mock_ctr_key_789',
                'doc_types' => ['controle_technique'],
                'active' => true,
                'timeout' => 5,
                'description' => 'Centre de Contrôle Technique Routier - Lubumbashi',
            ],
            [
                'name' => 'Allianz Assurance Lubumbashi',
                'endpoint' => 'https://httpbin.org/anything/assurance',
                'api_key' => 'mock_allianz_key_abc',
                'doc_types' => ['assurance'],
                'active' => true,
                'timeout' => 3,
                'description' => 'Assurance véhicule - Agence Lubumbashi',
            ],
            [
                'name' => 'SODECO Lubumbashi - Permis',
                'endpoint' => 'https://httpbin.org/anything/permis_conduire',
                'api_key' => 'mock_sodeco_key_def',
                'doc_types' => ['permis_conduire'],
                'active' => true,
                'timeout' => 4,
                'description' => 'Service de délivrance permis - Lubumbashi',
            ],
            [
                'name' => 'Plaques SONAS Central',
                'endpoint' => 'https://httpbin.org/anything/plaque',
                'api_key' => 'mock_plaques_key_ghi',
                'doc_types' => ['plaque', 'immatriculation'],
                'active' => true,
                'timeout' => 2,
                'description' => 'Vérification plaques d\'immatriculation SONAS',
            ],
        ];

        BrokerService::truncate(); // Reset for demo
        foreach ($brokers as $broker) {
            BrokerService::create($broker);
        }
    }
}
