<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Rotas do Client
|--------------------------------------------------------------------------
| Cliente do sistema: envia client_id + client_secret para gerar token
*/
Route::prefix('client')->group(function () {
    // Gera token temporário do client
    Route::get('/auth/token', [AuthController::class, 'generateToken']);

    // Rotas protegidas pelo token do client
    Route::middleware('auth.client')->group(function () {
 
        Route::get('/ping', function () {
            return response()->json([
                'ping' => true,
                'timestamp' => now()
            ]);
        });


    });
});

/*
|--------------------------------------------------------------------------
| Rotas do Usuário final
|--------------------------------------------------------------------------
*/
Route::prefix('user')->group(function () {

    // Login do usuário final (user_token)
    Route::post('/login', [UserController::class, 'login']);

    // Rotas protegidas por token do usuário
    Route::middleware('auth.user')->group(function () {
        // Perfil próprio
        Route::get('/profile', [UserController::class, 'profile']);

        // Atualizar próprio usuário (senha ou dados limitados)
        Route::patch('/{id}', [UserController::class, 'updateUser']);
    });
Route::get('/teste', function () {
    return response()->json([
        'message' => 'Usuário autenticado! Token válido.',
        'user' => request()->user()
    ]);
})->middleware('auth.user');



    
});





/*
|--------------------------------------------------------------------------
| Rotas do Usuário Admin
|--------------------------------------------------------------------------
| Admin pode gerenciar clientes e usuários
*/
Route::middleware(['auth.user', 'auth.admin'])->prefix('admin')->group(function () {
    // Gerenciar clientes
    Route::patch('/clients/{id}', [ClientController::class, 'updateClient']);

    // Gerenciar usuários
    Route::post('/users', [UserController::class, 'createUser']);
    Route::patch('/users/{id}', [UserController::class, 'updateUser']);
});

/*
|--------------------------------------------------------------------------
| Fallback JSON para rotas não existentes
|--------------------------------------------------------------------------
*/
Route::fallback(function (\Illuminate\Http\Request $request) {
    return response()->json([
        'error' => 'Rota não encontrada',
        'method' => $request->method(),
        'url' => $request->fullUrl()
    ], 404);
});
