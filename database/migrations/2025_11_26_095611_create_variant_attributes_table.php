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
        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->string('attribute_name', 255)->notNull(); // Color, Size, Material,...
            $table->string('attribute_value', 255)->notNull(); // Red, XL, Cotton,...
            $table->timestamps();

            // Index for faster lookups
            $table->index(['variant_id', 'attribute_name'], 'variant_attr_name');
            $table->index('variant_id', 'variant_attr_variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_attributes');
    }
};
