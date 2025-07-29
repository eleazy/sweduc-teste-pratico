<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class ClienteNaoConfiguradoException extends Exception
{
    public function __construct()
    {
        parent::__construct("Cliente não configurado!");
    }
}
