<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('token');
            $table->enum('platform', ['ios', 'android'])->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            // Token uniqueness with user_id
            $table->unique(['user_id', 'token'], 'user_token_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
