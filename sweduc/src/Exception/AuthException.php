<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class AuthException extends Exception
{
    public static function loginOuSenhaIncorreta($message = "Login ou senha incorreta!")
    {
        return new static($message);
    }
}
