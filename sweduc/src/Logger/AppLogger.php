<?php

declare(strict_types=1);

namespace App\Logger;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppLogger extends Logger
{
    public function __construct()
    {
        // Envia logs de erro para /storage/app.log
        $fileHandler = new StreamHandler(__DIR__ . '/../../storage/app.log');
        $fileHandler->setFormatter(new LineFormatter());

        // Envia logs de erro para stderr
        $errorLogHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::ERROR);

        parent::__construct('app', [$fileHandler, $errorLogHandler]);
    }
}
