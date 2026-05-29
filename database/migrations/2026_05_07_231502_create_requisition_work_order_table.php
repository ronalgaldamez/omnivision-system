<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requisition_work_order', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requisition_work_order');
    }
};