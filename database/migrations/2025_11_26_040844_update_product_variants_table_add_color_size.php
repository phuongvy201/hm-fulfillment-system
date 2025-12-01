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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('color')->nullable()->after('name');
            $table->string('size')->nullable()->after('color');
            // Remove old attributes column if exists and replace with color/size
            // Keep attributes column as JSON for other attributes if needed
            $table->index(['color', 'size'], 'variant_color_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('variant_color_size');
            $table->dropColumn(['color', 'size']);
        });
    }
};
