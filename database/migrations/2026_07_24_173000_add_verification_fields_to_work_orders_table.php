<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('mufa_has_space')->nullable()->after('installation_date');
            $table->decimal('drop_distance', 8, 2)->nullable()->after('mufa_has_space');
            $table->decimal('verification_price', 10, 2)->nullable()->after('drop_distance');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['mufa_has_space', 'drop_distance', 'verification_price']);
        });
    }
};
