<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intercompany_sales', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('seller_branch_id')->constrained('branches');
            $table->foreignId('buyer_branch_id')->constrained('branches');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intercompany_sales');
    }
};
