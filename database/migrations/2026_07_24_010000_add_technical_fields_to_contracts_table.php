<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_type', 30)->nullable()->after('status');
            $table->string('service_contracted', 30)->nullable()->after('contract_type');
            $table->string('access_type', 100)->nullable()->after('service_contracted');
            $table->string('speed', 50)->nullable()->after('access_type');
            $table->string('technology', 100)->nullable()->after('speed');
            $table->string('modem_serial', 100)->nullable()->after('technology');
            $table->integer('term_months')->nullable()->after('modem_serial');
            $table->decimal('installation_cost', 10, 2)->nullable()->after('term_months');
            $table->string('benefit', 200)->nullable()->after('installation_cost');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'contract_type', 'service_contracted', 'access_type', 'speed',
                'technology', 'modem_serial', 'term_months',
                'installation_cost', 'benefit',
            ]);
        });
    }
};
