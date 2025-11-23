<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\FailoverBanco;



// Todas as rotas explícitas vão aqui
// Por exemplo, se quiser permitir a raiz apenas para admins ou sistema interno, crie rota específica:
// Route::get('/', [HomeController::class, 'index']);

// Fallback para todas as outras rotas (incluindo / se não houver rota definida)
Route::fallback(function (\Illuminate\Http\Request $request) {
    return response()->json([
        'error' => 'Rota não encontrada',
        'method' => $request->method(),
        'url' => $request->fullUrl()
    ], 404);
});