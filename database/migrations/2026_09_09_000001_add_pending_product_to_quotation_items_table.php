<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            // El producto puede no existir todavía (propuesto desde la cotización)
            $table->foreignId('product_id')->nullable()->change();
            // Datos del producto propuesto (se materializa al recibir)
            $table->string('pending_name')->nullable()->after('product_id');
            $table->string('pending_unit')->nullable()->after('pending_name');
            $table->foreignId('pending_category_id')->nullable()->after('pending_unit')->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Los ítems con product_id NULL son productos propuestos por esta feature:
        // se eliminan para poder restaurar la restricción NOT NULL original.
        DB::table('quotation_items')->whereNull('product_id')->delete();

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['pending_category_id']);
            $table->dropColumn(['pending_name', 'pending_unit', 'pending_category_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
