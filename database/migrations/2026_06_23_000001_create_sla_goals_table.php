<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_goals', function (Blueprint $table) {
            $table->id();
            $table->string('priority'); // P1, P2, P3, P4
            $table->foreignId('service_type_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('minutes'); // Tiempo objetivo en minutos
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_goals');
    }
};
