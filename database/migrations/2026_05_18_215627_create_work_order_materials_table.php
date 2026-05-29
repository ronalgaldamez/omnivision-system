<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('work_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity_used', 15, 2);
            $table->foreignId('requisition_item_id')->nullable()->constrained('requisition_items')->nullOnDelete();
            $table->text('notes')->nullable(); // motivo de modificación
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_order_materials');
    }
};