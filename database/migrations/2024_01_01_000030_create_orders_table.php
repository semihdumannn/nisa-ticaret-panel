<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique()->index();
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('address_id')->nullable()->constrained()->onDelete('set null');
            
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 
                'on_the_way', 'delivered', 'cancelled'
            ])->default('pending')->index();
            
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('shipping_amount', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2);
            
            $table->string('payment_method', 20)->nullable()->comment('cash, credit_card, bank_transfer, account');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable()->comment('Admin notları');
            
            // Teslimat bilgileri
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->comment('Teslimat personeli');
            $table->date('scheduled_delivery_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Siparişi oluşturan');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['customer_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
