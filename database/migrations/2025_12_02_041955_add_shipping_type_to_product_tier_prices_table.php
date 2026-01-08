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
        // Kiểm tra xem cột đã tồn tại chưa
        $columns = DB::select("SHOW COLUMNS FROM product_tier_prices LIKE 'shipping_type'");
        
        if (empty($columns)) {
            Schema::table('product_tier_prices', function (Blueprint $table) {
                // Thêm shipping_type: null = giá thông thường, 'seller' = giá ship by seller (đã bao gồm base + ship), 'tiktok' = giá ship by tiktok (đã bao gồm base + ship)
                $table->enum('shipping_type', ['seller', 'tiktok'])->nullable()->after('pricing_tier_id')->default(null);
            });
        }
        
        // Kiểm tra và tạo unique constraint mới
        $indexes = DB::select("SHOW INDEX FROM product_tier_prices WHERE Key_name = 'unique_tier_price_with_shipping'");
        
        if (empty($indexes)) {
            // Drop unique constraint cũ nếu có
            try {
                DB::statement('ALTER TABLE product_tier_prices DROP INDEX unique_tier_price_with_shipping');
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
            
            // Tạo unique constraint mới
            Schema::table('product_tier_prices', function (Blueprint $table) {
                $table->unique(['product_id', 'variant_id', 'market_id', 'pricing_tier_id', 'shipping_type'], 'unique_tier_price_with_shipping');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_tier_prices', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_tier_price_with_shipping');
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
            $table->dropColumn('shipping_type');
        });
    }
};
