<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 30)->default('mobile')->after('created_by')
                  ->comment('web | mobile | field_agent');
            $table->foreignId('assigned_agent_id')->nullable()->after('assigned_to')
                  ->constrained('users')->onDelete('set null')
                  ->comment('Saha personeli');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_agent_id');
            $table->dropColumn('source');
        });
    }
};
