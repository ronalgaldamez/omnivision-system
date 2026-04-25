<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->text('description')->nullable()->after('notes');
            $table->string('service_type')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['description', 'service_type']);
        });
    }
};