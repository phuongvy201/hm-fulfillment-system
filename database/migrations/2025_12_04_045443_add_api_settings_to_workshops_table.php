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
        Schema::table('workshops', function (Blueprint $table) {
            $table->string('api_type')->nullable()->comment('API type: rest, soap, custom');
            $table->string('api_endpoint')->nullable()->comment('Base API endpoint URL');
            $table->string('api_key')->nullable()->comment('API key for authentication');
            $table->string('api_secret')->nullable()->comment('API secret for authentication');
            $table->json('api_settings')->nullable()->comment('Additional API settings (headers, timeout, etc.)');
            $table->boolean('api_enabled')->default(false)->comment('Enable/disable API integration');
            $table->text('api_notes')->nullable()->comment('Notes about API configuration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn([
                'api_type',
                'api_endpoint',
                'api_key',
                'api_secret',
                'api_settings',
                'api_enabled',
                'api_notes',
            ]);
        });
    }
};
