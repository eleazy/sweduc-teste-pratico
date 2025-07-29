<?php

declare(strict_types=1);

namespace App\Framework\Service;

class ExampleService
{
    /**
     * Roda código na execução da aplicação
     *
     * É possível passar dependencias que serão resolvidas
     * pelo container de injeção de dependencias para o método
     */
    public function boot(): void
    {
        /**
         * Faz alguma coisa na
         * inicialização da aplicação
         */
    }
}
