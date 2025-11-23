<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitialSetupSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::create([
            'name' => 'Sistema Interno',
            'client_id' => bin2hex(random_bytes(8)),
            'client_secret' => hash('sha256', bin2hex(random_bytes(32))),
            'active' => true
        ]);

        $user = User::create([
            'name' => 'Administrador',
            'email' => 'admin@email.com',
            'password' => '12345678',
            'is_admin' => true,
            'active' => true
        ]);

        $this->command->info("Client ID: {$client->client_id}");
        $this->command->info("Client Secret (plain): [salve antes de rodar]");
        $this->command->info("User email: {$user->email}");
    }
}
