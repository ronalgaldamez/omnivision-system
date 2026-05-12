<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Nuevos valores que deseamos
        $nuevos = "'entrada','salida','technician_out','technician_return','requisition_out','return_to_supplier'";

        // 1. Lista de tipos originales que sí estaban antes
        $originales = "'entrada','salida','technician_out','technician_return'";

        // 2. Actualizar cualquier fila cuyo type no esté en la lista original ni en la nueva
        DB::statement("UPDATE `movements` SET `type` = 'entrada' WHERE `type` NOT IN ($originales)");

        // 3. Ahora sí modificar el ENUM
        DB::statement("ALTER TABLE `movements` CHANGE `type` `type` ENUM($nuevos) NOT NULL DEFAULT 'entrada'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `movements` CHANGE `type` `type` ENUM(
            'entrada',
            'salida',
            'technician_out',
            'technician_return'
        ) NOT NULL DEFAULT 'entrada'");
    }
};