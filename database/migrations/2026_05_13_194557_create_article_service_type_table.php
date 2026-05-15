<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('article_service_type', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('knowledge_base_articles')->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained('service_types')->cascadeOnDelete();
            $table->primary(['article_id', 'service_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('article_service_type');
    }
};