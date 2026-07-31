<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('client_signature_data')->nullable()->after('billing_address');
            $table->string('signature_token', 100)->nullable()->unique()->after('client_signature_data');
            $table->timestamp('signature_token_expires_at')->nullable()->after('signature_token');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['signature_token', 'signature_token_expires_at', 'client_signature_data']);
        });
    }
};
