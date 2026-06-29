<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('current_stock', 12, 4)->default(0)->change();
            $table->decimal('stock_min', 12, 4)->default(0)->change();
            $table->decimal('stock_max', 12, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->change();
            $table->integer('stock_min')->default(0)->change();
            $table->integer('stock_max')->nullable()->change();
        });
    }
};
