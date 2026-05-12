<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('technician_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity_in_hand', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['technician_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('technician_inventory');
    }
};