<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\DonkiRepositoryInterface;
use Illuminate\Http\Request;

class DonkiController extends Controller
{
    private DonkiRepositoryInterface $donkiRepository;

    public function __construct(DonkiRepositoryInterface $donkiRepository)
    {
        $this->donkiRepository = $donkiRepository;
    }

    public function getAllInstruments()
    {
        $instruments = $this->donkiRepository->getAllInstruments();
        return response()->json(['instruments' => array_values($instruments)]);
    }

    public function getAllActivityIds()
    {
        $activityIds = $this->donkiRepository->getAllActivityIds();
        $activityIds = array_map(function ($id) {
            return preg_replace('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}-/', '', $id);
        }, $activityIds);
        return response()->json(['activityIds' => array_values(array_unique($activityIds))]);
    }

    public function getInstrumentUsagePercentages()
    {
        $percentages = $this->donkiRepository->getInstrumentUsagePercentages();
        return response()->json(['instruments_use' => $percentages]);
    }

    public function getActivityPercentageByInstrument(Request $request)
    {
        $instrument = $request->input('instrument');
        $percentages = $this->donkiRepository->getActivityPercentageByInstrument($instrument);
        return response()->json(['instrument_activity' => $percentages]);
    }
}
