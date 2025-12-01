<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Drop old attribute columns
            $columnsToDrop = [
                'color',
                'size',
                'attributes',
                'attribute1_name',
                'attribute1_value',
                'attribute2_name',
                'attribute2_value',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('product_variants', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Drop old indexes safely
            try {
                DB::statement('ALTER TABLE product_variants DROP INDEX IF EXISTS variant_color_size');
            } catch (\Exception $e) {
                // Index might not exist or already dropped
            }

            // Add price and cost columns
            if (!Schema::hasColumn('product_variants', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('sku');
            }
            if (!Schema::hasColumn('product_variants', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('price');
            }

            // Drop name column if exists (not needed in new structure)
            if (Schema::hasColumn('product_variants', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Restore name column
            if (!Schema::hasColumn('product_variants', 'name')) {
                $table->string('name')->after('product_id');
            }

            // Remove price and cost
            if (Schema::hasColumn('product_variants', 'cost')) {
                $table->dropColumn('cost');
            }
            if (Schema::hasColumn('product_variants', 'price')) {
                $table->dropColumn('price');
            }

            // Restore old columns (simplified)
            if (!Schema::hasColumn('product_variants', 'color')) {
                $table->string('color')->nullable()->after('name');
                $table->string('size')->nullable()->after('color');
                $table->index(['color', 'size'], 'variant_color_size');
            }
        });
    }
};
