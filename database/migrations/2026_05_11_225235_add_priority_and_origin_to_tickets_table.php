<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('service_type'); // P1, P2, P3, P4
            $table->string('origin')->nullable()->after('priority');       // Origen del contacto
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['priority', 'origin']);
        });
    }
};