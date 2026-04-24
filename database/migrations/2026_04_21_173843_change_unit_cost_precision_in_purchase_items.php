<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 4)->change();
        });
    }

    public function down()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->change();
        });
    }
};