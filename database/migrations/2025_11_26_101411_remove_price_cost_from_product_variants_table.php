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
        Schema::table('product_variants', function (Blueprint $table) {
            // Drop price and cost columns
            if (Schema::hasColumn('product_variants', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('product_variants', 'cost')) {
                $table->dropColumn('cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Restore price and cost columns
            if (!Schema::hasColumn('product_variants', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('sku');
            }
            if (!Schema::hasColumn('product_variants', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->after('price');
            }
        });
    }
};
