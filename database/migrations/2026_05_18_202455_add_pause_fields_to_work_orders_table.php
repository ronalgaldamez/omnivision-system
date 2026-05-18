<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Añadir la columna accumulated_seconds
        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedInteger('accumulated_seconds')->default(0)->after('completed_date');
        });

        // Modificar el ENUM para incluir 'paused'
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM('pending', 'in_progress', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        // Revertir el ENUM
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('accumulated_seconds');
        });
    }
};