<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControlController extends Controller
{
    // 1. Dipanggil oleh JS (Update Status)
    public function update(Request $request)
    {
        // Ambil data pertama, kalau kosong buat baru
        $state = DB::table('control_states')->first();
        if (!$state) {
            DB::table('control_states')->insert(['created_at' => now()]);
            $state = DB::table('control_states')->first();
        }

        // Update hanya field yang dikirim
        DB::table('control_states')->where('id', $state->id)->update($request->all());

        return response()->json(['status' => 'success']);
    }

    // 2. Dipanggil oleh ESP32 (Baca Status) & JS (Sync Tampilan)
    public function getStatus()
    {
        $state = DB::table('control_states')->first();
        // Pakan kita reset ke 0 setelah dibaca supaya tidak muter terus (One-shot)
        if ($state && $state->pakan == 1) {
             DB::table('control_states')->where('id', $state->id)->update(['pakan' => 0]);
        }
        return response()->json($state);
    }
}