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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Internal order number');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('workshop_id')->constrained('workshops')->onDelete('cascade');
            $table->string('workshop_order_id')->nullable()->comment('Order ID from workshop API');
            $table->string('tracking_number')->nullable()->comment('Tracking number from workshop');
            $table->string('tracking_url')->nullable()->comment('Tracking URL');
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'failed'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->json('shipping_address')->nullable()->comment('Shipping address details');
            $table->json('billing_address')->nullable()->comment('Billing address details');
            $table->json('items')->nullable()->comment('Order items (product, variant, quantity, price)');
            $table->json('api_request')->nullable()->comment('Original API request data');
            $table->json('api_response')->nullable()->comment('API response data');
            $table->text('notes')->nullable();
            $table->text('error_message')->nullable()->comment('Error message if order failed');
            $table->timestamp('submitted_at')->nullable()->comment('When order was submitted to workshop');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index('order_number');
            $table->index(['user_id', 'status']);
            $table->index(['workshop_id', 'status']);
            $table->index('workshop_order_id');
            $table->index('tracking_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
