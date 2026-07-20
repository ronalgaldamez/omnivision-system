<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('contract_digital_code', 20)->nullable()->unique()->after('id');
            $table->string('signed_pdf_path')->nullable()->after('longitude');
            $table->timestamp('signed_at')->nullable()->after('signed_pdf_path');
            $table->text('contract_terms')->nullable()->after('contract_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->after('contract_terms');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'contract_digital_code',
                'signed_pdf_path',
                'signed_at',
                'contract_terms',
                'created_by',
            ]);
        });
    }
};
