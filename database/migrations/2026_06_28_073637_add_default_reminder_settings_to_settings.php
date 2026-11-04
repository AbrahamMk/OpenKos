<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('reminder_enabled')->default(true);
            $table->unsignedTinyInteger('reminder_days_before')->default(3);

            // MySQL/MariaDB reject literal defaults on JSON columns (error 1101).
            if ($this->driverRejectsJsonDefaults()) {
                $table->json('reminder_overdue_intervals')->nullable();
            } else {
                $table->json('reminder_overdue_intervals')->default('[1,3,7]');
            }
        });

        if ($this->driverRejectsJsonDefaults()) {
            DB::table('settings')->whereNull('reminder_overdue_intervals')->update([
                'reminder_overdue_intervals' => json_encode([1, 3, 7]),
            ]);

            Schema::table('settings', function (Blueprint $table) {
                $table->json('reminder_overdue_intervals')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['reminder_enabled', 'reminder_days_before', 'reminder_overdue_intervals']);
        });
    }

    private function driverRejectsJsonDefaults(): bool
    {
        return in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
