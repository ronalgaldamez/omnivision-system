<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encargado_id')->constrained('users');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehiculos');
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->boolean('is_active')->default(true);
            $table->date('assigned_at');
            $table->date('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
