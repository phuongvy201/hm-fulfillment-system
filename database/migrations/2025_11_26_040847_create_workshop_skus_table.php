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
        Schema::create('workshop_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained('workshops')->onDelete('cascade');
            $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->string('sku')->unique(); // SKU của xưởng cho variant này
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['workshop_id', 'variant_id'], 'workshop_variant_unique'); // Mỗi workshop chỉ có 1 SKU cho mỗi variant
            $table->index('sku', 'workshop_sku_code');
            $table->index(['workshop_id', 'status'], 'workshop_sku_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_skus');
    }
};
