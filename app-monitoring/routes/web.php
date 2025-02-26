<?php

use Illuminate\Support\Facades\Route;
use App\Models\SensorLog;
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');