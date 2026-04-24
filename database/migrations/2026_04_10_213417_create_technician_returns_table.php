<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('technician_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_request_id')->nullable()->constrained('technician_requests')->nullOnDelete();
            $table->enum('type', ['surplus', 'damage']);
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_returns');
    }
};