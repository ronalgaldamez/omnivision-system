<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->integer('extra_tvs')->default(0)->after('term_months')
                ->comment('Cantidad de TVs extra instaladas (equipo adicional para otra pantalla)');
            $table->decimal('tv_install_fee', 10, 2)->default(0)->after('extra_tvs')
                ->comment('Cargo único de instalación por TVs extra (6 USD por TV)');
            $table->decimal('monthly_extra_fee', 10, 2)->default(0)->after('tv_install_fee')
                ->comment('Recargo mensual recurrente por TVs extra (1 USD por TV)');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['extra_tvs', 'tv_install_fee', 'monthly_extra_fee']);
        });
    }
};
