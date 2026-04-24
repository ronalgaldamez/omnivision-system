<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Añadir client_id (nullable por si se necesita migrar datos antiguos)
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->nullOnDelete();
            // Hacer technician_id nullable
            $table->foreignId('technician_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->foreignId('technician_id')->nullable(false)->change();
        });
    }
};