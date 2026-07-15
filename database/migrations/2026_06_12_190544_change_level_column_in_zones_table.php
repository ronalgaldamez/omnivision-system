<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE zones MODIFY COLUMN level VARCHAR(50) NOT NULL DEFAULT 'localidad'");
        }
    }

    public function down()
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE zones MODIFY COLUMN level ENUM('departamento','municipio','localidad') NOT NULL DEFAULT 'localidad'");
        }
    }
};
