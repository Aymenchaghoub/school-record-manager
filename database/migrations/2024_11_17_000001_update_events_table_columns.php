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
        Schema::table('events', function (Blueprint $table) {
            // Add missing columns that controllers expect
            $table->date('event_date')->nullable()->after('description');
            $table->string('event_time')->nullable()->after('event_date');
            $table->string('event_type')->nullable()->after('type');
            $table->json('target_audience')->nullable()->after('is_public');
            $table->boolean('is_published')->default(true)->after('is_public');
        });
        
        // Copy data from existing columns to new columns
        \DB::table('events')->update([
            'event_date' => \DB::raw('DATE(start_date)'),
            'event_time' => \DB::raw('TIME(start_date)'),
            'event_type' => \DB::raw('type')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['event_date', 'event_time', 'event_type', 'target_audience', 'is_published']);
        });
    }
};
