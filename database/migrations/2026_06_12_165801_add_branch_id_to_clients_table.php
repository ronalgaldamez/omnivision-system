<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->after('service');
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete()->after('branch_id');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete()->after('zone_id');
            $table->date('contract_date')->nullable()->after('plan_id');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('zone_id');
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('contract_date');
        });
    }
};
