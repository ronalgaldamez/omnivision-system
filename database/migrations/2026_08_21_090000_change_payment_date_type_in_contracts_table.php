<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->change()
                ->comment('Próxima fecha de pago del cliente (fecha real, ej. 2026-09-15). El día se usa para abono y moras.');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('payment_date', 255)->nullable()->change();
        });
    }
};
