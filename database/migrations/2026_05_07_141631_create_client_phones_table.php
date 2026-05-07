<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->string('type')->nullable(); // personal, casa, referencia, trabajo, otro
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_phones');
    }
};