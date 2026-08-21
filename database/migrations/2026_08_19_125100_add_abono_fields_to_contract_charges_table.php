<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_charges', function (Blueprint $table) {
            $table->string('charge_type')->default('abono')->after('type')
                ->comment('abono = prorrateo por días, cuota = monto completo mensual');
            $table->decimal('base_amount', 10, 2)->nullable()->after('amount')
                ->comment('Cuota mensual base (sin abonar) que originó el cargo');
            $table->integer('days')->nullable()->after('quantity')
                ->comment('Días de servicio considerados en el abono');
            $table->timestamp('applied_at')->nullable()->after('created_at')
                ->comment('Fecha en que se registró el pago');
        });
    }

    public function down(): void
    {
        Schema::table('contract_charges', function (Blueprint $table) {
            $table->dropColumn(['charge_type', 'base_amount', 'days', 'applied_at']);
        });
    }
};
