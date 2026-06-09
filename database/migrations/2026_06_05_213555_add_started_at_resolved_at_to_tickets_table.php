<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('requires_noc');
            }
            if (!Schema::hasColumn('tickets', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'started_at')) {
                $table->dropColumn('started_at');
            }
            if (Schema::hasColumn('tickets', 'resolved_at')) {
                $table->dropColumn('resolved_at');
            }
        });
    }
};