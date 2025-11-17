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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable(); // Teacher who recorded the absence
            $table->date('absence_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_justified')->default(false);
            $table->enum('type', ['full_day', 'partial', 'late_arrival', 'early_departure'])->default('full_day');
            $table->string('reason')->nullable();
            $table->text('justification')->nullable();
            $table->string('justification_document')->nullable(); // Path to uploaded document
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['student_id', 'absence_date']);
            $table->index(['class_id', 'absence_date']);
            $table->index('is_justified');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
