<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('zone_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2)->nullable()->comment('Null = hereda del padre o precio base');
            $table->timestamps();

            $table->unique(['zone_id', 'plan_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('zone_plan_prices');
    }
};
