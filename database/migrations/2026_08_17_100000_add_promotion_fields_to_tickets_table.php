<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('promotion_status')->nullable()->after('contracts_ended_at');
            $table->timestamp('promoted_at')->nullable()->after('promotion_status');
            $table->text('rejection_reason')->nullable()->after('promoted_at');
            $table->decimal('contract_price_snapshot', 10, 2)->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['promotion_status', 'promoted_at', 'rejection_reason', 'contract_price_snapshot']);
        });
    }
};
