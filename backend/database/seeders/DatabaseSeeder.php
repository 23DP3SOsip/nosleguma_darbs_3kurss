<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'vadiba@example.com'],
            [
                'name' => 'Vadiba',
                'password' => Hash::make('Vadiba123!'),
                'role' => 'vadiba',
                'created_by' => null,
            ]
        );

        $cars = [
            [
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'plate_number' => 'AB-1234',
                'transmission_type' => 'Automātiskā',
                'image_url' => 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'brand' => 'Volkswagen',
                'model' => 'Passat',
                'plate_number' => 'CD-5678',
                'transmission_type' => 'Manuālā',
                'image_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'brand' => 'Skoda',
                'model' => 'Octavia',
                'plate_number' => 'EF-9012',
                'transmission_type' => 'Automātiskā',
                'image_url' => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'brand' => 'BMW',
                'model' => '320d',
                'plate_number' => 'GH-3456',
                'transmission_type' => 'Automātiskā',
                'image_url' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        foreach ($cars as $car) {
            Car::query()->updateOrCreate(
                ['plate_number' => $car['plate_number']],
                $car
            );
        }
    }
}
