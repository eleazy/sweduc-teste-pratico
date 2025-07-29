<?php

declare(strict_types=1);

namespace App\Logger;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class RematriculaLogger extends Logger
{
    public function __construct()
    {
        // Envia logs de erro para /storage/rematricula.log
        $handler = new StreamHandler(__DIR__ . '/../../storage/rematricula.log');
        $handler->setFormatter(new JsonFormatter());

        parent::__construct('rematricula', [$handler]);
    }
}
