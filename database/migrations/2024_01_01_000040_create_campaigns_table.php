<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y'])->comment('İndirim tipi');
            $table->decimal('value', 10, 2)->nullable()->comment('İndirim miktarı veya %');
            $table->decimal('min_purchase_amount', 12, 2)->nullable()->comment('Minimum sepet tutarı');
            $table->decimal('max_discount_amount', 12, 2)->nullable()->comment('Maksimum indirim');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('usage_limit')->nullable()->comment('Toplam kullanım limiti');
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
