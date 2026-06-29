<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('packaging_id')->nullable()->after('unit_cost')->constrained('product_packagings')->nullOnDelete();
            $table->decimal('base_quantity', 12, 4)->nullable()->after('packaging_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['packaging_id']);
            $table->dropColumn(['packaging_id', 'base_quantity']);
        });
    }
};
