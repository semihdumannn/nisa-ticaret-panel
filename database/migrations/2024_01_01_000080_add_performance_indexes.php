<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 8 — Performance Indexes
 *
 * Adds database indexes identified during the optimization audit.
 * All additions are additive (no existing indexes are removed).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── order_items ───────────────────────────────────────────────────────
        // product_id is used in GetTopProductsUseCase JOIN and revenue aggregation.
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id', 'order_items_product_id_idx');
        });

        // ── orders ────────────────────────────────────────────────────────────
        // payment_status queried for financial reports and payment reconciliation.
        Schema::table('orders', function (Blueprint $table) {
            $table->index('payment_status', 'orders_payment_status_idx');
        });

        // ── users ─────────────────────────────────────────────────────────────
        // deleted_at helps soft-delete queries skip logically deleted rows fast.
        Schema::table('users', function (Blueprint $table) {
            $table->index('deleted_at', 'users_deleted_at_idx');
        });

        // ── products ─────────────────────────────────────────────────────────
        // deleted_at for same reason on product soft deletes.
        Schema::table('products', function (Blueprint $table) {
            $table->index('deleted_at', 'products_deleted_at_idx');
        });

        // ── notifications ─────────────────────────────────────────────────────
        // created_at for ordering + range queries.
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('created_at', 'notifications_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_product_id_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_payment_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_deleted_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_deleted_at_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_created_at_idx');
        });
    }
};
