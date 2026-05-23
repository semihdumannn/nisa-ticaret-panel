<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('avatar_url')->nullable();
            $table->string('company_name', 200)->nullable();
            $table->string('tax_number', 20)->nullable();
            $table->decimal('balance', 12, 2)->default(0.00)->comment('Cari hesap bakiyesi');
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->json('metadata')->nullable()->comment('Esnek özellikler için JSON');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
