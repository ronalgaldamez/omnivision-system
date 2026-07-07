<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packaging_types', function (Blueprint $table) {
            $table->string('unit_of_measure', 20)->default('unidad')->after('name');
        });

        DB::table('packaging_types')->whereIn('name', ['Rollo', 'Bobina'])->update(['unit_of_measure' => 'm']);
    }

    public function down(): void
    {
        Schema::table('packaging_types', function (Blueprint $table) {
            $table->dropColumn('unit_of_measure');
        });
    }
};
