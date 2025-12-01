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
        Schema::table('products', function (Blueprint $table) {
            // Add workshop_id column first (nullable, so existing data won't break)
            if (!Schema::hasColumn('products', 'workshop_id')) {
                $table->foreignId('workshop_id')->nullable()->after('id')->constrained('workshops')->onDelete('restrict');
                $table->index('workshop_id', 'products_workshop_id_index');
            }
        });

        // Drop existing foreign key and index for market_id (in separate step)
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'market_id')) {
                // Try to drop foreign key if exists
                try {
                    $table->dropForeign(['market_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist or has different name
                    try {
                        $foreignKeys = DB::select("
                            SELECT CONSTRAINT_NAME 
                            FROM information_schema.KEY_COLUMN_USAGE 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'products' 
                            AND COLUMN_NAME = 'market_id' 
                            AND REFERENCED_TABLE_NAME IS NOT NULL
                        ");

                        foreach ($foreignKeys as $key) {
                            DB::statement("ALTER TABLE products DROP FOREIGN KEY {$key->CONSTRAINT_NAME}");
                        }
                    } catch (\Exception $e2) {
                        // Ignore if no foreign keys found
                    }
                }

                try {
                    $table->dropIndex(['market_id']);
                } catch (\Exception $e) {
                    // Index might not exist
                }

                $table->dropColumn('market_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop workshop_id
            if (Schema::hasColumn('products', 'workshop_id')) {
                $table->dropForeign(['workshop_id']);
                $table->dropIndex(['workshop_id']);
                $table->dropColumn('workshop_id');
            }

            // Restore market_id
            $table->foreignId('market_id')->nullable()->after('id')->constrained('markets')->onDelete('restrict');
            $table->index('market_id', 'products_market_id_index');
        });
    }
};
