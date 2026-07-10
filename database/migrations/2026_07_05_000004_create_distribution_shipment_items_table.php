<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('distribution_shipments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->string('product_name', 255);
            $table->decimal('quantity', 12, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_shipment_items');
    }
};
