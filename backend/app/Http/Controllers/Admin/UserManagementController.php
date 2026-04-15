<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.panelis', [
            'users' => User::query()->with('createdBy')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $creator = $request->user();

        if (! $creator) {
            return redirect()->route('pieslegties');
        }

        $allowedRoles = $this->allowedCreateRoles($creator->role);

        if ($allowedRoles === []) {
            return back()->with('kluda', 'Jums nav tiesību izveidot lietotājus.');
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

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'created_by' => $creator->id,
        ]);

        return back()->with('statuss', 'Lietotājs veiksmīgi izveidots.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if (! $actor) {
            return redirect()->route('pieslegties');
        }

        if ($actor->id === $user->id) {
            return back()->with('kluda', 'Jūs nevarat dzēst pats sevi.');
        }

        if (! $this->canDelete($actor->role, $user->role)) {
            return back()->with('kluda', 'Jums nav tiesību dzēst šo lietotāju.');
        }

        if ($user->role === 'vadiba' && User::query()->where('role', 'vadiba')->count() <= 1) {
            return back()->with('kluda', 'Vienīgo vadiba kontu dzēst nedrīkst.');
        }

        $user->delete();

        return back()->with('statuss', 'Lietotājs veiksmīgi dzēsts.');
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
