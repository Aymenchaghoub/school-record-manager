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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['exam', 'meeting', 'holiday', 'sports', 'cultural', 'parent_meeting', 'other']);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('class_id')->nullable(); // null means it's a school-wide event
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_public')->default(true);
            $table->string('color')->default('#3B82F6'); // For calendar display
            $table->timestamps();
            
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['start_date', 'end_date']);
            $table->index('type');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
