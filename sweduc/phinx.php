<?php

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

/**
 * Carrega variáveis de ambiente
 */
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/tenancy/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/tenancy/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'default',
        'default' => [
            'adapter' => 'mysql',
            'host' => $_SERVER['DB_HOST'],
            'name' => $_SERVER['DB_DATABASE'],
            'user' => $_SERVER['DB_USER'],
            'pass' => $_SERVER['DB_PASSWORD'],
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation'
];
