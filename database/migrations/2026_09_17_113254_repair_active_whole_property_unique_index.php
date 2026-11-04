<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropActiveWholePropertyUniqueIndex();
        $this->createActiveWholePropertyUniqueIndex();
    }

    public function down(): void
    {
        $this->dropActiveWholePropertyUniqueIndex();
    }

    private function createActiveWholePropertyUniqueIndex(): void
    {
        match (DB::getDriverName()) {
            'pgsql', 'sqlite' => DB::statement("CREATE UNIQUE INDEX leases_active_whole_property_unique ON leases (property_id) WHERE unit_id IS NULL AND status = 'active' AND deleted_at IS NULL"),
            'mysql', 'mariadb' => Schema::table('leases', function (Blueprint $table): void {
                $table->unsignedBigInteger('active_whole_property_key')->nullable()->storedAs(
                    "IF(unit_id IS NULL AND status = 'active' AND deleted_at IS NULL, property_id, NULL)"
                );
                $table->unique('active_whole_property_key', 'leases_active_whole_property_unique');
            }),
            default => throw new LogicException('The lease unique-index repair migration does not support this database driver.'),
        };
    }

    private function dropActiveWholePropertyUniqueIndex(): void
    {
        match (DB::getDriverName()) {
            'pgsql', 'sqlite' => DB::statement('DROP INDEX IF EXISTS leases_active_whole_property_unique'),
            'mysql', 'mariadb' => $this->dropMysqlActiveWholePropertyUniqueIndex(),
            default => throw new LogicException('The lease unique-index repair migration does not support this database driver.'),
        };
    }

    private function dropMysqlActiveWholePropertyUniqueIndex(): void
    {
        if (! Schema::hasColumn('leases', 'active_whole_property_key')) {
            return;
        }

        Schema::table('leases', function (Blueprint $table): void {
            $table->dropUnique('leases_active_whole_property_unique');
            $table->dropColumn('active_whole_property_key');
        });
    }
};
