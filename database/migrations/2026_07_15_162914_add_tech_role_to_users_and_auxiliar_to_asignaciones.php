<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tech_role')->nullable()->after('branch_id');
        });

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->foreignId('auxiliar_id')->nullable()->after('encargado_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tech_role');
        });

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropForeign(['auxiliar_id']);
            $table->dropColumn('auxiliar_id');
        });
    }
};
