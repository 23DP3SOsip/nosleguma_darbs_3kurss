<?php

namespace Database\Seeders;

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
    }
}
