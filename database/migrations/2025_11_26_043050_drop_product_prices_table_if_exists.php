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
        // Drop bảng product_prices nếu còn tồn tại (từ migration cũ đã bị xóa)
        if (Schema::hasTable('product_prices')) {
            Schema::dropIfExists('product_prices');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần tạo lại vì đã có migration mới
    }
};
