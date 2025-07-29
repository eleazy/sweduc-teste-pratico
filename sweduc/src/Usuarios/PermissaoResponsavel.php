<?php

declare(strict_types=1);

namespace App\Usuarios;

class PermissaoResponsavel
{
    public static function deMatricula($matricula)
    {
        $curso = $matricula->turma->serie->curso;
        $perfil = $curso->perfilpais;

        $permissoes = [];
        $permissoes['boletim'] = $perfil[3];
        return array_keys(array_filter($permissoes));
    }
}
