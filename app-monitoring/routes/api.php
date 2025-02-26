<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RelayController;
use App\Models\SensorLog;

Route::post('/sensor-data', function (Request $request) {
    $request->validate([
        'suhu' => 'required|numeric',
        'kelembapan' => 'required|numeric'
    ]);

    $log = SensorLog::create([
        'suhu' => $request->suhu,
        'kelembapan' => $request->kelembapan
    ]);

    return response()->json(['success' => true, 'data' => $log]);
});

Route::get('/sensor-data', function () {
    $logs = SensorLog::latest()->limit(10)->get();

    return response()->json([
        'labels' => $logs->pluck('created_at')->map(fn($time) => $time->format('H:i')),
        'suhuData' => $logs->pluck('suhu'),
        'kelembapanData' => $logs->pluck('kelembapan')
    ]);
});

Route::get('/relay-status', [RelayController::class, 'getRelayStatus']);

Route::post('/relay-status', [RelayController::class, 'toggleRelay']);