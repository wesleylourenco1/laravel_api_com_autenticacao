<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        // Registrar alias de middleware para uso nas rotas
        $middleware->alias([
            'auth.client' => App\Http\Middleware\AuthClient::class,
            'auth.user' => App\Http\Middleware\AuthUser::class,
            'auth.admin' => App\Http\Middleware\AuthAdmin::class,
            'cleanup' => \App\Http\Middleware\CheckCleanup::class, // alias
        ]);

        // Se quiser o middleware em TODAS as rotas (global), use append
        $middleware->append(\App\Http\Middleware\CheckCleanup::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sempre renderizar JSON
        $exceptions->shouldRenderJsonWhen(fn($request, $e) => true);

        // Captura NotFoundHttpException (rota não encontrada)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return response()->json([
                'error' => 'Rota não encontrada',
                'method' => $request->method(),
                'url' => $request->fullUrl()
            ], 404);
        });

        // Captura UnauthorizedHttpException (não autorizado)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $e, $request) {
            return response()->json([
                'error' => 'Não autorizado'
            ], 401);
        });

        // Captura outras exceções genéricas
        $exceptions->render(function (\Throwable $e, $request) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        });
    })
    ->create();
