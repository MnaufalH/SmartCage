<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SensorData;

class SensorController extends Controller
{
    public function simpan(Request $request)
{
    // Validasi
    $request->validate([
        'suhu' => 'required|numeric',
        'kelembaban' => 'required|numeric',
        'gas' => 'required|numeric', // <--- Tambah validasi
    ]);

    // Simpan
    SensorData::create([
        'suhu' => $request->suhu,
        'kelembaban' => $request->kelembaban,
        'gas' => $request->gas // <--- Simpan gas
    ]);

    return response()->json(['message' => 'Sukses masuk db!']);
}
}