<?php

namespace App\Domain\Repositories;

interface DonkiRepositoryInterface
{
    public function getAllInstruments(): array;
    public function getAllActivityIds(): array;
    public function getInstrumentUsagePercentages(): array;
    public function getActivityPercentageByInstrument(string $instrument): array;
}
