<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ...
    public function up(): void
    {
        Schema::create('control_states', function (Blueprint $table) {
            $table->id();
            $table->string('mode')->default('AUTO'); // AUTO atau MANUAL
            $table->boolean('lampu')->default(false);
            $table->boolean('minum')->default(false); // Dinamo Air
            $table->boolean('pakan')->default(false); // Pemicu Servo
            $table->timestamps();
        });
    }
// ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_states');
    }
};
