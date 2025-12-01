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
        Schema::create('user_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pricing_tier_id')->constrained('pricing_tiers')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            // One tier per user
            $table->unique('user_id');
            $table->index('pricing_tier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pricing_tiers');
    }
};
