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
        Schema::create('import_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path'); // S3 path
            $table->string('file_url'); // S3 URL
            $table->string('original_name');
            $table->integer('file_size'); // in bytes
            $table->string('mime_type');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('total_orders')->default(0); // Total orders in file
            $table->integer('processed_orders')->default(0); // Orders successfully processed
            $table->integer('failed_orders')->default(0); // Orders failed to process
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('errors')->nullable(); // JSON array of errors
            $table->json('order_data')->nullable(); // Store parsed order data for review
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_files');
    }
};
