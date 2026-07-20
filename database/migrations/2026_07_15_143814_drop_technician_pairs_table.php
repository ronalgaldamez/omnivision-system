<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('technician_pairs');
    }

    public function down(): void
    {
        Schema::create('technician_pairs', function ($table) {
            $table->id();
            $table->foreignId('primary_technician_id')->constrained('users');
            $table->foreignId('assistant_technician_id')->nullable()->constrained('users');
            $table->string('vehicle_plate', 20)->nullable();
            $table->string('vehicle_brand', 100)->nullable();
            $table->string('vehicle_model', 100)->nullable();
            $table->string('vehicle_color', 50)->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->boolean('is_active')->default(true);
            $table->date('assigned_at');
            $table->date('ended_at')->nullable();
            $table->timestamps();
        });
    }
};
