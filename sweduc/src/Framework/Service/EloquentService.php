<?php

declare(strict_types=1);

namespace App\Framework\Service;

use App\Framework\Database\Eloquent;
use Psr\Log\LoggerInterface;

class EloquentService
{
    private bool $debugDatabase = false;

    /**
     * Laravel's eloquent ORM
     */
    public function boot(Eloquent $eloquent, LoggerInterface $logger): void
    {
        $eloquent->addConnection([
            'driver'    => 'mysql',
            'host'      => $_SERVER['DB_HOST'],
            'database'  => $_SERVER['DB_DATABASE'],
            'username'  => $_SERVER['DB_USER'],
            'password'  => $_SERVER['DB_PASSWORD'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        $eloquent->addConnection([
            'driver'    => 'mysql',
            'host'      => $_SERVER['DB_HOST'],
            'database'  => 'sweduc_shared',
            'username'  => $_SERVER['DB_USER'],
            'password'  => $_SERVER['DB_PASSWORD'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ], 'shared');

        $eloquent->setAsGlobal();
        $eloquent->bootEloquent();

        if ($this->debugDatabase) {
            Eloquent::listen(function ($query) use ($logger) {
                $logger->info("Query $query->sql took {$query->time}ms");
            });
        }
    }
}
