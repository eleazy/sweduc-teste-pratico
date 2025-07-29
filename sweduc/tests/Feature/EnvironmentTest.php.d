<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Framework\Testing\TestCase;
use Illuminate\Database\Capsule\Manager;

use function PHPUnit\Framework\assertEquals;

final class EnvironmentTest extends TestCase
{
    public function testEloquentOrmUsingTestDbConnection()
    {
        $connection = Manager::connection();
        assertEquals('mysql', $connection->getDriverName());
        assertEquals('sweduc_testing', $connection->getDatabaseName());
    }
}
