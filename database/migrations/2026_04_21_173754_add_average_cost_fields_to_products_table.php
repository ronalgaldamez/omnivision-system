<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('average_cost', 12, 4)->nullable()->after('current_stock');
            $table->decimal('total_value', 12, 2)->nullable()->after('average_cost');
            $table->boolean('is_obsolete')->default(false)->after('total_value');
            $table->boolean('is_floating')->default(false)->after('is_obsolete');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['average_cost', 'total_value', 'is_obsolete', 'is_floating']);
        });
    }
};