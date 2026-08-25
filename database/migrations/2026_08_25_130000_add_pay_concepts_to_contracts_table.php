<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('pay_install')->default(false)->after('payment_invoice');
            $table->boolean('pay_tv')->default(false)->after('pay_install');
            $table->boolean('pay_abono')->default(false)->after('pay_tv');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['pay_install', 'pay_tv', 'pay_abono']);
        });
    }
};
