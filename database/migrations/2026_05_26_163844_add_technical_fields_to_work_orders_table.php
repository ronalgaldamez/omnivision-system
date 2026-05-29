<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('wifi_name')->nullable()->after('notes');
            $table->string('wifi_password')->nullable()->after('wifi_name');
            $table->string('profile_name')->nullable()->after('wifi_password');
            $table->string('profile_password')->nullable()->after('profile_name');
            $table->string('mac')->nullable()->after('profile_password');
            $table->string('pon')->nullable()->after('mac');
            $table->string('mufa')->nullable()->after('pon');
            $table->date('installation_date')->nullable()->after('mufa');
        });
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'wifi_name',
                'wifi_password',
                'profile_name',
                'profile_password',
                'mac',
                'pon',
                'mufa',
                'installation_date',
            ]);
        });
    }
};