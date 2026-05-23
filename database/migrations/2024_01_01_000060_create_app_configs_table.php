<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_configs', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value');
            $table->enum('type', ['string', 'number', 'boolean', 'json'])->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Başlangıç config'leri
        DB::table('app_configs')->insert([
            ['key' => 'app_version_ios', 'value' => '1.0.0', 'type' => 'string', 'description' => 'Minimum iOS app version'],
            ['key' => 'app_version_android', 'value' => '1.0.0', 'type' => 'string', 'description' => 'Minimum Android app version'],
            ['key' => 'force_update', 'value' => 'false', 'type' => 'boolean', 'description' => 'Force update required'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Maintenance mode active'],
            ['key' => 'logo_url', 'value' => '', 'type' => 'string', 'description' => 'App logo URL'],
            ['key' => 'splash_image_url', 'value' => '', 'type' => 'string', 'description' => 'Splash screen image URL'],
            ['key' => 'primary_color', 'value' => '#E73A99', 'type' => 'string', 'description' => 'Primary brand color'],
            ['key' => 'secondary_color', 'value' => '#13275A', 'type' => 'string', 'description' => 'Secondary brand color'],
            ['key' => 'accent_color', 'value' => '#00A6AB', 'type' => 'string', 'description' => 'Accent color'],
            ['key' => 'whatsapp_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'WhatsApp notifications enabled'],
            ['key' => 'whatsapp_number', 'value' => '+905551234567', 'type' => 'string', 'description' => 'WhatsApp contact number'],
            ['key' => 'min_order_amount', 'value' => '50', 'type' => 'number', 'description' => 'Minimum order amount (TL)'],
            ['key' => 'free_shipping_threshold', 'value' => '200', 'type' => 'number', 'description' => 'Free shipping threshold (TL)'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_configs');
    }
};
