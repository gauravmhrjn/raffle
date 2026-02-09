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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->index('idx_category_id');
            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->index('idx_brand_id');
            $table->tinyInteger('status')->default(0);
            $table->string('sku', 255)->unique()->index('idx_sku');
            $table->string('slug', 255)->unique()->index('idx_slug');
            $table->string('name', 255);
            $table->string('image_url', 255);
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('qty');
            $table->dateTime('raffle_date')->index('raffle_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
