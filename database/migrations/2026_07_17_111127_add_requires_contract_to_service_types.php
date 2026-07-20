<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->boolean('requires_contract')->default(false)->after('requires_noc');
        });
    }

    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('requires_contract');
        });
    }
};
