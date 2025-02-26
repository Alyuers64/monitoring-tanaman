<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorLog;

class DashboardController extends Controller
{
    public function index()
    {
        $logs = SensorLog::orderBy('created_at', 'desc')->take(10)->get()->reverse();

        $labels = $logs->pluck('created_at')->map(fn($date) => $date->format('H:i'));

        $suhuData = $logs->pluck('suhu');
        $kelembapanData = $logs->pluck('kelembapan');

        $latestLog = $logs->last();
        $connectedDevices = 5; //dummy ya ges

        return view('dashboard', compact('labels', 'suhuData', 'kelembapanData', 'latestLog', 'connectedDevices'));
    }
}