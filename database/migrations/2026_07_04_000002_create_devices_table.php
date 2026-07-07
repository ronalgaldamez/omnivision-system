<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('purchase_id')->nullable()->constrained('purchases');
            $table->string('mac_address', 50)->unique();
            $table->string('pon_sn', 50)->unique()->nullable();
            $table->string('default_ip', 45)->nullable();
            $table->string('default_username', 100)->nullable();
            $table->string('default_password', 255)->nullable();
            $table->string('default_ssid1', 100)->nullable();
            $table->string('default_lan_key', 100)->nullable();
            $table->enum('status', ['in_stock', 'assigned', 'installed', 'damaged'])->default('in_stock');
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->dateTime('assigned_at')->nullable();
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders');
            $table->dateTime('installed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
