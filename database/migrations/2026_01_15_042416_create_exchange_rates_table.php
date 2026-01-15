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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3); // USD, GBP, etc.
            $table->string('to_currency', 3); // USD, GBP, etc.
            $table->decimal('rate', 15, 6); // Exchange rate (e.g., 1 USD = 24500 VND)
            $table->date('effective_date'); // When this rate becomes effective
            $table->date('expires_at')->nullable(); // Optional expiry date
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable(); // Optional notes from accountant
            $table->timestamps();

            $table->index(['from_currency', 'to_currency']);
            $table->index('effective_date');
            $table->index('status');
            // Ensure no duplicate active rates for the same currency pair and date
            // Note: Using partial unique index would be better but MySQL doesn't support it
            // We'll handle uniqueness validation in the controller
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
