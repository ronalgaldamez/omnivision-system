<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Obtener los valores actuales del ENUM y agregar 'entry' si no existe
        DB::statement("ALTER TABLE `movements` CHANGE `type` `type` ENUM(
            'entrada',
            'salida',
            'technician_out',
            'technician_return',
            'requisition_out',
            'return_to_supplier',
            'entry',
            'exit'
        ) NOT NULL DEFAULT 'entrada'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `movements` CHANGE `type` `type` ENUM(
            'entrada',
            'salida',
            'technician_out',
            'technician_return',
            'requisition_out',
            'return_to_supplier'
        ) NOT NULL DEFAULT 'entrada'");
    }
};