<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('document_type', ['dui', 'cedula', 'ruc', 'pasaporte'])->nullable()->after('phone');
            $table->string('document_number')->nullable()->after('document_type');
            $table->string('email')->nullable()->after('document_number');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('nro_luz')->nullable()->after('longitude');
            $table->text('installation_address')->nullable()->after('nro_luz');
            $table->text('notes')->nullable()->after('service');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'document_type',
                'document_number',
                'email',
                'latitude',
                'longitude',
                'nro_luz',
                'installation_address',
                'notes',
            ]);
        });
    }
};