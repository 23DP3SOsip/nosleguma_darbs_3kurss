<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return new JsonResponse([
                'message' => 'Nepieciešama autorizācija.',
            ], 401);
        }

        $hashedToken = hash('sha256', $token);
        $user = User::query()->where('api_token', $hashedToken)->first();

        if (! $user) {
            return new JsonResponse([
                'message' => 'Nederīgs piekļuves tokens.',
            ], 401);
        }

        auth()->setUser($user);
        $request->setUserResolver(static fn (): User => $user);

        return $next($request);
    }
}
