<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Tests\Traits\UsesRouter;

use function App\Framework\app;

class TestCase extends PhpUnitTestCase
{
    use UsesRouter;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        app();
    }
}
