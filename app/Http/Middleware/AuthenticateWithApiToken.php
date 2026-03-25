<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autendib API päringud Bearer tokeni või ?api_token query-parameetri kaudu.
 *
 * Kasutus routes/api.php-s:
 *   Route::middleware(\App\Http\Middleware\AuthenticateWithApiToken::class)->group(...)
 */
class AuthenticateWithApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->query('api_token');

        if (! $token) {
            return response()->json([
                'error' => 'API token puudub. Lisa Authorization: Bearer <token> päis.',
            ], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (! $user) {
            return response()->json([
                'error' => 'Vigane API token.',
            ], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}