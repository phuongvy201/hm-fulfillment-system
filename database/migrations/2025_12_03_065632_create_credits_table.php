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
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('credit_limit', 15, 2)->default(0)->comment('Hạn mức công nợ');
            $table->decimal('current_credit', 15, 2)->default(0)->comment('Công nợ hiện tại');
            $table->boolean('enabled')->default(false)->comment('Có được cấp công nợ hay không');
            $table->timestamps();

            $table->index('user_id');
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
