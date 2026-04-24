<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('sku')->constrained('brands')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->after('brand_id')->constrained('product_models')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('model_id')->constrained('categories')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('model_id');
            $table->dropConstrainedForeignId('category_id');
        });
    }
};