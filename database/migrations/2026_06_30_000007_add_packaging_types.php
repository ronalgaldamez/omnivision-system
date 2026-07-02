<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('product_packagings', function (Blueprint $table) {
            $table->foreignId('packaging_type_id')->nullable()->after('product_id')->constrained('packaging_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_packagings', function (Blueprint $table) {
            $table->dropForeign(['packaging_type_id']);
            $table->dropColumn('packaging_type_id');
        });
        Schema::dropIfExists('packaging_types');
    }
};
