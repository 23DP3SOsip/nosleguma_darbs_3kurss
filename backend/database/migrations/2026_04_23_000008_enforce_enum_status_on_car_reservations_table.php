<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('car_reservations') || ! Schema::hasColumn('car_reservations', 'status')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE car_reservations MODIFY COLUMN status ENUM('active','completed') NOT NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('car_reservations') || ! Schema::hasColumn('car_reservations', 'status')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE car_reservations MODIFY COLUMN status VARCHAR(255) NOT NULL");
        }
    }
};