<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('nrc')->nullable()->after('address');
            $table->string('nit')->nullable()->after('nrc');
            $table->json('bank_accounts')->nullable()->after('nit'); // almacenar array de cuentas
        });
    }

    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['nrc', 'nit', 'bank_accounts']);
        });
    }
};