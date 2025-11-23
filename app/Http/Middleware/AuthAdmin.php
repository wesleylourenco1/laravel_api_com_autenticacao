<?php

namespace App\Http\Middleware;

use Closure;

class AuthAdmin
{
    public function handle($request, Closure $next)
    {
        $user = $request->get('user');

        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Admin privileges required'], 403);
        }

        return $next($request);
    }
}
