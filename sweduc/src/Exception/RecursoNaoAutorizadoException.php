<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

/**
 * Recurso não autorizado para o usuário
 */
class RecursoNaoAutorizadoException extends Exception
{
    public function __construct($message = "Recurso não autorizado ou inexistente!")
    {
        parent::__construct($message);
    }
}
