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
        Schema::create('raffle_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('entry_code')->unique();
            $table->tinyInteger('status')->default(0);
            $table->foreignId('user_id')->index('idx_user_id');
            $table->foreignId('address_id')->constrained();
            $table->foreignId('product_id')->constrained()->index('idx_product_id');
            $table->string('encrypted_payment_token', 255);
            $table->timestamps();

            $table->unique(['product_id', 'user_id'], 'unique_product_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raffle_entries');
    }
};
