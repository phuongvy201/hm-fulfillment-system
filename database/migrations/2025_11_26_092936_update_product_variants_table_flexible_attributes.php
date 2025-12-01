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
            // Drop old columns
            if (Schema::hasColumn('product_variants', 'attributes')) {
                $table->dropColumn('attributes');
            }

            // Add new flexible attribute columns
            if (!Schema::hasColumn('product_variants', 'attribute1_name')) {
                $table->string('attribute1_name')->nullable()->after('name');
                $table->string('attribute1_value')->nullable()->after('attribute1_name');
                $table->string('attribute2_name')->nullable()->after('attribute1_value');
                $table->string('attribute2_value')->nullable()->after('attribute2_name');
            }

            // Keep color and size for backward compatibility with SKU generation
            // They will be automatically synced with attribute1/attribute2 if names match
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Drop new columns
            if (Schema::hasColumn('product_variants', 'attribute1_name')) {
                $table->dropColumn(['attribute1_name', 'attribute1_value', 'attribute2_name', 'attribute2_value']);
            }

            // Restore attributes column
            if (!Schema::hasColumn('product_variants', 'attributes')) {
                $table->json('attributes')->nullable()->after('name');
            }
        });
    }
};
