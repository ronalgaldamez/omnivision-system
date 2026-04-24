<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 4)->change(); // cambiar tipo
            $table->decimal('total_value', 12, 2)->nullable()->after('unit_cost');
        });
    }

    public function down()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->change();
            $table->dropColumn('total_value');
        });
    }
};