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
        Schema::table('pricing_tiers', function (Blueprint $table) {
            // Số đơn hàng tối thiểu để đạt tier này (null = không có điều kiện, như special tier)
            $table->integer('min_orders')->nullable()->after('priority');
            
            // Có tự động gán tier này dựa trên điều kiện không (special tier sẽ là false)
            $table->boolean('auto_assign')->default(true)->after('min_orders');
            
            // Chu kỳ reset (monthly = reset mỗi tháng, never = không reset)
            $table->enum('reset_period', ['monthly', 'quarterly', 'yearly', 'never'])->default('monthly')->after('auto_assign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_tiers', function (Blueprint $table) {
            $table->dropColumn(['min_orders', 'auto_assign', 'reset_period']);
        });
    }
};
