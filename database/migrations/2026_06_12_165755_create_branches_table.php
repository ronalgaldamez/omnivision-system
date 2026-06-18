<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('branches')->insert([
            ['name' => 'Casa Matriz Chalatenango', 'code' => 'MATRIZ', 'address' => 'Chalatenango', 'phone' => null],
            ['name' => 'Sucursal Concepción Quezaltepeque', 'code' => 'CQ', 'address' => null, 'phone' => null],
            ['name' => 'Sucursal Amayo', 'code' => 'AMAYO', 'address' => null, 'phone' => null],
            ['name' => 'Sucursal Aguilares', 'code' => 'AGUILARES', 'address' => null, 'phone' => null],
            ['name' => 'Sucursal La Palma', 'code' => 'PALMA', 'address' => null, 'phone' => null],
            ['name' => 'Sucursal San Pablo Tacachico', 'code' => 'SMP', 'address' => null, 'phone' => null],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('branches');
    }
};
