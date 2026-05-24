<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Our custom notifications table (app push/in-app notifications) conflicts
 * with Laravel's standard notification system used by Filament.
 *
 * Solution:
 *  1. Rename our custom table → app_notifications
 *  2. Create standard Laravel notifications table for Filament DB notifications
 */
return new class extends Migration
{
    public function up(): void
    {
        // Rename our custom table to avoid conflict
        Schema::rename('notifications', 'app_notifications');

        // Create standard Laravel notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::rename('app_notifications', 'notifications');
    }
};
