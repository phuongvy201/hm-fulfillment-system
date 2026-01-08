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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'top_up',              // Nạp tiền
                'payment',             // Thanh toán đơn
                'credit_used',         // Sử dụng công nợ
                'credit_payment',      // Thanh toán công nợ
                'admin_adjustment',    // Điều chỉnh bởi admin
                'refund'               // Hoàn tiền
            ]);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable()->comment('Model class của đối tượng liên quan');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID của đối tượng liên quan');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Người tạo giao dịch (admin nếu là điều chỉnh)');
            $table->timestamps();
            
            $table->index('wallet_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
