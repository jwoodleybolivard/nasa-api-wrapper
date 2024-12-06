<?php

namespace App\Models\Repositories;

use App\Domain\Repositories\DonkiRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DonkiRepository implements DonkiRepositoryInterface
{
    private string $baseUrl;
    private string $apiKey;
    private const TIMEOUT = 60;
    private const YEAR = 2023;
    private const CACHE_TTL = 3600;

    public function __construct()
    {
        $this->baseUrl = config('nasa.base_url');
        $this->apiKey = config('nasa.api_key');
    }

    public function getAllInstruments(): array
    {
        return Cache::remember('all_instruments_' . self::YEAR, self::CACHE_TTL, function () {
            return $this->fetchDataFromEndpoints('instruments');
        });
    }

    public function getAllActivityIds(): array
    {
        return Cache::remember('all_activity_ids_' . self::YEAR, self::CACHE_TTL, function () {
            return $this->fetchDataFromEndpoints('activityIDs');
        });
    }


    public function getInstrumentUsagePercentages(): array
    {
        return Cache::remember('instrument_usage_percentages_' . self::YEAR, self::CACHE_TTL, function () {
            $instruments = $this->getAllInstruments();
            // $instruments = array_values(array_unique($instruments));
            $usages = array_map([$this, 'getActivityPercentageByInstrument'], $instruments);
            // $usages = array_values(array_unique($usages));
            $totalUsage = array_sum($usages);

            $percentages = [];
            foreach ($instruments as $index => $instrument) {
                $percentages[$instrument] = $totalUsage > 0 ? $usages[$index] / $totalUsage : 0;
            }

            return $percentages;
        });
    }

    public function getActivityPercentageByInstrument(string $instrument): array
    {
        return Cache::remember("instrument_percentage_{$instrument}_" . self::YEAR, self::CACHE_TTL, function () use ($instrument) {
            $data = $this->fetchDataFromEndpoints('instrumentUsage', $instrument);
            $totalActivities = $data['totalActivities'];
            $instrumentActivities = $data['instrumentActivities'];
            $activityPercentages = [];
            foreach ($instrumentActivities as $activity => $count) {
                $activityPercentages[$activity] = $count / $totalActivities;
            }

            // Asegurarse de que la suma de los porcentajes sea 1
            $totalPercentage = array_sum($activityPercentages);
            foreach ($activityPercentages as $activity => &$percentage) {
                $percentage = round($percentage / $totalPercentage, 10);
            }
            return [$instrument => $activityPercentages];
        });
    }

    public function getAllMagnetopauseCrossings(): array
    {
        return Cache::remember('all_magnetopause_crossings_' . self::YEAR, self::CACHE_TTL, function () {
            return $this->fetchDataFromEndpoint('MPC');
        });
    }

    private function fetchDataFromEndpoints(string $dataType, string $instrument = ''): array
    {
        $endpoints = ['CME', 'GST', 'IPS', 'FLR', 'SEP', 'MPC', 'RBE', 'HSS'];
        $result = [];
        foreach ($endpoints as $endpoint) {
            $data = $this->fetchDataFromEndpoint($endpoint);
            $result = array_merge($result, $this->extractData($data, $dataType, $instrument));
        }
        return $dataType === 'instruments' || $dataType === 'activityIDs' ? array_unique($result) : $result;
    }

    private function fetchDataFromEndpoint(string $endpoint): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/DONKI/{$endpoint}", [
                    'api_key' => $this->apiKey,
                    'startDate' => self::YEAR . '-01-01',
                    'endDate' => self::YEAR . '-12-31'
                ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error("Error fetching {$endpoint} data for " . self::YEAR . ": " . $e->getMessage());
        }

        return [];
    }

    private function extractData(array $data, string $dataType, string $instrument = ''): array
    {
        $result = ['totalActivities' => 0, 'instrumentActivities' => []];
        foreach ($data as $item) {
            switch ($dataType) {
                case 'instruments':
                    if (isset($item['instruments'])) {
                        foreach ($item['instruments'] as $instrumentData) {
                            $result[] = $instrumentData['displayName'];
                        }
                    }
                    break;
                case 'activityIDs':
                    if (isset($item['activityID'])) {
                        $result[] = $item['activityID'];
                    }
                    break;
                case 'instrumentUsage':
                    $result['totalActivities']++;
                    if (isset($item['instruments'])) {
                        foreach ($item['instruments'] as $instrumentData) {
                            if ($instrumentData['displayName'] === $instrument) {
                                $activityType = $item['activityType'] ?? 'Unknown';
                                if (!isset($result['instrumentActivities'][$activityType])) {
                                    $result['instrumentActivities'][$activityType] = 0;
                                }
                                $result['instrumentActivities'][$activityType]++;
                            }
                        }
                    }
                    break;
            }
        }
        return $result;
    }
}
