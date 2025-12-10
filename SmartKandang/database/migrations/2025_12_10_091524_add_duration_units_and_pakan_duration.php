<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('control_states', function (Blueprint $table) {
            // Pakan Duration
            $table->integer('pakan_duration')->default(1); // Default 1 detik/menit
            
            // Duration Units (Second/Minute)
            $table->enum('pakan_duration_unit', ['second', 'minute'])->default('minute');
            $table->enum('minum_duration_unit', ['second', 'minute'])->default('minute');
            $table->enum('lampu_duration_unit', ['second', 'minute'])->default('minute');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_states', function (Blueprint $table) {
            $table->dropColumn([
                'pakan_duration', 
                'pakan_duration_unit', 
                'minum_duration_unit', 
                'lampu_duration_unit'
            ]);
        });
    }
};
