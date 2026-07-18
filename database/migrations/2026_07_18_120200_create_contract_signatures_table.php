<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // client, witness, sales_rep
            $table->text('signature_data'); // base64 o path a imagen PNG
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('signature_token', 64)->nullable()->unique();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_signatures');
    }
};
