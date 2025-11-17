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
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->string('term'); // e.g., "Term 1", "Semester 1", "Q1"
            $table->string('academic_year');
            $table->decimal('overall_average', 5, 2)->nullable();
            $table->integer('total_absences')->default(0);
            $table->integer('justified_absences')->default(0);
            $table->integer('rank_in_class')->nullable();
            $table->integer('total_students')->nullable();
            $table->json('subject_grades'); // JSON array of subject grades and averages
            $table->text('principal_remarks')->nullable();
            $table->text('teacher_remarks')->nullable();
            $table->enum('conduct_grade', ['Excellent', 'Very Good', 'Good', 'Fair', 'Poor'])->nullable();
            $table->date('issue_date');
            $table->boolean('is_final')->default(false);
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            
            $table->unique(['student_id', 'class_id', 'term', 'academic_year']);
            $table->index(['academic_year', 'term']);
            $table->index('is_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
