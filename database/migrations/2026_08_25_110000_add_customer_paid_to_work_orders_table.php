<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('customer_paid')->nullable()->after('customer_accepts_cost')
                ->comment('1=el cliente pagó, 0=no pagó, null=sin definir');
            $table->string('payment_place', 30)->nullable()->after('customer_paid')
                ->comment('oficina=pagó en oficina, instalacion=pagará el día de instalación');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['customer_paid', 'payment_place']);
        });
    }
};
