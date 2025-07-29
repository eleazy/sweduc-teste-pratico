<?php

declare(strict_types=1);

namespace App\Logger;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class EredeLogger extends Logger
{
    public function __construct()
    {
        // Envia logs de erro para /storage/erede.log
        $handler = new StreamHandler(__DIR__ . '/../../storage/erede.log');
        $handler->setFormatter(new JsonFormatter());

        parent::__construct('e-rede', [$handler]);
    }
}
