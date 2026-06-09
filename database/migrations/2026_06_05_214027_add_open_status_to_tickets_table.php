<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar el ENUM para agregar 'open'
        DB::statement("ALTER TABLE `tickets` MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'resolved', 'closed', 'open') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    public function down(): void
    {
        // Volver al estado anterior (sin 'open')
        DB::statement("ALTER TABLE `tickets` MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'resolved', 'closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }
};