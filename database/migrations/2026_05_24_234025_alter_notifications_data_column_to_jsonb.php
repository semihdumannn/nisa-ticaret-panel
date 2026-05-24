<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Filament v5 DatabaseNotifications queries the `data` column using PostgreSQL
 * JSONB operators (->>'format'). The standard Laravel notifications migration
 * creates `data` as TEXT which does not support these operators on PostgreSQL.
 *
 * This migration converts the column to JSONB (PostgreSQL only; no-op on SQLite).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE jsonb USING data::jsonb');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE text USING data::text');
        }
    }
};
