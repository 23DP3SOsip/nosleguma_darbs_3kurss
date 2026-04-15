<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  array<int, string>  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('pieslegties');
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()->route('sakums')->with('kluda', 'Jums nav pieejas šai sadaļai.');
        }

        return $next($request);
    }
}
