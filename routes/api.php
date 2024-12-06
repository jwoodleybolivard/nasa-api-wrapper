<?php

use App\Http\Controllers\DonkiController;
use Illuminate\Support\Facades\Route;

Route::get('/instruments', [DonkiController::class, 'getAllInstruments']);
Route::get('/activity-ids', [DonkiController::class, 'getAllActivityIds']);
Route::get('/instrument-usage-percentages', [DonkiController::class, 'getInstrumentUsagePercentages']);
Route::post('/instrument-activity-percentage', [DonkiController::class, 'getActivityPercentageByInstrument']);
