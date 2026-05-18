<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarMaintenanceLog;
use App\Models\CarReservation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Passwords are the original bcrypt hashes from the Laragon database dump
     * and are inserted directly, bypassing Laravel's hashing cast so the
     * existing credentials remain valid.
     */
    public function run(): void
    {
        // ------------------------------------------------------------------ //
        // Users  (IDs are forced so that foreign-key references in the        //
        // reservations / maintenance logs rows match exactly)                 //
        // ------------------------------------------------------------------ //

        $users = [
            [
                'id'         => 1,
                'name'       => 'Vadiba',
                'email'      => 'vadiba@example.com',
                // bcrypt hash from Laragon dump
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role'       => 'vadiba',
                'created_by' => null,
                'api_token'  => null,
                'created_at' => '2026-04-15 00:00:00',
                'updated_at' => '2026-04-15 00:00:00',
            ],
            [
                'id'         => 3,
                'name'       => 'sanders',
                'email'      => 'test@test.com',
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role'       => 'user',
                'created_by' => 1,
                'api_token'  => null,
                'created_at' => '2026-04-16 00:00:00',
                'updated_at' => '2026-04-16 00:00:00',
            ],
            [
                'id'         => 4,
                'name'       => 'ADMIN',
                'email'      => 'admin@test.com',
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role'       => 'admin',
                'created_by' => 1,
                'api_token'  => null,
                'created_at' => '2026-04-16 00:00:00',
                'updated_at' => '2026-04-16 00:00:00',
            ],
            [
                'id'         => 5,
                'name'       => 'Mushket',
                'email'      => 'mushket@test.com',
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'role'       => 'user',
                'created_by' => 1,
                'api_token'  => null,
                'created_at' => '2026-04-23 00:00:00',
                'updated_at' => '2026-04-23 00:00:00',
            ],
        ];

        // Upsert users keyed on e-mail so re-running the seeder is idempotent.
        // We use DB::table() to bypass the 'hashed' cast and keep the original
        // bcrypt strings intact.
        foreach ($users as $user) {
            DB::table('users')->upsert(
                $user,
                ['email'],
                ['id', 'name', 'password', 'role', 'created_by', 'api_token', 'updated_at']
            );
        }

        // ------------------------------------------------------------------ //
        // Cars                                                                //
        // ------------------------------------------------------------------ //

        $cars = [
            [
                'id'                => 1,
                'brand'             => 'Toyota',
                'model'             => 'Corolla',
                'plate_number'      => 'AB-1234',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2026-04-16 00:00:00',
                'updated_at'        => '2026-04-16 00:00:00',
            ],
            [
                'id'                => 2,
                'brand'             => 'Volkswagen',
                'model'             => 'Passat',
                'plate_number'      => 'CD-5678',
                'transmission_type' => 'Manuālā',
                'image_url'         => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2026-04-16 00:00:00',
                'updated_at'        => '2026-04-16 00:00:00',
            ],
            [
                'id'                => 3,
                'brand'             => 'Skoda',
                'model'             => 'Octavia',
                'plate_number'      => 'EF-9012',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2026-04-16 00:00:00',
                'updated_at'        => '2026-04-16 00:00:00',
            ],
            [
                'id'                => 4,
                'brand'             => 'BMW',
                'model'             => '320d',
                'plate_number'      => 'GH-3456',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2026-04-16 00:00:00',
                'updated_at'        => '2026-04-16 00:00:00',
            ],
            [
                'id'                => 5,
                'brand'             => 'Mercedes-Benz',
                'model'             => 'C-Class',
                'plate_number'      => 'IJ-7890',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2026-04-16 00:00:00',
                'updated_at'        => '2026-04-16 00:00:00',
            ],
        ];

        foreach ($cars as $car) {
            DB::table('cars')->upsert(
                $car,
                ['plate_number'],
                ['id', 'brand', 'model', 'transmission_type', 'image_url', 'status', 'updated_at']
            );
        }

        // ------------------------------------------------------------------ //
        // Car reservations  (16 rows from the Laragon dump)                  //
        // ------------------------------------------------------------------ //

        $reservations = [
            ['id' =>  1, 'car_id' => 1, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-16 08:00:00', 'ended_at' => '2026-04-16 10:00:00', 'created_at' => '2026-04-16 07:50:00', 'updated_at' => '2026-04-16 10:05:00'],
            ['id' =>  2, 'car_id' => 2, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-17 09:00:00', 'ended_at' => '2026-04-17 11:30:00', 'created_at' => '2026-04-17 08:45:00', 'updated_at' => '2026-04-17 11:35:00'],
            ['id' =>  3, 'car_id' => 3, 'user_id' => 5, 'status' => 'completed', 'started_at' => '2026-04-18 07:00:00', 'ended_at' => '2026-04-18 09:00:00', 'created_at' => '2026-04-18 06:55:00', 'updated_at' => '2026-04-18 09:10:00'],
            ['id' =>  4, 'car_id' => 4, 'user_id' => 5, 'status' => 'completed', 'started_at' => '2026-04-19 10:00:00', 'ended_at' => '2026-04-19 12:00:00', 'created_at' => '2026-04-19 09:50:00', 'updated_at' => '2026-04-19 12:05:00'],
            ['id' =>  5, 'car_id' => 5, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-20 08:30:00', 'ended_at' => '2026-04-20 10:30:00', 'created_at' => '2026-04-20 08:20:00', 'updated_at' => '2026-04-20 10:35:00'],
            ['id' =>  6, 'car_id' => 1, 'user_id' => 5, 'status' => 'completed', 'started_at' => '2026-04-21 09:00:00', 'ended_at' => '2026-04-21 11:00:00', 'created_at' => '2026-04-21 08:50:00', 'updated_at' => '2026-04-21 11:10:00'],
            ['id' =>  7, 'car_id' => 2, 'user_id' => 5, 'status' => 'completed', 'started_at' => '2026-04-22 07:30:00', 'ended_at' => '2026-04-22 09:30:00', 'created_at' => '2026-04-22 07:20:00', 'updated_at' => '2026-04-22 09:35:00'],
            ['id' =>  8, 'car_id' => 3, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-23 10:00:00', 'ended_at' => '2026-04-23 12:30:00', 'created_at' => '2026-04-23 09:45:00', 'updated_at' => '2026-04-23 12:35:00'],
            ['id' =>  9, 'car_id' => 4, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-24 08:00:00', 'ended_at' => '2026-04-24 10:00:00', 'created_at' => '2026-04-24 07:55:00', 'updated_at' => '2026-04-24 10:05:00'],
            ['id' => 10, 'car_id' => 5, 'user_id' => 5, 'status' => 'completed', 'started_at' => '2026-04-25 09:30:00', 'ended_at' => '2026-04-25 11:30:00', 'created_at' => '2026-04-25 09:20:00', 'updated_at' => '2026-04-25 11:35:00'],
            ['id' => 11, 'car_id' => 1, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-26 07:00:00', 'ended_at' => '2026-04-26 09:00:00', 'created_at' => '2026-04-26 06:50:00', 'updated_at' => '2026-04-26 09:05:00'],
            ['id' => 12, 'car_id' => 2, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-27 10:00:00', 'ended_at' => '2026-04-27 12:00:00', 'created_at' => '2026-04-27 09:50:00', 'updated_at' => '2026-04-27 12:05:00'],
            ['id' => 13, 'car_id' => 3, 'user_id' => 5, 'status' => 'completed', 'started_at' => '2026-04-28 08:30:00', 'ended_at' => '2026-04-28 10:30:00', 'created_at' => '2026-04-28 08:20:00', 'updated_at' => '2026-04-28 10:35:00'],
            ['id' => 14, 'car_id' => 4, 'user_id' => 5, 'status' => 'completed', 'started_at' => '2026-04-29 09:00:00', 'ended_at' => '2026-04-29 11:00:00', 'created_at' => '2026-04-29 08:50:00', 'updated_at' => '2026-04-29 11:05:00'],
            ['id' => 15, 'car_id' => 5, 'user_id' => 3, 'status' => 'completed', 'started_at' => '2026-04-30 07:30:00', 'ended_at' => '2026-04-30 09:30:00', 'created_at' => '2026-04-30 07:20:00', 'updated_at' => '2026-04-30 09:35:00'],
            ['id' => 16, 'car_id' => 1, 'user_id' => 5, 'status' => 'active',    'started_at' => '2026-05-01 08:00:00', 'ended_at' => null,                  'created_at' => '2026-05-01 07:55:00', 'updated_at' => '2026-05-01 08:00:00'],
        ];

        foreach ($reservations as $reservation) {
            DB::table('car_reservations')->upsert(
                $reservation,
                ['id'],
                ['car_id', 'user_id', 'status', 'started_at', 'ended_at', 'updated_at']
            );
        }

        // ------------------------------------------------------------------ //
        // Car maintenance logs  (2 rows from the Laragon dump)               //
        // ------------------------------------------------------------------ //

        $maintenanceLogs = [
            [
                'id'               => 1,
                'car_id'           => 2,
                'user_id'          => 4,
                'maintenance_type' => 'Eļļas maiņa',
                'description'      => 'Nomainīta motoreļļa un eļļas filtrs. Izmantota 5W-30 sintētiskā eļļa.',
                'performed_at'     => '2026-04-20 10:00:00',
                'mileage'          => 85000,
                'cost'             => '65.00',
                'created_at'       => '2026-04-20 10:30:00',
                'updated_at'       => '2026-04-20 10:30:00',
            ],
            [
                'id'               => 2,
                'car_id'           => 4,
                'user_id'          => 4,
                'maintenance_type' => 'Bremžu pārbaude',
                'description'      => 'Pārbaudītas un nomainītas priekšējās bremžu kluči. Aizmugurējās bremzes vēl labā stāvoklī.',
                'performed_at'     => '2026-04-22 14:00:00',
                'mileage'          => 62000,
                'cost'             => '120.00',
                'created_at'       => '2026-04-22 14:45:00',
                'updated_at'       => '2026-04-22 14:45:00',
            ],
        ];

        foreach ($maintenanceLogs as $log) {
            DB::table('car_maintenance_logs')->upsert(
                $log,
                ['id'],
                ['car_id', 'user_id', 'maintenance_type', 'description', 'performed_at', 'mileage', 'cost', 'updated_at']
            );
        }
    }
}
