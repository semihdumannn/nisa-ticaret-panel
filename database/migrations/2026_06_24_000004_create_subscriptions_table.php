<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('variant_id')->constrained('product_variants');
            $table->integer('quantity')->default(1);
            $table->foreignId('address_id')->constrained('addresses');
            $table->enum('plan', ['weekly', 'biweekly', 'monthly']);
            $table->decimal('discount_rate', 5, 2);
            $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
            $table->date('next_order_date');
            $table->foreignId('last_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->date('start_date');
            $table->date('pause_until')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index(['status', 'next_order_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
