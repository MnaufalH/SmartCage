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
            // Pakan Schedule
            $table->integer('pakan_schedule_val')->nullable();
            $table->enum('pakan_schedule_unit', ['minute', 'hour', 'day'])->nullable();
            $table->timestamp('last_run_pakan')->nullable();

            // Minum Schedule
            $table->integer('minum_schedule_val')->nullable();
            $table->enum('minum_schedule_unit', ['minute', 'hour', 'day'])->nullable();
            $table->integer('minum_duration')->default(1); // Default 1 menit
            $table->timestamp('last_run_minum')->nullable();

            // Lampu Schedule
            $table->integer('lampu_schedule_val')->nullable();
            $table->enum('lampu_schedule_unit', ['minute', 'hour', 'day'])->nullable();
            $table->integer('lampu_duration')->default(1); // Default 1 menit
            $table->timestamp('last_run_lampu')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_states', function (Blueprint $table) {
            $table->dropColumn([
                'pakan_schedule_val', 'pakan_schedule_unit', 'last_run_pakan',
                'minum_schedule_val', 'minum_schedule_unit', 'minum_duration', 'last_run_minum',
                'lampu_schedule_val', 'lampu_schedule_unit', 'lampu_duration', 'last_run_lampu'
            ]);
        });
    }
};
