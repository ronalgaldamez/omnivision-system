<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('plan_group_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['plan_group_id', 'plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_group_plan');
        Schema::dropIfExists('plan_groups');
    }
};
