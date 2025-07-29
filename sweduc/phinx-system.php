<?php

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/system/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/system/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'system',
        'system' => [
            'adapter' => 'mysql',
            'host' => $_SERVER['MASTER_DB_HOST'],
            'name' => $_SERVER['MASTER_DB_DATABASE'],
            'user' => $_SERVER['MASTER_DB_USER'],
            'pass' => $_SERVER['MASTER_DB_PASSWORD'],
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation'
];
