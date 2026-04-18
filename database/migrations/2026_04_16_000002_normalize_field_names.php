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
        if (Schema::hasTable('classes')) {
            if (Schema::hasColumn('classes', 'responsible_teacher_id') && ! Schema::hasColumn('classes', 'teacher_id')) {
                Schema::table('classes', function (Blueprint $table) {
                    $table->renameColumn('responsible_teacher_id', 'teacher_id');
                });
            } elseif (Schema::hasColumn('classes', 'teacher_id') && Schema::hasColumn('classes', 'responsible_teacher_id')) {
                Schema::table('classes', function (Blueprint $table) {
                    $table->dropColumn('responsible_teacher_id');
                });
            }
        }

        if (Schema::hasTable('events')) {
            if (Schema::hasColumn('events', 'event_date') && ! Schema::hasColumn('events', 'start_date')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->renameColumn('event_date', 'start_date');
                });
            }

            if (! Schema::hasColumn('events', 'end_date') && Schema::hasColumn('events', 'start_date')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->dateTime('end_date')->nullable()->after('start_date');
                });
            }

            if (Schema::hasColumn('events', 'event_date') && Schema::hasColumn('events', 'start_date')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropColumn('event_date');
                });
            }

            if (Schema::hasColumn('events', 'event_time')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropColumn('event_time');
                });
            }

            if (Schema::hasColumn('events', 'event_type') && Schema::hasColumn('events', 'type')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->dropColumn('event_type');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'teacher_id') && ! Schema::hasColumn('classes', 'responsible_teacher_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->renameColumn('teacher_id', 'responsible_teacher_id');
            });
        }

        if (Schema::hasTable('events')) {
            if (Schema::hasColumn('events', 'start_date') && ! Schema::hasColumn('events', 'event_date')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->date('event_date')->nullable()->after('description');
                });
            }

            if (! Schema::hasColumn('events', 'event_time')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->string('event_time')->nullable()->after('event_date');
                });
            }

            if (Schema::hasColumn('events', 'type') && ! Schema::hasColumn('events', 'event_type')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->string('event_type')->nullable()->after('type');
                });
            }
        }
    }
};
