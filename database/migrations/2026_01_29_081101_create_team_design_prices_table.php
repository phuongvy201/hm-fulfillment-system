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
        Schema::create('team_design_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->decimal('first_side_price_vnd', 10, 2)->comment('Price for first side in VND');
            $table->decimal('additional_side_price_vnd', 10, 2)->comment('Price for each additional side in VND');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['team_id', 'status'], 'team_design_price_lookup');
            $table->index(['valid_from', 'valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_design_prices');
    }
};
