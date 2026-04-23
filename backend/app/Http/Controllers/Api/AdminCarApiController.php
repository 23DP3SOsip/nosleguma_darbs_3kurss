<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarReservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCarApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav piekļuves šai sadaļai.',
            ], 403);
        }

        $cars = Car::query()
            ->with(['activeReservation.user:id,name'])
            ->orderBy('brand')
            ->orderBy('model')
            ->get()
            ->map(static function (Car $car): array {
                $activeReservation = $car->activeReservation;

                return [
                    'id' => $car->id,
                    'brand' => $car->brand,
                    'model' => $car->model,
                    'plate_number' => $car->plate_number,
                    'transmission_type' => $car->transmission_type,
                    'image_url' => $car->image_url,
                    'is_reserved' => $activeReservation !== null,
                    'reserved_by' => $activeReservation?->user?->name,
                    'reserved_from' => $activeReservation?->started_at?->toISOString(),
                    'created_at' => $car->created_at,
                ];
            });

        return new JsonResponse([
            'cars' => $cars,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību izveidot automašīnas.',
            ], 403);
        }

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:50', 'unique:cars,plate_number'],
            'transmission_type' => ['required', Rule::in(['Automātiskā', 'Manuālā'])],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ], [
            'brand.required' => 'Lauks "Zīmols" ir obligāts.',
            'model.required' => 'Lauks "Modelis" ir obligāts.',
            'plate_number.required' => 'Lauks "Numurzīme" ir obligāts.',
            'plate_number.unique' => 'Šāda numurzīme jau pastāv.',
            'transmission_type.required' => 'Lauks "Ātrumkārba" ir obligāts.',
            'transmission_type.in' => 'Ātrumkārbas tipam jābūt "Automātiskā" vai "Manuālā".',
            'image_url.url' => 'Ievadiet derīgu attēla URL adresi.',
        ]);

        $car = Car::query()->create($validated);

        return new JsonResponse([
            'message' => 'Automašīna veiksmīgi izveidota.',
            'car' => $car,
        ], 201);
    }

    public function update(Request $request, Car $car): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību rediģēt automašīnas.',
            ], 403);
        }

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:50', Rule::unique('cars', 'plate_number')->ignore($car->id)],
            'transmission_type' => ['required', Rule::in(['Automātiskā', 'Manuālā'])],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ], [
            'brand.required' => 'Lauks "Zīmols" ir obligāts.',
            'model.required' => 'Lauks "Modelis" ir obligāts.',
            'plate_number.required' => 'Lauks "Numurzīme" ir obligāts.',
            'plate_number.unique' => 'Šāda numurzīme jau pastāv.',
            'transmission_type.required' => 'Lauks "Ātrumkārba" ir obligāts.',
            'transmission_type.in' => 'Ātrumkārbas tipam jābūt "Automātiskā" vai "Manuālā".',
            'image_url.url' => 'Ievadiet derīgu attēla URL adresi.',
        ]);

        $car->update($validated);

        return new JsonResponse([
            'message' => 'Automašīna veiksmīgi atjaunota.',
            'car' => $car->fresh(),
        ]);
    }

    public function destroy(Request $request, Car $car): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību dzēst automašīnas.',
            ], 403);
        }

        $hasActiveReservation = CarReservation::query()
            ->where('car_id', $car->id)
            ->where('status', CarReservation::STATUS_ACTIVE)
            ->exists();

        if ($hasActiveReservation) {
            return new JsonResponse([
                'message' => 'Automašīnu ar aktīvu rezervāciju dzēst nedrīkst.',
            ], 422);
        }

        $car->delete();

        return new JsonResponse([
            'message' => 'Automašīna veiksmīgi dzēsta.',
        ]);
    }

    public function reservations(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav piekļuves šai sadaļai.',
            ], 403);
        }

        $reservations = CarReservation::query()
            ->with(['car:id,brand,model,plate_number', 'user:id,name,role'])
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(static function (CarReservation $reservation): array {
                return [
                    'id' => $reservation->id,
                    'status' => $reservation->status,
                    'status_label' => match ($reservation->status) {
                        CarReservation::STATUS_ACTIVE => 'Aktīva',
                        CarReservation::STATUS_COMPLETED => 'Pabeigta',
                        CarReservation::STATUS_CANCELLED => 'Atcelta',
                        default => $reservation->status,
                    },
                    'car' => [
                        'id' => $reservation->car?->id,
                        'brand' => $reservation->car?->brand,
                        'model' => $reservation->car?->model,
                        'plate_number' => $reservation->car?->plate_number,
                    ],
                    'user' => [
                        'id' => $reservation->user?->id,
                        'name' => $reservation->user?->name,
                        'role' => $reservation->user?->role,
                    ],
                    'started_at' => $reservation->started_at?->toISOString(),
                    'ended_at' => $reservation->ended_at?->toISOString(),
                    'created_at' => $reservation->created_at?->toISOString(),
                ];
            });

        return new JsonResponse([
            'reservations' => $reservations,
        ]);
    }
}