<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete()->after('service_type');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete()->after('zone_id');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('zone_id');
            $table->dropConstrainedForeignId('plan_id');
        });
    }
};
