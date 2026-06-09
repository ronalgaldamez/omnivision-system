<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // OT — desde que NOC crea la OT hasta que atención al cliente confirma
            $table->timestamp('ot_started_at')->nullable()->after('completed_date');   // cuando se crea la OT (equivale a created_at pero explícito)
            $table->timestamp('ot_ended_at')->nullable()->after('ot_started_at');      // cuando atención al cliente confirma solución

            // Field Supervisor — solo registro, sin cronómetro propio
            $table->unsignedBigInteger('assigned_by')->nullable()->after('ot_ended_at'); // quién asignó (field_supervisor_id)
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');           // cuándo se asignó al técnico

            // Técnico en campo
            $table->timestamp('tech_started_at')->nullable()->after('assigned_at');      // cuando técnico acepta e inicia
            $table->timestamp('tech_ended_at')->nullable()->after('tech_started_at');    // cuando técnico termina en campo

            // Llave foránea del field supervisor
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn([
                'ot_started_at',
                'ot_ended_at',
                'assigned_by',
                'assigned_at',
                'tech_started_at',
                'tech_ended_at',
            ]);
        });
    }
};