<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

/**
 * Recurso não encontrado
 */
class RecursoNaoEncontradoException extends Exception
{
    public function __construct($message = "Recurso inexistente!")
    {
        parent::__construct($message);
    }
}
