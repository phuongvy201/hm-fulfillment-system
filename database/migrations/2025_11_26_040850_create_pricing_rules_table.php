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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->string('rule_type'); // printing_sides, size_adjustment, etc.
            $table->string('condition_key')->nullable(); // product_type, size, color, etc.
            $table->string('condition_value')->nullable(); // t-shirt, S, red, etc.
            $table->enum('operation', ['add', 'subtract', 'multiply', 'divide', 'set']); // +, -, *, /, =
            $table->decimal('amount', 15, 2); // Giá trị áp dụng
            $table->string('currency', 3)->nullable(); // Currency of the amount
            $table->integer('priority')->default(0); // Độ ưu tiên khi áp dụng nhiều rules
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['market_id', 'product_id', 'rule_type', 'status'], 'pricing_rules_lookup');
            $table->index(['priority', 'status'], 'pricing_rules_priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
