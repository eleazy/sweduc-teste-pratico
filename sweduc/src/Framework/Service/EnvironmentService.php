<?php

declare(strict_types=1);

namespace App\Framework\Service;

use App\Exception\ClienteNaoConfiguradoException;
use App\Framework\Environment\EnvironmentManager;

class EnvironmentService
{
    /**
     * Roda código na execução da aplicação
     */
    public function boot(EnvironmentManager $env): void
    {
        // Adiciona variaveis do env no server sem sobreescrita
        $_SERVER += $_ENV;

        // Carrega variáveis via .env se não estiverem carregadas no sistema
        if (!$env->isConfigReady()) {
            $env->loadConfig(__DIR__ . '/../../..');
        }

        // Carrega variáveis via multitenancy database se estiver configurado
        if (filter_var($_SERVER['MULTI_TENANCY'] ?? false, FILTER_VALIDATE_BOOL) && !$env->isConfigReady()) {
            $env->loadClientConfig();
        }

        // Emite erro de configuração se não foi possível carregar ambiente após o processo
        if (!$env->isConfigReady()) {
            throw new ClienteNaoConfiguradoException();
        }
    }
}
