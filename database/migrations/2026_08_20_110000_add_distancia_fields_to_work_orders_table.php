<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('cumple_distancia')->nullable()->after('mufa_has_space')
                ->comment('null=no decidido, 1=si cumple, 0=no cumple distancia');
            $table->decimal('distancia_exceso', 8, 2)->nullable()->after('drop_distance')
                ->comment('Metros que excede la distancia (si no cumple)');
            $table->decimal('precio_por_metro', 10, 2)->nullable()->after('distancia_exceso')
                ->comment('Precio por metro manual (si no cumple distancia)');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['cumple_distancia', 'distancia_exceso', 'precio_por_metro']);
        });
    }
};
