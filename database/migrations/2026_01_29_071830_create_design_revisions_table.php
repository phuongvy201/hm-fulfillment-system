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
        Schema::create('design_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('design_task_id')->constrained('design_tasks')->onDelete('cascade');
            $table->foreignId('designer_id')->constrained('users')->onDelete('cascade');
            $table->string('design_file')->comment('Design file path for this revision');
            $table->text('notes')->nullable()->comment('Designer notes');
            $table->text('revision_notes')->nullable()->comment('Customer revision notes');
            $table->integer('version')->default(1)->comment('Revision version number');
            $table->string('status')->default('submitted')->comment('Revision status');
            $table->timestamp('submitted_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index('design_task_id');
            $table->index('designer_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_revisions');
    }
};
