<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sku', 50)->unique();
            $table->string('name', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->string('barcode', 50)->nullable()->index();
            $table->string('unit', 20)->default('piece')->comment('piece, kg, liter, box, pack');
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable()->comment('Maliyet fiyatı');
            $table->decimal('tax_rate', 5, 2)->default(20.00)->comment('KDV oranı');
            $table->integer('min_order_qty')->default(1);
            $table->integer('max_order_qty')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable()->comment('Cam/plastik şişe, hacim vs');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['brand_id', 'is_active']);
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
