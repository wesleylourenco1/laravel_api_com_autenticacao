<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Login do usuário final
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('active', true)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        $token = Str::random(60);
        $expires = now()->addHours(2);

        $userToken = UserToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => $expires
        ]);

        return response()->json([
            'user_token' => $token,
            'expires_at' => $expires->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }

    /**
     * Cria um novo usuário
     * Apenas admins podem criar (via middleware)
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'is_admin' => 'boolean'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => $request->is_admin ?? false,
            'active' => $request->active ?? true
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'user' => $user
        ]);
    }

    /**
     * Atualiza um usuário
     * - Senha: só o próprio usuário
     * - Demais campos: só admin (via middleware)
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Atualização de senha: só o próprio usuário
        if ($request->has('password')) {
            if ($request->user->id !== $user->id) {
                return response()->json(['error' => 'Não pode alterar a senha de outro usuário'], 403);
            }
            $request->validate([
                'password' => 'required|string|min:8',
            ]);
            $user->password = $request->password;
        }

        // Atualização de outros campos: só admin
        if ($request->hasAny(['name', 'email', 'is_admin'])) {
            if (!$request->user->is_admin) {
                return response()->json(['error' => 'Somente admins podem alterar esses dados'], 403);
            }
            $request->validate([
                'name' => 'string',
                'email' => 'email|unique:users,email,' . $user->id,
                'is_admin' => 'boolean'
            ]);
            $user->update($request->only(['name', 'email', 'is_admin']));
        }

        $user->save();

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user' => $user
        ]);
    }

    /**
     * Perfil do usuário autenticado
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user
        ]);
    }
}
