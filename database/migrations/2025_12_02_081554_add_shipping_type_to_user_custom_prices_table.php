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
        if (!Schema::hasTable('user_custom_prices')) {
            return;
        }

        // Check if column already exists
        $columns = DB::select("SHOW COLUMNS FROM user_custom_prices LIKE 'shipping_type'");
        
        if (empty($columns)) {
            Schema::table('user_custom_prices', function (Blueprint $table) {
                // Thêm shipping_type: null = giá thông thường, 'seller' = giá ship by seller (đã bao gồm base + ship), 'tiktok' = giá ship by tiktok (đã bao gồm base + ship)
                $table->enum('shipping_type', ['seller', 'tiktok'])->nullable()->after('market_id')->default(null);
            });

            // Update unique index to include shipping_type
            try {
                // Drop old index if exists
                $indexExists = DB::select("
                    SELECT COUNT(*) as count
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'user_custom_prices' 
                    AND INDEX_NAME = 'user_custom_price_lookup'
                ");

                if (!empty($indexExists) && $indexExists[0]->count > 0) {
                    DB::statement("ALTER TABLE `user_custom_prices` DROP INDEX `user_custom_price_lookup`");
                }

                // Create new unique index with shipping_type
        Schema::table('user_custom_prices', function (Blueprint $table) {
                    $table->unique(['user_id', 'product_id', 'variant_id', 'market_id', 'shipping_type'], 'user_custom_price_lookup');
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
        if (!Schema::hasTable('user_custom_prices')) {
            return;
        }

        Schema::table('user_custom_prices', function (Blueprint $table) {
            // Drop unique index first
            try {
                $table->dropUnique('user_custom_price_lookup');
            } catch (\Exception $e) {
                // Index might not exist
            }

            // Recreate index without shipping_type
            try {
                $table->index(['user_id', 'product_id', 'variant_id', 'market_id', 'status'], 'user_custom_price_lookup');
            } catch (\Exception $e) {
                // Index might already exist
            }

            // Drop shipping_type column
            if (Schema::hasColumn('user_custom_prices', 'shipping_type')) {
                $table->dropColumn('shipping_type');
            }
        });
    }
};
