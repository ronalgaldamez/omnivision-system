<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->boolean('apply_plazo')->default(true)->after('contract_type');
            $table->boolean('promo_enabled_free')->default(true)->after('apply_plazo');
            $table->boolean('promo_enabled_double')->default(true)->after('promo_enabled_free');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['apply_plazo', 'promo_enabled_free', 'promo_enabled_double']);
        });
    }
};
