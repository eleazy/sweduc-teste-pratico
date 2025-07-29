<?php

declare(strict_types=1);

namespace App\Configuracoes\Controller;

use App\Framework\Http\BaseController;
use App\Model\Core\Usuario;

class PerfilFuncionarioController extends BaseController
{
    public function index()
    {
        $usuario = Usuario::fromSession();
        $funcionario = $usuario->funcionario;
        return $this->platesView('Config/PerfilFuncionario', [
            'usuario' => $usuario,
            'funcionario' => $funcionario,
        ]);
    }
}
