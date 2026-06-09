<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('l1_ended_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('l2_started_at')->nullable();
            $table->timestamp('l2_ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'l1_ended_at',
                'escalated_at',
                'l2_started_at',
                'l2_ended_at',
            ]);
        });
    }
};