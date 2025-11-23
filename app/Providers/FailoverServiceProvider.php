<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Helpers\FailoverBanco;

class FailoverServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registra o FailoverBanco como singleton
        $this->app->singleton(FailoverBanco::class, function ($app) {
            return new FailoverBanco();
        });
    }

    public function boot()
    {
        // Substitui a conexão 'mysql' do Laravel
        $failover = $this->app->make(FailoverBanco::class);

        config([
            'database.connections.mysql.host' => $failover->host,
            'database.connections.mysql.port' => $failover->porta,
            'database.connections.mysql.database' => $failover->banco,
            'database.connections.mysql.username' => $failover->usuario,
            'database.connections.mysql.password' => $failover->senha,
        ]);

        // Limpa a conexão antiga
        DB::purge('mysql');
    }
}
