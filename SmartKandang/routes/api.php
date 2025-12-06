<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SensorController;

// Ini jalur khusus untuk ESP32
Route::post('/kirim-data', [SensorController::class, 'simpan']);