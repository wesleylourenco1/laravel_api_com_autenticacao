<?php

// Script para inicializar a aplicação

$dotenv = __DIR__.'/.env';
if (!file_exists($dotenv)) {
    copy(__DIR__.'/.env.example', $dotenv);
    echo ".env criado a partir do .env.example\n";
}

// Instalar dependências se não existir vendor
if (!is_dir(__DIR__.'/vendor')) {
    echo "Instalando dependências...\n";
    shell_exec('composer install');
}

// Gerar APP_KEY
echo "Gerando APP_KEY...\n";
shell_exec('php artisan key:generate');

// Criar banco de dados se não existir
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO("mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']}", $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_ENV['DB_DATABASE']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Banco de dados criado (se não existia).\n";
} catch (PDOException $e) {
    die("Erro ao criar banco de dados: " . $e->getMessage());
}

// Rodar migrations e seeders
echo "Rodando migrations e seeders...\n";
shell_exec('php artisan migrate --seed');

echo "Setup concluído!\n";
