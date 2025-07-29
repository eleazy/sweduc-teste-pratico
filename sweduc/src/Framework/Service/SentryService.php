<?php

declare(strict_types=1);

namespace App\Framework\Service;

use function Sentry\init;

class SentryService
{
    /**
     * Roda código na execução da aplicação
     */
    public function boot(): void
    {
        // Se a variável de ambiente APP_DISABLE_SENTRY estiver definida como true, não inicializa o Sentry
        if ($_ENV['APP_DISABLE_SENTRY'] ?? false) {
            return;
        }

        init([
            'dsn' => 'https://70496a348c01444eb65f9dda418b319a@o420348.ingest.sentry.io/5338427',
            'max_breadcrumbs' => 50,
            'error_types' => E_ERROR,
            'sample_rate' => 1,
            'environment' => $_ENV['APP_ENV'],
        ]);
    }
}
