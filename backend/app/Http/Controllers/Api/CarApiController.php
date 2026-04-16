<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarReservation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor) {
            return new JsonResponse([
                'message' => 'Nepieciešama autorizācija.',
            ], 401);
        }

        $cars = Car::query()
            ->with(['activeReservation.user:id,name,role'])
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $userHasActiveReservation = $actor->role === 'user'
            && $actor->carReservations()->where('status', CarReservation::STATUS_ACTIVE)->exists();

        $payload = $cars->map(static function (Car $car) use ($actor, $userHasActiveReservation): array {
            $activeReservation = $car->activeReservation;
            $isAvailable = $activeReservation === null;
            $ownedByCurrentUser = $activeReservation?->user_id === $actor->id;
            $canReserve = $isAvailable && ($actor->role !== 'user' || ! $userHasActiveReservation);
            $canManageReservation = $activeReservation !== null && (
                $ownedByCurrentUser || in_array($actor->role, ['admin', 'vadiba'], true)
            );

            return [
                'id' => $car->id,
                'brand' => $car->brand,
                'model' => $car->model,
                'display_name' => trim($car->brand.' '.$car->model),
                'plate_number' => $car->plate_number,
                'transmission_type' => $car->transmission_type,
                'image_url' => $car->image_url,
                'status' => $isAvailable ? 'free' : 'reserved',
                'status_label' => $isAvailable ? 'brīva' : 'rezervēta',
                'is_available' => $isAvailable,
                'can_reserve' => $canReserve,
                'can_complete' => $canManageReservation,
                'active_reservation' => $activeReservation ? [
                    'id' => $activeReservation->id,
                    'started_at' => $activeReservation->started_at?->toISOString(),
                    'ended_at' => $activeReservation->ended_at?->toISOString(),
                    'user' => [
                        'id' => $activeReservation->user?->id,
                        'name' => $activeReservation->user?->name,
                        'role' => $activeReservation->user?->role,
                    ],
                ] : null,
            ];
        });

        return new JsonResponse([
            'cars' => $payload,
            'summary' => [
                'total' => $cars->count(),
                'free' => $cars->filter(static fn (Car $car): bool => $car->activeReservation === null)->count(),
                'reserved' => $cars->filter(static fn (Car $car): bool => $car->activeReservation !== null)->count(),
            ],
        ]);
    }

    public function reserve(Request $request, Car $car): JsonResponse
    {
        $actor = $request->user();

        if (! $actor) {
            return new JsonResponse([
                'message' => 'Nepieciešama autorizācija.',
            ], 401);
        }

        $result = DB::transaction(function () use ($actor, $car): ?JsonResponse {
            $car = Car::query()
                ->with('activeReservation')
                ->whereKey($car->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($car->activeReservation) {
                return new JsonResponse([
                    'message' => 'Šī automašīna jau ir rezervēta.',
                ], 409);
            }

            if ($actor->role === 'user') {
                $hasActiveReservation = CarReservation::query()
                    ->where('user_id', $actor->id)
                    ->where('status', CarReservation::STATUS_ACTIVE)
                    ->lockForUpdate()
                    ->exists();

                if ($hasActiveReservation) {
                    return new JsonResponse([
                        'message' => 'Lietotājam var būt tikai viena aktīva rezervācija. Atceliet vai pabeidziet esošo rezervāciju.',
                    ], 422);
                }
            }

            CarReservation::query()->create([
                'car_id' => $car->id,
                'user_id' => $actor->id,
                'status' => CarReservation::STATUS_ACTIVE,
                'started_at' => now(),
            ]);

            return new JsonResponse([
                'message' => 'Automobilis veiksmīgi rezervēts.',
            ]);
        });

        return $result ?? new JsonResponse([
            'message' => 'Neizdevās rezervēt automobili.',
        ], 500);
    }

    public function complete(Request $request, Car $car): JsonResponse
    {
        return $this->finishReservation($request, $car, CarReservation::STATUS_COMPLETED, 'Rezervācija pabeigta.');
    }

    private function finishReservation(Request $request, Car $car, string $status, string $message): JsonResponse
    {
        $actor = $request->user();

        if (! $actor) {
            return new JsonResponse([
                'message' => 'Nepieciešama autorizācija.',
            ], 401);
        }

        $reservation = DB::transaction(function () use ($actor, $car, $status): ?CarReservation {
            $reservation = CarReservation::query()
                ->with('user')
                ->where('car_id', $car->id)
                ->where('status', CarReservation::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $reservation) {
                return null;
            }

            $canManage = $reservation->user_id === $actor->id || in_array($actor->role, ['admin', 'vadiba'], true);

            if (! $canManage) {
                return null;
            }

            $reservation->update([
                'status' => $status,
                'ended_at' => now(),
            ]);

            return $reservation;
        });

        if (! $reservation) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību mainīt šo rezervāciju vai aktīva rezervācija nav atrasta.',
            ], 403);
        }

        return new JsonResponse([
            'message' => $message,
        ]);
    }
}