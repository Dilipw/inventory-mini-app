<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->date('order_date');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['order_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};