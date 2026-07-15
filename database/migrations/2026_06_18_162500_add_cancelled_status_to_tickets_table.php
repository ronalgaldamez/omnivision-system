<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `tickets` MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'resolved', 'closed', 'open', 'cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending'");
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('resolved_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancellation_reason']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `tickets` MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'resolved', 'closed', 'open') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        }
    }
};
