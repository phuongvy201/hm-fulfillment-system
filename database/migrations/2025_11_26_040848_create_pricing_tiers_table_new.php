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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // wood, silver, gold, diamond, etc.
            $table->string('name');
            $table->integer('priority')->default(0); // Higher priority = better tier
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['priority', 'status'], 'pricing_tier_priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};
