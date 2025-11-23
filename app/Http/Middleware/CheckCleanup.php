<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\CleanupService;

class CheckCleanup
{
    public function handle($request, Closure $next)
    {
        $cleanup = new CleanupService();
        $cleanup->runIfDue(); // executa limpeza se necessário

        return $next($request);
    }
}
