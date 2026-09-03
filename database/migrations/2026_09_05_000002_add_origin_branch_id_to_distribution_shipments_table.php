<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('distribution_shipments', function (Blueprint $table) {
            $table->foreignId('origin_branch_id')->nullable()->after('branch_id')->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('distribution_shipments', function (Blueprint $table) {
            $table->dropForeign(['origin_branch_id']);
            $table->dropColumn('origin_branch_id');
        });
    }
};
