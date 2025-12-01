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
        Schema::create('product_printing_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->tinyInteger('sides'); // 1, 2, 3, 4, 5 mặt
            $table->decimal('price', 15, 2); // Giá in
            $table->string('currency', 3);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'variant_id', 'market_id', 'sides'], 'unique_printing_price');
            $table->index(['product_id', 'variant_id', 'market_id', 'status'], 'printing_price_lookup');
            $table->index(['valid_from', 'valid_to'], 'printing_price_valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_printing_prices');
    }
};
