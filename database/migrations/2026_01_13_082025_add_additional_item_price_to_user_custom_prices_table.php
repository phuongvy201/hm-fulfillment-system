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
        Schema::table('user_custom_prices', function (Blueprint $table) {
            // Giá cho item thứ 2 trở đi (đã trừ phí label)
            // Chỉ áp dụng cho shipping_type = 'seller' hoặc 'tiktok'
            $table->decimal('additional_item_price', 15, 2)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_custom_prices', function (Blueprint $table) {
            $table->dropColumn('additional_item_price');
        });
    }
};
