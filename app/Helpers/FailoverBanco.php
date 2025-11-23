<?php

namespace App\Helpers;

use Exception;
use PDO;
use PDOException;
use Illuminate\Support\Facades\DB;

class FailoverBanco
{
    private string $arquivo;
    private array $bancos;

    public string $host;
    public int $porta;
    public string $usuario;
    public string $senha;
    public string $banco;

    public function __construct()
    {
        $hosts = explode(',', env('DB_HOSTS', '127.0.0.1'));
        $ports = explode(',', env('DB_PORTS', '3306'));
        $database = env('DB_DATABASE', 'laravel');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        $this->bancos = [];
        foreach ($hosts as $i => $host) {
            $this->bancos["banco{$i}"] = [
                'host'    => $host,
                'porta'   => $ports[$i] ?? '3306',
                'usuario' => $username,
                'senha'   => $password,
                'banco'   => $database,
            ];
        }

        $this->arquivo = storage_path('app/host_atual.txt');

        $this->selecionarBanco();
        $this->configurarLaravel();
    }

    private function selecionarBanco(): void
    {
        $ultimo = $this->lerHostAtual();

        if ($ultimo !== null && $this->testarConexao($ultimo, false)) {
            $this->atribuir($ultimo);
            return;
        }

        foreach ($this->bancos as $key => $db) {
            if ($this->testarConexao($key)) {
                $this->atribuir($key);
                $this->gravarHostAtual($key);
                return;
            }
        }

        throw new Exception("Nenhum servidor de banco está respondendo no failover.");
    }

    private function testarConexao(string $key, bool $comBanco = true): bool
    {
        if (!isset($this->bancos[$key])) return false;

        $db = $this->bancos[$key];
        $dsn = $comBanco
            ? "mysql:host={$db['host']};port={$db['porta']};dbname={$db['banco']};charset=utf8mb4"
            : "mysql:host={$db['host']};port={$db['porta']};charset=utf8mb4";

        try {
            new PDO($dsn, $db['usuario'], $db['senha'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return true;
        } catch (PDOException $e) {
            if (!$comBanco && str_contains($e->getMessage(), 'Unknown database')) {
                return true;
            }
            return false;
        }
    }

    private function atribuir(string $key): void
    {
        $db = $this->bancos[$key];

        $this->host    = $db['host'];
        $this->porta   = $db['porta'];
        $this->usuario = $db['usuario'];
        $this->senha   = $db['senha'];
        $this->banco   = $db['banco'];
    }

    private function gravarHostAtual(string $key): void
    {
        file_put_contents($this->arquivo, $key);
    }

    private function lerHostAtual(): ?string
    {
        if (!file_exists($this->arquivo)) return null;

        $key = trim(file_get_contents($this->arquivo));
        return isset($this->bancos[$key]) ? $key : null;
    }

    private function configurarLaravel(): void
    {
        // Atualiza dinamicamente a conexão mysql do Laravel
        config([
            'database.connections.mysql.host' => $this->host,
            'database.connections.mysql.port' => $this->porta,
            'database.connections.mysql.database' => $this->banco,
            'database.connections.mysql.username' => $this->usuario,
            'database.connections.mysql.password' => $this->senha,
        ]);

        // Limpa a conexão antiga do Laravel para forçar reconexão
        DB::purge('mysql');
    }

    public function getPDO(bool $usarBanco = true): PDO
    {
        $dsn = $usarBanco
            ? "mysql:host={$this->host};port={$this->porta};dbname={$this->banco};charset=utf8mb4"
            : "mysql:host={$this->host};port={$this->porta};charset=utf8mb4";

        try {
            return new PDO($dsn, $this->usuario, $this->senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            if (!$usarBanco && str_contains($e->getMessage(), 'Unknown database')) {
                return new PDO("mysql:host={$this->host};port={$this->porta};charset=utf8mb4", $this->usuario, $this->senha, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            }
            throw new Exception("Falha ao conectar no MySQL: " . $e->getMessage());
        }
    }
}
