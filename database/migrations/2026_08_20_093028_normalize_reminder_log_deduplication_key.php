<?php

use App\Models\ReminderLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureNormalizedKeysAreUnique();

        Schema::table('reminder_logs', function (Blueprint $table) {
            // MySQL may use reminder_logs_unique to support the lease_id FK.
            $table->dropForeign(['lease_id']);
            $table->dropUnique('reminder_logs_unique');
        });

        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->smallInteger('overdue_days')
                ->nullable()
                ->default(null)
                ->change();
        });

        DB::table('reminder_logs')
            ->whereNull('overdue_days')
            ->update(['overdue_days' => ReminderLog::NON_OVERDUE_DAYS]);

        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->smallInteger('overdue_days')
                ->default(ReminderLog::NON_OVERDUE_DAYS)
                ->nullable(false)
                ->change();
            $table->unique(
                ['lease_id', 'period_start', 'reminder_type', 'overdue_days'],
                'reminder_logs_unique',
            );
            $table->foreign('lease_id')->references('id')->on('leases')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->dropForeign(['lease_id']);
            $table->dropUnique('reminder_logs_unique');
        });

        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->smallInteger('overdue_days')
                ->nullable()
                ->default(null)
                ->change();
        });

        DB::table('reminder_logs')
            ->where('overdue_days', ReminderLog::NON_OVERDUE_DAYS)
            ->update(['overdue_days' => null]);

        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->unsignedTinyInteger('overdue_days')
                ->nullable()
                ->default(null)
                ->change();
        });

        Schema::table('reminder_logs', function (Blueprint $table) {
            $table->unique(
                ['lease_id', 'period_start', 'reminder_type', 'overdue_days'],
                'reminder_logs_unique',
            );
            $table->foreign('lease_id')->references('id')->on('leases')->cascadeOnDelete();
        });
    }

    private function ensureNormalizedKeysAreUnique(): void
    {
        // Use a raw grouped query. Query Builder + bound COALESCE expressions
        // trips MySQL ONLY_FULL_GROUP_BY even when the expressions match.
        $normalizedDays = (int) ReminderLog::NON_OVERDUE_DAYS;

        $hasDuplicates = DB::selectOne(
            "select exists(
                select 1
                from reminder_logs
                group by lease_id, period_start, reminder_type, coalesce(overdue_days, {$normalizedDays})
                having count(*) > 1
            ) as `exists`"
        );

        if ((int) ($hasDuplicates->exists ?? 0) === 1) {
            throw new RuntimeException(
                'Cannot normalize reminder_logs.overdue_days because duplicate reminder keys exist.',
            );
        }
    }
};
