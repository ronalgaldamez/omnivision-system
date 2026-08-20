<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type')->default('extra_tv')
                ->comment('extra_tv, instalacion, mensualidad, otro');
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->boolean('is_recurring')->default(false)
                ->comment('true si se cobra mensualmente (ej. +1 USD TV extra)');
            $table->string('recurring_period')->nullable()->default('monthly');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_charges');
    }
};
