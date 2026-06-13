<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('service_type', ['internet', 'cable', 'internet_cable'])->default('internet_cable');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->string('speed')->nullable()->comment('Velocidad para planes de internet');
            $table->integer('channels')->nullable()->comment('Canales para planes de cable');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
