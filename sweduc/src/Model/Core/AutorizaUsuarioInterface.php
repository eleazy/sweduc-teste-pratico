<?php

declare(strict_types=1);

namespace App\Model\Core;

use App\Exception\RecursoNaoAutorizadoException;
use App\Model\Core\Usuario;

interface AutorizaUsuarioInterface
{
    /**
     * Permite acesso do usuário ao recurso ou emite exceção de autorização
     *
     * @throws RecursoNaoAutorizadoException
     * @return void
     */
    public function autoriza(Usuario $usuario): void;
}
