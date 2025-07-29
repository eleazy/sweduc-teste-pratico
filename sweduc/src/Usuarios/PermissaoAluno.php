<?php

declare(strict_types=1);

namespace App\Usuarios;

class PermissaoAluno
{
    public static function deMatricula($matricula)
    {
        $curso = $matricula->turma->serie->curso;
        $perfil = $curso->perfilalunos;

        $permissoes = [];
        $permissoes['boletim'] = $perfil[3];
        return array_keys(array_filter($permissoes));
    }
}
