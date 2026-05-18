<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarMaintenanceLog;
use App\Models\CarReservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ----------------------------------------------------------------
        // Users
        // ----------------------------------------------------------------
        // Passwords are the bcrypt hashes taken directly from the SQL dump.
        // api_token is not in $fillable, so we upsert via DB::table().
        $users = [
            [
                'id'         => 1,
                'name'       => 'vadiba',
                'email'      => 'vadiba@example.com',
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // Vadiba123!
                'role'       => 'vadiba',
                'created_by' => null,
                'api_token'  => null,
                'created_at' => '2025-04-15 10:00:00',
                'updated_at' => '2025-04-15 10:00:00',
            ],
            [
                'id'         => 2,
                'name'       => 'sanders',
                'email'      => 'sanders@example.com',
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // Sanders123!
                'role'       => 'user',
                'created_by' => 1,
                'api_token'  => null,
                'created_at' => '2025-04-15 10:05:00',
                'updated_at' => '2025-04-15 10:05:00',
            ],
            [
                'id'         => 3,
                'name'       => 'admin',
                'email'      => 'admin@example.com',
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // Admin123!
                'role'       => 'admin',
                'created_by' => 1,
                'api_token'  => null,
                'created_at' => '2025-04-15 10:10:00',
                'updated_at' => '2025-04-15 10:10:00',
            ],
            [
                'id'         => 4,
                'name'       => 'mushket',
                'email'      => 'mushket@example.com',
                'password'   => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // Mushket123!
                'role'       => 'user',
                'created_by' => 1,
                'api_token'  => null,
                'created_at' => '2025-04-15 10:15:00',
                'updated_at' => '2025-04-15 10:15:00',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->upsert(
                $user,
                ['id'],
                ['name', 'email', 'password', 'role', 'created_by', 'api_token', 'updated_at']
            );
        }

        // ----------------------------------------------------------------
        // Cars
        // ----------------------------------------------------------------
        $cars = [
            [
                'id'                => 1,
                'brand'             => 'Toyota',
                'model'             => 'Corolla',
                'plate_number'      => 'AB-1234',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2025-04-16 09:00:00',
                'updated_at'        => '2025-04-16 09:00:00',
            ],
            [
                'id'                => 2,
                'brand'             => 'Volkswagen',
                'model'             => 'Passat',
                'plate_number'      => 'CD-5678',
                'transmission_type' => 'Manuālā',
                'image_url'         => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2025-04-16 09:05:00',
                'updated_at'        => '2025-04-16 09:05:00',
            ],
            [
                'id'                => 3,
                'brand'             => 'Skoda',
                'model'             => 'Octavia',
                'plate_number'      => 'EF-9012',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2025-04-16 09:10:00',
                'updated_at'        => '2025-04-16 09:10:00',
            ],
            [
                'id'                => 4,
                'brand'             => 'BMW',
                'model'             => '320d',
                'plate_number'      => 'GH-3456',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'available',
                'created_at'        => '2025-04-16 09:15:00',
                'updated_at'        => '2025-04-16 09:15:00',
            ],
            [
                'id'                => 5,
                'brand'             => 'Mercedes-Benz',
                'model'             => 'C-Class',
                'plate_number'      => 'IJ-7890',
                'transmission_type' => 'Automātiskā',
                'image_url'         => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=80',
                'status'            => 'maintenance',
                'created_at'        => '2025-04-16 09:20:00',
                'updated_at'        => '2025-04-16 09:20:00',
            ],
        ];

        foreach ($cars as $car) {
            Car::query()->upsert(
                $car,
                ['id'],
                ['brand', 'model', 'plate_number', 'transmission_type', 'image_url', 'status', 'updated_at']
            );
        }

        // ----------------------------------------------------------------
        // Car Reservations (16 records)
        // ----------------------------------------------------------------
        $reservations = [
            // Car 1 – Toyota Corolla
            ['id' =>  1, 'car_id' => 1, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-01-05 08:00:00', 'ended_at' => '2025-01-07 18:00:00', 'created_at' => '2025-01-04 12:00:00', 'updated_at' => '2025-01-07 18:00:00'],
            ['id' =>  2, 'car_id' => 1, 'user_id' => 4, 'status' => 'completed', 'started_at' => '2025-01-20 09:00:00', 'ended_at' => '2025-01-22 17:00:00', 'created_at' => '2025-01-19 10:00:00', 'updated_at' => '2025-01-22 17:00:00'],
            ['id' =>  3, 'car_id' => 1, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-02-10 08:00:00', 'ended_at' => '2025-02-12 18:00:00', 'created_at' => '2025-02-09 11:00:00', 'updated_at' => '2025-02-12 18:00:00'],
            ['id' =>  4, 'car_id' => 1, 'user_id' => 4, 'status' => 'active',    'started_at' => '2025-04-20 08:00:00', 'ended_at' => null,                   'created_at' => '2025-04-19 09:00:00', 'updated_at' => '2025-04-20 08:00:00'],

            // Car 2 – Volkswagen Passat
            ['id' =>  5, 'car_id' => 2, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-01-15 10:00:00', 'ended_at' => '2025-01-17 16:00:00', 'created_at' => '2025-01-14 08:00:00', 'updated_at' => '2025-01-17 16:00:00'],
            ['id' =>  6, 'car_id' => 2, 'user_id' => 4, 'status' => 'completed', 'started_at' => '2025-02-01 09:00:00', 'ended_at' => '2025-02-03 17:00:00', 'created_at' => '2025-01-31 10:00:00', 'updated_at' => '2025-02-03 17:00:00'],
            ['id' =>  7, 'car_id' => 2, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-03-05 08:00:00', 'ended_at' => '2025-03-07 18:00:00', 'created_at' => '2025-03-04 09:00:00', 'updated_at' => '2025-03-07 18:00:00'],
            ['id' =>  8, 'car_id' => 2, 'user_id' => 4, 'status' => 'active',    'started_at' => '2025-04-22 10:00:00', 'ended_at' => null,                   'created_at' => '2025-04-21 11:00:00', 'updated_at' => '2025-04-22 10:00:00'],

            // Car 3 – Skoda Octavia
            ['id' =>  9, 'car_id' => 3, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-01-25 08:00:00', 'ended_at' => '2025-01-27 17:00:00', 'created_at' => '2025-01-24 10:00:00', 'updated_at' => '2025-01-27 17:00:00'],
            ['id' => 10, 'car_id' => 3, 'user_id' => 4, 'status' => 'completed', 'started_at' => '2025-02-15 09:00:00', 'ended_at' => '2025-02-17 18:00:00', 'created_at' => '2025-02-14 08:00:00', 'updated_at' => '2025-02-17 18:00:00'],
            ['id' => 11, 'car_id' => 3, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-03-10 08:00:00', 'ended_at' => '2025-03-12 17:00:00', 'created_at' => '2025-03-09 09:00:00', 'updated_at' => '2025-03-12 17:00:00'],
            ['id' => 12, 'car_id' => 3, 'user_id' => 4, 'status' => 'active',    'started_at' => '2025-04-25 09:00:00', 'ended_at' => null,                   'created_at' => '2025-04-24 10:00:00', 'updated_at' => '2025-04-25 09:00:00'],

            // Car 4 – BMW 320d
            ['id' => 13, 'car_id' => 4, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-02-05 08:00:00', 'ended_at' => '2025-02-07 18:00:00', 'created_at' => '2025-02-04 09:00:00', 'updated_at' => '2025-02-07 18:00:00'],
            ['id' => 14, 'car_id' => 4, 'user_id' => 4, 'status' => 'completed', 'started_at' => '2025-03-01 09:00:00', 'ended_at' => '2025-03-03 17:00:00', 'created_at' => '2025-02-28 10:00:00', 'updated_at' => '2025-03-03 17:00:00'],
            ['id' => 15, 'car_id' => 4, 'user_id' => 2, 'status' => 'completed', 'started_at' => '2025-03-20 08:00:00', 'ended_at' => '2025-03-22 18:00:00', 'created_at' => '2025-03-19 09:00:00', 'updated_at' => '2025-03-22 18:00:00'],
            ['id' => 16, 'car_id' => 4, 'user_id' => 4, 'status' => 'active',    'started_at' => '2025-04-28 10:00:00', 'ended_at' => null,                   'created_at' => '2025-04-27 11:00:00', 'updated_at' => '2025-04-28 10:00:00'],
        ];

        foreach ($reservations as $reservation) {
            CarReservation::query()->upsert(
                $reservation,
                ['id'],
                ['car_id', 'user_id', 'status', 'started_at', 'ended_at', 'updated_at']
            );
        }

        // ----------------------------------------------------------------
        // Car Maintenance Logs (2 records)
        // ----------------------------------------------------------------
        $maintenanceLogs = [
            [
                'id'               => 1,
                'car_id'           => 5,
                'user_id'          => 3,
                'maintenance_type' => 'Eļļas maiņa',
                'description'      => 'Veikta eļļas un eļļas filtra maiņa. Nomainīts arī gaisa filtrs.',
                'performed_at'     => '2025-04-10 10:00:00',
                'mileage'          => 85000,
                'cost'             => '75.00',
                'created_at'       => '2025-04-10 10:30:00',
                'updated_at'       => '2025-04-10 10:30:00',
            ],
            [
                'id'               => 2,
                'car_id'           => 5,
                'user_id'          => 3,
                'maintenance_type' => 'Bremžu pārbaude',
                'description'      => 'Veikta bremžu sistēmas pilna pārbaude. Nomainīti priekšējie bremžu diski un kluči.',
                'performed_at'     => '2025-04-18 14:00:00',
                'mileage'          => 85200,
                'cost'             => '210.00',
                'created_at'       => '2025-04-18 15:00:00',
                'updated_at'       => '2025-04-18 15:00:00',
            ],
        ];

        foreach ($maintenanceLogs as $log) {
            CarMaintenanceLog::query()->upsert(
                $log,
                ['id'],
                ['car_id', 'user_id', 'maintenance_type', 'description', 'performed_at', 'mileage', 'cost', 'updated_at']
            );
        }
    }
}
