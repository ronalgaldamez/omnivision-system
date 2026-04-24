<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'client_phone', 'client_address']);
        });
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->text('client_address')->nullable();
        });
    }
};