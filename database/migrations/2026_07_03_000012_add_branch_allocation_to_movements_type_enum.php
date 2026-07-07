<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE `movements` CHANGE `type` `type` ENUM(
            'entrada',
            'salida',
            'technician_out',
            'technician_return',
            'requisition_out',
            'return_to_supplier',
            'entry',
            'exit',
            'branch_allocation'
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
            'return_to_supplier',
            'entry',
            'exit'
        ) NOT NULL DEFAULT 'entrada'");
    }
};
