<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->json('contact_channels')->nullable()->after('email')
                ->comment('Canales para envío de facturas/contrato: ["email"], ["whatsapp"], ambos o null');
        });

        // Migrar datos existentes de contact_preference
        $rows = DB::table('clients')->whereIn('contact_preference', ['email', 'whatsapp'])->get();
        foreach ($rows as $row) {
            DB::table('clients')->where('id', $row->id)->update([
                'contact_channels' => json_encode([$row->contact_preference]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('contact_channels');
        });
    }
};
