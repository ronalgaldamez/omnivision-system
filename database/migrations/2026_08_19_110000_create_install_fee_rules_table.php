<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('install_fee_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained()->onDelete('cascade')
                ->comment('Zona a la que aplica la tarifa. null = global/fallback');
            $table->string('service_type')->default('internet')
                ->comment('internet, cable, combo');
            $table->integer('covered_meters')->default(150)
                ->comment('Metros que cubre el cargo base (ej. 150)');
            $table->decimal('fee', 10, 2)->default(25)
                ->comment('Costo base de instalación (ej. 25)');
            $table->decimal('excess_per_50m', 10, 2)->default(5)
                ->comment('Recargo por cada 50m adicionales que exceda covered_meters');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['zone_id', 'service_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('install_fee_rules');
    }
};
