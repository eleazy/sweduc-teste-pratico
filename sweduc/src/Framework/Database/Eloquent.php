<?php

declare(strict_types=1);

namespace App\Framework\Database;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;

class Eloquent extends Capsule
{
    public static function bootAsGlobal()
    {
        $eloquent = new Eloquent();
        $eloquent->setAsGlobal();
        $eloquent->bootEloquent();
    }

    public function __construct(?Container $container = null)
    {
        parent::__construct($container);
    }

    public function addMysqlConnection(
        string $host,
        string $database,
        string $username,
        string $password,
        $name = 'default'
    ) {
        $this->addConnection([
            'driver'    => 'mysql',
            'host'      => $host,
            'database'  => $database,
            'username'  => $username,
            'password'  => $password,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ], $name);
    }

    public function debugDatabase(LoggerInterface $logger)
    {
        Capsule::listen(function ($query) use ($logger) {
            $logger->info("Query $query->sql took {$query->time}ms");
        });
    }
}
