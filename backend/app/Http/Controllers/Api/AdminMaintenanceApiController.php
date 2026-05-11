<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarMaintenanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMaintenanceApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav piekļuves šai sadaļai.',
            ], 403);
        }

        $logs = CarMaintenanceLog::query()
            ->with(['car:id,brand,model,plate_number', 'user:id,name,role'])
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(static function (CarMaintenanceLog $log): array {
                return [
                    'id' => $log->id,
                    'maintenance_type' => $log->maintenance_type,
                    'description' => $log->description,
                    'performed_at' => $log->performed_at?->toISOString(),
                    'mileage' => $log->mileage,
                    'cost' => $log->cost,
                    'car' => [
                        'id' => $log->car?->id,
                        'brand' => $log->car?->brand,
                        'model' => $log->car?->model,
                        'plate_number' => $log->car?->plate_number,
                    ],
                    'user' => [
                        'id' => $log->user?->id,
                        'name' => $log->user?->name,
                        'role' => $log->user?->role,
                    ],
                    'created_at' => $log->created_at?->toISOString(),
                ];
            });

        return new JsonResponse([
            'logs' => $logs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību pievienot apkopes ierakstus.',
            ], 403);
        }

        $validated = $request->validate([
            'car_id' => ['required', 'integer', 'exists:cars,id'],
            'maintenance_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:4000'],
            'performed_at' => ['required', 'date'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ], [
            'car_id.required' => 'Izvēlieties automašīnu.',
            'car_id.exists' => 'Izvēlētā automašīna neeksistē.',
            'maintenance_type.required' => 'Lauks "Apkopes veids" ir obligāts.',
            'description.required' => 'Lauks "Apraksts" ir obligāts.',
            'performed_at.required' => 'Lauks "Datums" ir obligāts.',
            'performed_at.date' => 'Ievadiet derīgu datumu.',
            'mileage.integer' => 'Nobraukumam jābūt skaitlim.',
            'mileage.min' => 'Nobraukums nevar būt negatīvs.',
            'cost.numeric' => 'Summai jābūt skaitlim.',
            'cost.min' => 'Summa nevar būt negatīva.',
        ]);

        $log = CarMaintenanceLog::query()->create([
            'car_id' => $validated['car_id'],
            'user_id' => $actor->id,
            'maintenance_type' => $validated['maintenance_type'],
            'description' => $validated['description'],
            'performed_at' => $validated['performed_at'],
            'mileage' => $validated['mileage'] ?? null,
            'cost' => $validated['cost'] ?? null,
        ]);

        return new JsonResponse([
            'message' => 'Apkopes ieraksts veiksmīgi pievienots.',
            'log' => $log,
        ], 201);
    }

    public function destroy(Request $request, CarMaintenanceLog $log): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību dzēst apkopes ierakstus.',
            ], 403);
        }

        $log->delete();

        return new JsonResponse([
            'message' => 'Apkopes ieraksts dzēsts.',
        ]);
    }
}