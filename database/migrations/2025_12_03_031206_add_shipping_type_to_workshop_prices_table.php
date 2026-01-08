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
        if (!Schema::hasTable('workshop_prices')) {
            return;
        }

        // Check if column already exists
        $columns = DB::select("SHOW COLUMNS FROM workshop_prices LIKE 'shipping_type'");
        
        if (empty($columns)) {
            Schema::table('workshop_prices', function (Blueprint $table) {
                // Thêm shipping_type: null = giá thông thường, 'seller' = giá ship by seller, 'tiktok' = giá ship by tiktok
                $table->enum('shipping_type', ['seller', 'tiktok'])->nullable()->after('variant_id')->default(null);
            });

            // Update unique index to include shipping_type
            try {
                // Drop old index if exists
                $indexExists = DB::select("
                    SELECT COUNT(*) as count
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'workshop_prices' 
                    AND INDEX_NAME = 'workshop_price_lookup'
                ");

                if (!empty($indexExists) && $indexExists[0]->count > 0) {
                    DB::statement("ALTER TABLE `workshop_prices` DROP INDEX `workshop_price_lookup`");
                }

                // Create new unique index with shipping_type
        Schema::table('workshop_prices', function (Blueprint $table) {
                    $table->unique(['workshop_id', 'product_id', 'variant_id', 'shipping_type'], 'workshop_price_lookup');
        });
            } catch (\Exception $e) {
                // Index might already exist or can't be created
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('workshop_prices')) {
            return;
        }

        Schema::table('workshop_prices', function (Blueprint $table) {
            // Drop unique index first
            try {
                $table->dropUnique('workshop_price_lookup');
            } catch (\Exception $e) {
                // Index might not exist
            }

            // Recreate index without shipping_type
            try {
                $table->index(['workshop_id', 'product_id', 'variant_id', 'status'], 'workshop_price_lookup');
            } catch (\Exception $e) {
                // Index might already exist
            }

            // Drop shipping_type column
            if (Schema::hasColumn('workshop_prices', 'shipping_type')) {
                $table->dropColumn('shipping_type');
            }
        });
    }
};
