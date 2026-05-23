<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->integer('total_customers')->default(0);
            $table->integer('new_customers')->default(0);
            $table->decimal('avg_order_value', 12, 2)->default(0.00);
            $table->timestamps();
            
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
    }
};
