<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('technician_returns', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable()->after('technician_request_id')->constrained('work_orders')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('technician_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_order_id');
        });
    }
};