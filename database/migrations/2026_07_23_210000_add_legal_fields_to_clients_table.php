<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('nit', 20)->nullable()->after('document_number');
            $table->string('nrc', 20)->nullable()->after('nit');
            $table->date('dui_expedition_date')->nullable()->after('nrc');
            $table->string('dui_expedition_place', 100)->nullable()->after('dui_expedition_date');
            $table->string('nationality', 50)->nullable()->after('dui_expedition_place');
            $table->string('marital_status', 30)->nullable()->after('nationality');
            $table->string('spouse_name', 200)->nullable()->after('marital_status');
            $table->string('occupation', 100)->nullable()->after('spouse_name');
            $table->string('workplace', 200)->nullable()->after('occupation');
            $table->string('position', 100)->nullable()->after('workplace');
            $table->decimal('monthly_income', 10, 2)->nullable()->after('position');
            $table->string('boss_name', 200)->nullable()->after('monthly_income');
            $table->string('work_phone', 20)->nullable()->after('boss_name');
            $table->text('work_address')->nullable()->after('work_phone');
            $table->text('billing_address')->nullable()->after('work_address');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'nit', 'nrc', 'dui_expedition_date', 'dui_expedition_place',
                'nationality', 'marital_status', 'spouse_name',
                'occupation', 'workplace', 'position', 'monthly_income',
                'boss_name', 'work_phone', 'work_address', 'billing_address',
            ]);
        });
    }
};
