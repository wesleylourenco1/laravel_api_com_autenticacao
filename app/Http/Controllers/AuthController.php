<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\AccessToken;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function generateToken(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'client_secret' => 'required'
        ]);

        $client = Client::where('client_id', $request->client_id)
                        ->where('client_secret', $request->client_secret)
                        ->where('active', true)
                        ->first();

        if (!$client) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = Str::random(60);
        $expires = now()->addHours(1);

        $accessToken = AccessToken::create([
            'client_id' => $client->id,
            'token' => $token,
            'expires_at' => $expires,
        ]);

        return response()->json([
            'token' => $token,
            'expires_at' => $expires->toDateTimeString()
        ]);
    }
}
