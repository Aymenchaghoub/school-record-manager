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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('teacher_id');
            $table->decimal('value', 5, 2);
            $table->decimal('max_value', 5, 2)->default(100);
            $table->enum('type', ['exam', 'quiz', 'assignment', 'project', 'participation', 'midterm', 'final']);
            $table->string('title')->nullable();
            $table->date('grade_date');
            $table->string('term')->nullable(); // e.g., "Term 1", "Semester 1"
            $table->decimal('weight', 5, 2)->default(1); // Weight for calculating averages
            $table->text('comment')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['student_id', 'subject_id']);
            $table->index(['class_id', 'subject_id']);
            $table->index('grade_date');
            $table->index('term');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
