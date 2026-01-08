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
        Schema::create('debt_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2)->comment('Số tiền thanh toán công nợ');
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method')->comment('Phương thức thanh toán');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_code')->unique()->nullable()->comment('Mã giao dịch');
            $table->string('proof_file')->nullable()->comment('File chứng từ thanh toán');
            $table->text('notes')->nullable()->comment('Ghi chú từ khách hàng');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable()->comment('Ghi chú từ admin');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payment_requests');
    }
};
