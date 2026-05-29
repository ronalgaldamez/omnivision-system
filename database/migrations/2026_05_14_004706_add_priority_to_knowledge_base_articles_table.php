<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('knowledge_base_articles', function (Blueprint $table) {
            $table->string('priority', 4)->nullable()->after('content'); // P1, P2, P3, P4
        });
    }

    public function down()
    {
        Schema::table('knowledge_base_articles', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};