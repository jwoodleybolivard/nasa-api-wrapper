<?php

namespace App\Models\Repositories;

use App\Domain\Repositories\DonkiRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DonkiRepository implements DonkiRepositoryInterface
{
    private string $baseUrl;
    private string $apiKey;
    private const TIMEOUT = 60;
    private const YEAR = 2023;

    public function __construct()
    {
        $this->baseUrl = config('nasa.base_url');
        $this->apiKey = config('nasa.api_key');
    }

    public function getAllInstruments(): array
    {
        $instruments = $this->fetchDataFromEndpoints('instruments');
        return array_unique($instruments);
    }

    public function getAllActivityIds(): array
    {
        $activityIds = $this->fetchDataFromEndpoints('activityIDs');
        return array_unique($activityIds);
    }

    public function getInstrumentUsagePercentages(): array
    {
        $instruments = $this->getAllInstruments();
        $usageCounts = [];

        foreach ($instruments as $instrument) {
            $usageCounts[$instrument] = 0;
        }

        $totalActivities = 0;

        foreach ($instruments as $instrument) {
            $percentageData = $this->getActivityPercentageByInstrument($instrument);
            if (isset($percentageData[$instrument]) && is_array($percentageData[$instrument])) {
                $usageCounts[$instrument] += array_sum($percentageData[$instrument]);
                $totalActivities += array_sum($percentageData[$instrument]);
            }
        }

        $percentages = [];
        foreach ($usageCounts as $instrument => $count) {
            if ($count > 0) {
                $percentages[$instrument] = $totalActivities > 0 ? $count / $totalActivities : 0;
            }
        }

        foreach ($percentages as $instrument => &$percentage) {
            $percentage = round($percentage, 1);
        }

        $totalPercentage = array_sum($percentages);
        if ($totalPercentage > 0) {
            $diff = 1 - $totalPercentage;
            if ($diff !== 0) {
                foreach ($percentages as &$percentage) {
                    $percentage += $diff;
                    break;
                }
            }
        }

        return $percentages;
    }


    public function getActivityPercentageByInstrument(string $instrument): array
    {
        $data = $this->fetchDataFromEndpoints('instrumentUsage', $instrument);
        $totalActivities = $data['totalActivities'] ?? 0;
        $instrumentActivities = $data['instrumentActivities'] ?? [];

        $activityPercentages = [];
        foreach ($instrumentActivities as $activityID => $count) {
            if ($activityID !== 'Unknown') {
                $activityPercentages[$activityID] = $totalActivities > 0 ? $count / $totalActivities : 0;
            }
        }

        $totalPercentage = array_sum($activityPercentages);
        foreach ($activityPercentages as $activityID => &$percentage) {
            $percentage = round($percentage, 1);
        }

        if ($totalPercentage > 0) {
            $diff = 1 - array_sum($activityPercentages);
            if ($diff !== 0) {
                foreach ($activityPercentages as $activityID => &$percentage) {
                    $percentage += $diff;
                    break;
                }
            }
        }

        return [$instrument => $activityPercentages];
    }

    private function fetchDataFromEndpoints(string $dataType, string $instrument = ''): array
    {
        $endpoints = ['CME', 'GST', 'IPS', 'FLR', 'SEP', 'MPC', 'RBE', 'HSS'];
        $result = [];
        foreach ($endpoints as $endpoint) {
            $data = $this->fetchDataFromEndpoint($endpoint);
            $extractedData = $this->extractData($data, $dataType, $instrument);
            if (is_array($extractedData)) {
                $result = array_merge($result, $extractedData);
            }
        }

        if ($dataType === 'instruments' || $dataType === 'activityIDs') {
            $result = array_filter($result, function ($item) {
                return !is_array($item) && !is_numeric($item);
            });
            return array_unique($result);
        }

        return $result;
    }


    private function fetchDataFromEndpoint(string $endpoint): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->retry(3, 1000)
                ->get("{$this->baseUrl}/DONKI/{$endpoint}", [
                    'api_key' => $this->apiKey,
                    'startDate' => self::YEAR . '-01-01',
                    'endDate' => self::YEAR . '-01-31'
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
                            if (!empty($instrumentData['displayName'])) {
                                $result[] = $instrumentData['displayName'];
                            }
                        }
                    }
                    break;
                case 'activityIDs':
                    if (isset($item['activityID'])) {
                        $result[] = $item['activityID'];
                    }
                    break;
                case 'instrumentUsage':
                    if (isset($item['instruments'])) {
                        foreach ($item['instruments'] as $instrumentData) {
                            if ($instrumentData['displayName'] === $instrument) {
                                $result['totalActivities']++;
                                if (isset($item['linkedEvents'])) {
                                    foreach ($item['linkedEvents'] as $linkedEvent) {
                                        $activityIDParts = explode("-", $linkedEvent['activityID']);
                                        $activityID = implode("-", array_slice($activityIDParts, -2));
                                        if (!isset($result['instrumentActivities'][$activityID])) {
                                            $result['instrumentActivities'][$activityID] = 0;
                                        }
                                        $result['instrumentActivities'][$activityID]++;
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
    }
}
