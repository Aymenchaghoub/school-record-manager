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
        if (! Schema::hasColumn('events', 'target_audience')) {
            Schema::table('events', function (Blueprint $table) {
                $table->json('target_audience')->nullable()->after('is_public');
            });
        }

        if (! Schema::hasColumn('events', 'is_published')) {
            Schema::table('events', function (Blueprint $table) {
                $table->boolean('is_published')->default(true)->after('is_public');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('events', 'target_audience') || Schema::hasColumn('events', 'is_published')) {
            Schema::table('events', function (Blueprint $table) {
                if (Schema::hasColumn('events', 'target_audience')) {
                    $table->dropColumn('target_audience');
                }

                if (Schema::hasColumn('events', 'is_published')) {
                    $table->dropColumn('is_published');
                }
            });
        }
    }
};
