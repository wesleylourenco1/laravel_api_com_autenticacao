<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserToken;

class AuthUser
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'User token missing'], 401);
        }

        $token = substr($header, 7);

        $userToken = UserToken::with('user')->where('token', $token)->first();

        if (!$userToken) {
            return response()->json(['error' => 'User token invalid'], 401);
        }

        if ($userToken->expires_at && $userToken->expires_at->isPast()) {
            return response()->json(['error' => 'User token expired'], 401);
        }

        $user = $userToken->user;

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado para esse token'], 404);
        }

        // Define o user resolver do Laravel
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
