<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor || ! in_array($actor->role, ['admin', 'vadiba'], true)) {
            return new JsonResponse([
                'message' => 'Jums nav piekļuves šai sadaļai.',
            ], 403);
        }

        $users = User::query()
            ->with('createdBy:id,name')
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'role', 'created_by', 'created_at'])
            ->map(static function (User $user): array {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_by' => $user->created_by,
                    'created_by_name' => $user->createdBy?->name,
                    'created_at' => $user->created_at,
                ];
            });

        return new JsonResponse([
            'users' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $creator = $request->user();

        if (! $creator) {
            return new JsonResponse([
                'message' => 'Nepieciešama autorizācija.',
            ], 401);
        }

        $allowedRoles = $this->allowedCreateRoles($creator->role);

        if ($allowedRoles === []) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību izveidot lietotājus.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
        ], [
            'name.required' => 'Lauks "Vārds" ir obligāts.',
            'name.max' => 'Lauks "Vārds" nedrīkst būt garāks par 255 simboliem.',
            'email.required' => 'Lauks "E-pasts" ir obligāts.',
            'email.email' => 'Ievadiet derīgu e-pasta adresi.',
            'email.max' => 'Lauks "E-pasts" nedrīkst būt garāks par 255 simboliem.',
            'email.unique' => 'Šis e-pasts jau tiek izmantots.',
            'password.required' => 'Lauks "Parole" ir obligāts.',
            'password.min' => 'Parolei jābūt vismaz 8 simbolus garai.',
            'role.required' => 'Izvēlieties lietotāja lomu.',
            'role.in' => 'Izvēlētā loma nav atļauta.',
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'created_by' => $creator->id,
        ]);

        return new JsonResponse([
            'message' => 'Lietotājs veiksmīgi izveidots.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_by' => $user->created_by,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        if (! $actor) {
            return new JsonResponse([
                'message' => 'Nepieciešama autorizācija.',
            ], 401);
        }

        if ($actor->id === $user->id) {
            return new JsonResponse([
                'message' => 'Jūs nevarat dzēst pats sevi.',
            ], 422);
        }

        if (! $this->canDelete($actor->role, $user->role)) {
            return new JsonResponse([
                'message' => 'Jums nav tiesību dzēst šo lietotāju.',
            ], 403);
        }

        if ($user->role === 'vadiba' && User::query()->where('role', 'vadiba')->count() <= 1) {
            return new JsonResponse([
                'message' => 'Vienīgo vadiba kontu dzēst nedrīkst.',
            ], 422);
        }

        $user->delete();

        return new JsonResponse([
            'message' => 'Lietotājs veiksmīgi dzēsts.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function allowedCreateRoles(string $role): array
    {
        return match ($role) {
            'vadiba' => ['admin', 'user'],
            'admin' => ['user'],
            default => [],
        };
    }

    private function canDelete(string $actorRole, string $targetRole): bool
    {
        return match ($actorRole) {
            'vadiba' => in_array($targetRole, ['admin', 'user'], true),
            'admin' => $targetRole === 'user',
            default => false,
        };
    }
}
