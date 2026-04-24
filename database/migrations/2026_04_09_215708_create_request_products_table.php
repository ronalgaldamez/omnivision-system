<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('request_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_request_id')->constrained('technician_requests')->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity_requested');
            $table->integer('quantity_delivered')->default(0);
            $table->integer('quantity_returned')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_products');
    }
};