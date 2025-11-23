<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AccessToken;

class AuthClient
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'Client token missing'], 401);
        }

        $token = substr($header, 7);

        $accessToken = AccessToken::where('token', $token)->first();

       if (!$accessToken || $accessToken->isExpired()) {

            return response()->json(['error' => 'Client token invalid or expired'], 401);
        }

        // Passa o client para as rotas
        $request->merge(['client' => $accessToken->client]);

        return $next($request);
    }
}
