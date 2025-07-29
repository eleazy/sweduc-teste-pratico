<?php

declare(strict_types=1);

namespace App\Framework\Service;

use Monolog\ErrorHandler;

class ErrorLoggerService
{
    /**
     * Registra interceptador de erros do monolog
     */
    public function boot(ErrorHandler $errorHandler): void
    {
        $errorHandler->registerErrorHandler([], false);
        $errorHandler->registerExceptionHandler();
        $errorHandler->registerFatalHandler();
    }
}
