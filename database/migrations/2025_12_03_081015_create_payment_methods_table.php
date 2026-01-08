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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['bank_transfer', 'lianpay', 'pingpong', 'worldfirst', 'payoneer'])->default('bank_transfer');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->text('qr_code')->nullable()->comment('QR code image path or data');
            $table->text('instructions')->nullable();
            $table->decimal('min_amount', 15, 2)->default(10.00);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->nullable()->comment('Exchange rate to USD');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
