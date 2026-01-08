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
        Schema::table('top_up_requests', function (Blueprint $table) {
            $table->string('transaction_code')->nullable()->after('payment_method');
            $table->foreignId('payment_method_id')->nullable()->after('transaction_code')->constrained('payment_methods')->onDelete('set null');
            $table->index('transaction_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('top_up_requests', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropIndex(['transaction_code']);
            $table->dropColumn(['transaction_code', 'payment_method_id']);
        });
    }
};
