<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('grades')) {
            return;
        }

        DB::statement(
            "UPDATE grades
            SET value = ROUND((value / NULLIF(max_value, 0)) * 20, 2),
                max_value = 20
            WHERE max_value IS NOT NULL
              AND max_value > 0
              AND max_value <> 20"
        );

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE grades ALTER COLUMN max_value SET DEFAULT 20');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('grades')) {
            return;
        }

        DB::statement(
            "UPDATE grades
            SET value = ROUND((value / 20) * 100, 2),
                max_value = 100
            WHERE max_value = 20"
        );

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE grades ALTER COLUMN max_value SET DEFAULT 100');
        }
    }
};
