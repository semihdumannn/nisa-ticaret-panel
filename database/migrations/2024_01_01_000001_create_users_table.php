<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_uid', 128)->unique()->nullable()->index();
            $table->string('phone', 20)->unique()->index();
            $table->string('name', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->enum('role', ['customer', 'field_agent', 'delivery', 'admin'])->default('customer')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // Opsiyonel (Firebase Auth kullanılıyor)
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
