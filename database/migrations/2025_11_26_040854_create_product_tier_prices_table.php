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
        Schema::create('product_tier_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->foreignId('pricing_tier_id')->constrained('pricing_tiers')->onDelete('cascade');
            $table->decimal('base_price', 15, 2); // Giá cơ bản
            $table->string('currency', 3);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'variant_id', 'market_id', 'pricing_tier_id', 'status'], 'tier_price_lookup');
            $table->index(['valid_from', 'valid_to'], 'tier_price_valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tier_prices');
    }
};
