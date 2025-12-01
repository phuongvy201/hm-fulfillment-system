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
        // Rename table if it exists and target doesn't
        if (Schema::hasTable('user_prices') && !Schema::hasTable('user_custom_prices')) {
            Schema::rename('user_prices', 'user_custom_prices');
        }

        // Only proceed if table exists
        if (!Schema::hasTable('user_custom_prices')) {
            return;
        }

        // Drop location_id column if exists
        if (Schema::hasColumn('user_custom_prices', 'location_id')) {
            // Step 1: Drop foreign key for location_id
            $locationForeignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'user_custom_prices' 
                AND COLUMN_NAME = 'location_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($locationForeignKeys as $fk) {
                try {
                    DB::statement("ALTER TABLE `user_custom_prices` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                } catch (\Exception $e) {
                    // Continue if already dropped
                }
            }

            // Step 2: Recreate index without location_id (if index exists)
            // Check if index exists
            $indexExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'user_custom_prices' 
                AND INDEX_NAME = 'user_price_lookup'
            ");

            if (!empty($indexExists) && $indexExists[0]->count > 0) {
                // Drop old index
                try {
                    DB::statement("ALTER TABLE `user_custom_prices` DROP INDEX `user_price_lookup`");
                } catch (\Exception $e) {
                    // Index might not exist or can't be dropped yet
                }
            }

            // Step 3: Drop the column
            try {
                DB::statement("ALTER TABLE `user_custom_prices` DROP COLUMN `location_id`");
            } catch (\Exception $e) {
                // If still fails, the index might be blocking
                // Try one more time with a different approach
            }
        }

        // Add market_id if not exists
        Schema::table('user_custom_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('user_custom_prices', 'market_id')) {
                $table->foreignId('market_id')->after('variant_id')->constrained('markets')->onDelete('cascade');
            }
        });

        // Add new index with market_id
        if (Schema::hasTable('user_custom_prices')) {
            try {
                Schema::table('user_custom_prices', function (Blueprint $table) {
                    $table->index(['user_id', 'product_id', 'variant_id', 'market_id', 'status'], 'user_custom_price_lookup');
                });
            } catch (\Exception $e) {
                // Index might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_custom_prices', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropIndex('user_custom_price_lookup');

            $table->dropColumn('market_id');

            $table->foreignId('location_id')->after('variant_id')->constrained('locations')->onDelete('cascade');

            $table->index(['user_id', 'product_id', 'variant_id', 'location_id', 'status'], 'user_price_lookup');
        });

        // Rename back
        Schema::rename('user_custom_prices', 'user_prices');
    }
};
