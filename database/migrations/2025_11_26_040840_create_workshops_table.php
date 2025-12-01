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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->onDelete('cascade');
            $table->string('code')->unique(); // Unique code for workshop
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('product_types')->nullable(); // Types of products this workshop can fulfill
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['market_id', 'status'], 'workshop_market_status');
            $table->index('code', 'workshop_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
