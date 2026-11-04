<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->foreignId('property_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->string('location')->nullable()->after('unit_id');
        });

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE maintenance_tickets ALTER COLUMN unit_id DROP NOT NULL'),
            'mysql', 'mariadb' => Schema::table('maintenance_tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->change();
            }),
            'sqlite' => $this->makeSqliteUnitIdNullable(),
            default => throw new LogicException('The maintenance ticket property migration does not support this database driver.'),
        };
    }

    public function down(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropColumn('location');
        });

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE maintenance_tickets ALTER COLUMN unit_id SET NOT NULL'),
            'mysql', 'mariadb' => Schema::table('maintenance_tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable(false)->change();
            }),
            default => null,
        };
    }

    private function makeSqliteUnitIdNullable(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
        });

        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id_new')->nullable()->after('location');
        });

        DB::statement('UPDATE maintenance_tickets SET unit_id_new = unit_id');

        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->dropColumn('unit_id');
            $table->renameColumn('unit_id_new', 'unit_id');
        });

        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
        });
    }
};
