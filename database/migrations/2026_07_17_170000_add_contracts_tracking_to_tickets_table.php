<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('contracts_escalated_at')->nullable()->after('escalated_at');
            $table->timestamp('contracts_started_at')->nullable()->after('contracts_escalated_at');
            $table->timestamp('contracts_ended_at')->nullable()->after('contracts_started_at');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['contracts_escalated_at', 'contracts_started_at', 'contracts_ended_at']);
        });
    }
};
