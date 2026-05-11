<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarMaintenanceLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CarMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_list_maintenance_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $car = Car::query()->create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'plate_number' => 'AB-7777',
            'transmission_type' => 'Automātiskā',
            'image_url' => null,
        ]);

        $this->actingWithApiToken($admin)
            ->postJson('/api/admin/maintenance', [
                'car_id' => $car->id,
                'maintenance_type' => 'TO',
                'description' => 'Eļļas maiņa un filtru pārbaude',
                'performed_at' => '2026-04-23 10:00:00',
                'mileage' => 12345,
                'cost' => 89.50,
            ])
            ->assertCreated()
            ->assertJson(['message' => 'Apkopes ieraksts veiksmīgi pievienots.']);

        $this->assertDatabaseHas('car_maintenance_logs', [
            'car_id' => $car->id,
            'user_id' => $admin->id,
            'maintenance_type' => 'TO',
            'mileage' => 12345,
        ]);

        $this->actingWithApiToken($admin)
            ->getJson('/api/admin/maintenance')
            ->assertOk()
            ->assertJsonPath('logs.0.car.plate_number', 'AB-7777')
            ->assertJsonPath('logs.0.maintenance_type', 'TO');
    }

    private function actingWithApiToken(User $user): self
    {
        $plainToken = Str::random(60);

        $user->forceFill([
            'api_token' => hash('sha256', $plainToken),
        ])->save();

        return $this->withHeader('Authorization', 'Bearer '.$plainToken)
            ->withHeader('Accept', 'application/json');
    }
}