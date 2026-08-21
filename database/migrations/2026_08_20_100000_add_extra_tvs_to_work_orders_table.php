<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->integer('extra_tvs')->default(0)->after('customer_accepts_cost')
                ->comment('TVs extra capturados en verificación (precarga opcional para el contrato)');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('extra_tvs');
        });
    }
};
