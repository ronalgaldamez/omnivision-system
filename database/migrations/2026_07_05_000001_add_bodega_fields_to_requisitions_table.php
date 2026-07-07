<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('technician_id')->constrained('branches');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users');
            $table->dateTime('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });

        DB::statement("ALTER TABLE requisitions MODIFY COLUMN status ENUM('open', 'heredada', 'closed', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE requisitions MODIFY COLUMN status ENUM('open', 'heredada', 'closed') NOT NULL DEFAULT 'open'");

        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['branch_id', 'approved_by', 'approved_at', 'rejection_reason']);
        });
    }
};
