<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('customer_paid')->nullable()->after('promo_enabled_double')
                ->comment('1=pagó, 0=no pagó, null=sin definir');
            $table->string('payment_place', 30)->nullable()->after('customer_paid')
                ->comment('oficina=paga en oficina, instalacion=paga el día de instalación');
            $table->string('payment_invoice', 50)->nullable()->after('payment_place')
                ->comment('N° de factura si ya pagó');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['customer_paid', 'payment_place', 'payment_invoice']);
        });
    }
};
