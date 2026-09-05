<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Un borrador puede guardarse sin proveedor definido todavía.
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Restaurar NOT NULL requiere que no queden borradores sin proveedor.
        $ids = DB::table('quotations')->where('status', 'draft')->whereNull('supplier_id')->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('quotation_items')->whereIn('quotation_id', $ids)->delete();
            DB::table('quotations')->whereIn('id', $ids)->delete();
        }

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable(false)->change();
        });
    }
};
