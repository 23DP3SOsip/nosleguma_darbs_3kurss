<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CarReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_only_one_active_reservation(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $carOne = $this->createCar('AB-1000');
        $carTwo = $this->createCar('AB-2000');

        $this->actingWithApiToken($user)
            ->postJson('/api/cars/'.$carOne->id.'/reserve')
            ->assertOk()
            ->assertJson(['message' => 'Automobilis veiksmīgi rezervēts.']);

        $this->actingWithApiToken($user)
            ->postJson('/api/cars/'.$carTwo->id.'/reserve')
            ->assertStatus(422)
            ->assertJson(['message' => 'Lietotājam var būt tikai viena aktīva rezervācija. Atceliet vai pabeidziet esošo rezervāciju.']);

        $this->assertDatabaseHas('car_reservations', [
            'car_id' => $carOne->id,
            'user_id' => $user->id,
            'status' => CarReservation::STATUS_ACTIVE,
        ]);
    }

    public function test_reserved_car_cannot_be_taken_by_another_user(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $car = $this->createCar('AB-3000');

        $this->actingWithApiToken($owner)
            ->postJson('/api/cars/'.$car->id.'/reserve')
            ->assertOk();

        $this->actingWithApiToken($otherUser)
            ->postJson('/api/cars/'.$car->id.'/reserve')
            ->assertStatus(409)
            ->assertJson(['message' => 'Šī automašīna jau ir rezervēta.']);
    }

    public function test_completing_reservation_releases_car(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $car = $this->createCar('AB-4000');

        $this->actingWithApiToken($user)
            ->postJson('/api/cars/'.$car->id.'/reserve')
            ->assertOk();

        $this->actingWithApiToken($user)
            ->postJson('/api/cars/'.$car->id.'/complete')
            ->assertOk()
            ->assertJson(['message' => 'Rezervācija pabeigta.']);

        $this->assertDatabaseHas('car_reservations', [
            'car_id' => $car->id,
            'user_id' => $user->id,
            'status' => CarReservation::STATUS_COMPLETED,
        ]);

        $this->actingWithApiToken($otherUser)
            ->postJson('/api/cars/'.$car->id.'/reserve')
            ->assertOk();
    }

    public function test_admin_can_reserve_multiple_cars(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $carOne = $this->createCar('AB-5000');
        $carTwo = $this->createCar('AB-6000');

        $this->actingWithApiToken($admin)
            ->postJson('/api/cars/'.$carOne->id.'/reserve')
            ->assertOk();

        $this->actingWithApiToken($admin)
            ->postJson('/api/cars/'.$carTwo->id.'/reserve')
            ->assertOk();

        $this->assertDatabaseHas('car_reservations', [
            'car_id' => $carOne->id,
            'user_id' => $admin->id,
            'status' => CarReservation::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('car_reservations', [
            'car_id' => $carTwo->id,
            'user_id' => $admin->id,
            'status' => CarReservation::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_set_car_to_maintenance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $car = $this->createCar('AB-7000');

        $this->actingWithApiToken($admin)
            ->putJson('/api/admin/cars/'.$car->id, [
                'brand' => $car->brand,
                'model' => $car->model,
                'plate_number' => $car->plate_number,
                'transmission_type' => $car->transmission_type,
                'image_url' => $car->image_url,
                'status' => Car::STATUS_MAINTENANCE,
            ])
            ->assertOk()
            ->assertJson(['message' => 'Automašīna veiksmīgi atjaunota.']);

        $this->assertDatabaseHas('cars', [
            'id' => $car->id,
            'status' => Car::STATUS_MAINTENANCE,
        ]);
    }

    public function test_car_in_maintenance_cannot_be_reserved(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $car = $this->createCar('AB-8000', Car::STATUS_MAINTENANCE);

        $this->actingWithApiToken($user)
            ->postJson('/api/cars/'.$car->id.'/reserve')
            ->assertStatus(422)
            ->assertJson(['message' => 'Šī automašīna šobrīd ir apkalpošanā un to nevar rezervēt.']);
    }

    private function createCar(string $plateNumber, string $status = Car::STATUS_AVAILABLE): Car
    {
        return Car::query()->create([
            'brand' => 'Test',
            'model' => 'Car',
            'plate_number' => $plateNumber,
            'transmission_type' => 'Automātiskā',
            'image_url' => 'https://example.com/car.jpg',
            'status' => $status,
        ]);
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