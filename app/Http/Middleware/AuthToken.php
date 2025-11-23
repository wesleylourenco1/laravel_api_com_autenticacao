<?php


namespace App\Http\Middleware;

use Closure;
use App\Models\AccessToken;

class AuthToken
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'Token missing'], 401);
        }

        $token = substr($header, 7);

        $accessToken = AccessToken::where('token', $token)->first();

        if (!$accessToken || $accessToken->isExpired()) {
            return response()->json(['error' => 'Token invalid or expired'], 401);
        }

        $request->merge(['client' => $accessToken->client]);

        return $next($request);
    }
}
