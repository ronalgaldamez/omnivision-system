<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('sla_goal_id')->nullable()->constrained()->nullOnDelete()->after('plan_id');
            $table->timestamp('sla_deadline_at')->nullable()->after('sla_goal_id');
            $table->boolean('sla_met')->nullable()->after('sla_deadline_at');
            $table->timestamp('sla_evaluated_at')->nullable()->after('sla_met');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sla_goal_id');
            $table->dropColumn(['sla_deadline_at', 'sla_met', 'sla_evaluated_at']);
        });
    }
};
