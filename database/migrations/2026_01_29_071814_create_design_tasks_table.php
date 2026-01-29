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
        Schema::create('design_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade')->comment('Customer who created the task');
            $table->foreignId('designer_id')->nullable()->constrained('users')->onDelete('set null')->comment('Designer assigned to the task');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sides_count')->comment('Number of design sides');
            $table->decimal('price', 8, 2);
            $table->enum('status', ['pending', 'joined', 'completed', 'approved', 'revision', 'cancelled'])->default('pending');
            $table->string('mockup_file')->nullable()->comment('Mockup file path');
            $table->string('design_file')->nullable()->comment('Final design file path');
            $table->text('revision_notes')->nullable()->comment('Notes for revision');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('customer_id');
            $table->index('designer_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_tasks');
    }
};
