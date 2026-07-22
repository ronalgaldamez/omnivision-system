<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('docs_token')->nullable()->unique()->after('gps_token_expires_at');
            $table->timestamp('docs_token_expires_at')->nullable()->after('docs_token');
            $table->json('uploaded_docs')->nullable()->after('docs_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['docs_token', 'docs_token_expires_at', 'uploaded_docs']);
        });
    }
};
