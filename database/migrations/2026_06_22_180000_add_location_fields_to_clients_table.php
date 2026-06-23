<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('departamento')->nullable()->after('installation_address');
            $table->string('municipio')->nullable()->after('departamento');
            $table->string('distrito')->nullable()->after('municipio');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['departamento', 'municipio', 'distrito']);
        });
    }
};
