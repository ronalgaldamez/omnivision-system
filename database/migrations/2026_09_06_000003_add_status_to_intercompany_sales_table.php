<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('intercompany_sales', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('total');
            $table->timestamp('in_transit_at')->nullable()->after('status');
            $table->timestamp('delivered_at')->nullable()->after('in_transit_at');
            $table->timestamp('confirmed_at')->nullable()->after('delivered_at');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intercompany_sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn(['status', 'in_transit_at', 'delivered_at', 'confirmed_at']);
        });
    }
};
