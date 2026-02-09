<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_code')->unique();
            $table->uuid('payment_transaction_code')->unique();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('payment_status')->default(0);
            $table->foreignId('raffle_entry_id')->constrained()->index('idx_raffle_entry_id');
            $table->foreignId('user_id')->constrained()->index('idx_user_id');
            $table->foreignId('address_id')->constrained();
            $table->foreignId('product_id')->constrained()->index('idx_product_id');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
