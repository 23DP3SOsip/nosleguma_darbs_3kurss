<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cars')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE cars ADD COLUMN status ENUM('available','maintenance') NOT NULL DEFAULT 'available' AFTER image_url");

            return;
        }

        Schema::table('cars', function (Blueprint $table): void {
            $table->string('status')->default('available')->after('image_url');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cars') || ! Schema::hasColumn('cars', 'status')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE cars DROP COLUMN status');

            return;
        }

        Schema::table('cars', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};