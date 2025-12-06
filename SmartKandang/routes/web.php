<?php

use Illuminate\Support\Facades\Route;
use App\Models\SensorData; // Panggil Model

Route::get('/', function () {
    // Ambil data terbaru dari database
    $data = SensorData::latest()->first();
    
    // Tampilkan file 'dashboard.blade.php'
    return view('dashboard', ['data' => $data]);
});