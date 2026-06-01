<?php

namespace App\Services;

use App\Models\BrokerService as BrokerModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class BrokerService
{
    /**
     * Vérifie TOUS les documents via tous les brokers actifs.
     */
    public function verifyAll(string $plaque): array
    {
        $cacheKey = 'broker_verify_' . md5($plaque);
        return Cache::remember($cacheKey, Config::get('brokers.cache_ttl', 3600), function () use ($plaque) {
            $docTypes = ['assurance', 'vignette', 'controle_technique', 'carte_rose', 'permis_conduire', 'plaque'];
            $statuses = [];
            $brokerCount = 0;

            foreach ($docTypes as $type) {
            $brokers = BrokerModel::active()->whereJsonContains('doc_types', $type)->get();
                $typeStatuses = [];

                foreach ($brokers as $broker) {
                    $brokerCount++;
                    $status = $this->queryBroker($broker, $plaque, $type);
                    $typeStatuses[$broker->name] = $status;
                }

                $statuses[$type] = $typeStatuses;
            }

            // Global status
            $allValid = collect($statuses)->flatten(1)->every(fn($s) => $s === 'valid');
            $hasOffline = collect($statuses)->flatten(1)->contains(fn($s) => $s === 'offline');

            return [
                'statuses' => $statuses,
                'summary' => [
                    'total_brokers_queried' => $brokerCount,
                    'all_valid' => $allValid,
                    'has_offline' => $hasOffline,
                    'global_status' => $allValid ? 'ok' : ($hasOffline ? 'partial' : 'bad'),
                ],
            ];
        });
    }

    /**
     * Query single broker.
     */
    private function queryBroker(BrokerModel $broker, string $plaque, string $type): string
    {
        if (Config::get('brokers.mock_mode', true)) {
            return $this->mockResponse();
        }

        try {
            $response = Http::timeout($broker->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $broker->api_key,
                    'Accept' => 'application/json',
                ])
                ->withOptions(Config::get('brokers.http_options', []))
                ->get($broker->endpoint, [
                    'plaque' => $plaque,
                    'type' => $type,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['status'] ?? 'unknown';
            }
        } catch (\Exception $e) {
            Log::warning("Broker {$broker->name} failed: " . $e->getMessage());
        }

        return 'offline';
    }

    /**
     * Mock response for development.
     */
    private function mockResponse(): string
    {
        $statuses = ['valid', 'invalid', 'pending'];
        return $statuses[array_rand($statuses)];
    }
}

