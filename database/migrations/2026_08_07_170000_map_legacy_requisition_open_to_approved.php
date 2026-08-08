<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normaliza el estado legado 'open' de requisiciones al estado real 'approved'
     * (el flujo actual crea 'pending' y bodega aprueba a 'approved'; 'open' nunca se asigna).
     */
    public function up(): void
    {
        DB::table('requisitions')
            ->where('status', 'open')
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        // No reversible: no hay forma de distinguir legado 'open' de 'approved' reales.
    }
};
