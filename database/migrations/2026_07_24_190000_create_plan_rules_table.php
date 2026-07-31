<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('term_months');
            $table->string('rule_key', 50);
            $table->json('rule_value')->nullable();
            $table->string('condition', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['plan_id', 'zone_id', 'term_months', 'rule_key', 'condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_rules');
    }
};
