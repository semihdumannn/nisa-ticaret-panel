<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sale_price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['sale_price', 'unit', 'min_order_qty', 'max_order_qty', 'package_qty', 'is_koli']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('sale_price', 10, 2)->nullable()->after('price_adjustment');
            $table->string('unit', 50)->nullable()->after('sale_price');
            $table->unsignedInteger('min_order_qty')->default(1)->after('unit');
            $table->unsignedInteger('max_order_qty')->nullable()->after('min_order_qty');
            $table->unsignedInteger('package_qty')->default(1)->after('max_order_qty');
            $table->boolean('is_koli')->default(false)->after('package_qty');
        });
    }
};
