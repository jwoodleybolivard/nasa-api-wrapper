<?php

namespace App\Http\Controllers;

use App\Domain\Repositories\DonkiRepositoryInterface;
use Illuminate\Http\Request;

class DonkiController extends Controller
{
    private $donkiRepository;

    public function __construct(DonkiRepositoryInterface $donkiRepository)
    {
        $this->donkiRepository = $donkiRepository;
    }

    public function getAllInstruments()
    {
        $instruments = $this->donkiRepository->getAllInstruments();
        $instruments = array_values($instruments);
        return response()->json(['instruments' => $instruments]);
    }

    public function getAllActivityIds()
    {
        $activityIds = $this->donkiRepository->getAllActivityIds();
        $activityIds = array_map(function ($id) {
            // Usar expresión regular para eliminar la parte de la fecha y hora
            return preg_replace('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}-/', '', $id);
        }, $activityIds);
        $activityIds = array_values(array_unique($activityIds));
        return response()->json(['activityIds' => $activityIds]);
    }


    public function getInstrumentUsagePercentages()
    {
        $percentages = $this->donkiRepository->getInstrumentUsagePercentages();
        return response()->json($percentages);
    }

    public function getActivityPercentageByInstrument(Request $request)
    {
        $instrument = $request->input('instrument');
        $activityData = $this->donkiRepository->getActivityPercentageByInstrument($instrument);
        return response()->json(['instrument_activity' => $activityData]);
    }
}
