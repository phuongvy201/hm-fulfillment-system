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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // US, UK, VN
            $table->string('name');
            $table->string('currency', 3); // USD, GBP, VND
            $table->string('timezone')->default('UTC');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
