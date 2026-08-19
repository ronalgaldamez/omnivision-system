<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('payment_date', 255)->nullable()->after('contract_date')
                ->comment('Fecha de pago definida por la sucursal, la llena el técnico en campo (ej. cada 15 de cada mes)');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });
    }
};
