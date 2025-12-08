<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\ControlController;

// Ini jalur khusus untuk ESP32
Route::post('/kirim-data', [SensorController::class, 'simpan']);

Route::post('/control/update', [ControlController::class, 'update']);
Route::get('/control/status', [ControlController::class, 'getStatus']);