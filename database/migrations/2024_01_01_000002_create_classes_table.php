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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('level'); // e.g., "Grade 1", "Grade 2", etc.
            $table->string('section')->nullable(); // e.g., "A", "B", "C"
            $table->string('academic_year');
            $table->unsignedBigInteger('responsible_teacher_id')->nullable();
            $table->integer('capacity')->default(30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('responsible_teacher_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['academic_year', 'is_active']);
            $table->index('responsible_teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
