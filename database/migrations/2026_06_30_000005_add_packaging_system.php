<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('base_unit')->nullable()->after('stock_min')->comment('Unidad base: metro, unidad, pieza');
        });

        Schema::create('product_packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('quantity_in_base_unit', 12, 4);
            $table->boolean('is_default_for_purchase')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_packagings');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('base_unit');
        });
    }
};
