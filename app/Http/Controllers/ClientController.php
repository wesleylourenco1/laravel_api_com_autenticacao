<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Cria um novo client.
     * Apenas usuários admin acessam essa rota (via middleware)
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'active' => 'boolean'
        ]);

        $newClientId = bin2hex(random_bytes(8));
        $newClientSecret = bin2hex(random_bytes(32));

        $client = Client::create([
            'name' => $request->name,
            'client_id' => $newClientId,
            'client_secret' => hash('sha256', $newClientSecret),
            'active' => $request->active ?? true,
        ]);

        return response()->json([
            'message' => 'Cliente criado com sucesso.',
            'client_id' => $newClientId,
            'client_secret' => $newClientSecret
        ]);
    }

    /**
     * Atualiza o client_secret de um client existente.
     * Qualquer usuário admin pode acessar via middleware
     */
    public function updateSecret(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $newSecret = bin2hex(random_bytes(32));
        $client->client_secret = hash('sha256', $newSecret);
        $client->save();

        return response()->json([
            'message' => 'Client secret atualizado com sucesso.',
            'client_secret' => $newSecret
        ]);
    }

    /**
     * Atualiza dados do client (nome, ativo)
     * Apenas admin via middleware
     */
    public function updateClient(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $request->validate([
            'name' => 'string',
            'active' => 'boolean',
        ]);

        $client->update($request->only(['name', 'active']));

        return response()->json([
            'message' => 'Cliente atualizado com sucesso.',
            'client' => $client
        ]);
    }
}
