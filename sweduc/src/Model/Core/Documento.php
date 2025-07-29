<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

/**
 * Template de documento usado em acadêmico, financeiro, etc
 * para relatórios, boletims, etc
 */
class Documento extends Model
{
    public const CONTEXTO_ALUNOS_ACADEMICO = 0;
    public const CONTEXTO_FINANCEIRO = 1;
    public const CONTEXTO_EMPRESA = 2;
    public const CONTEXTO_RESPONSAVEIS = 3;
    public const CONTEXTO_TURMAS_ACADEMICO = 4;
    public const CONTEXTO_REMATRICULA = 5;
    public const CONTEXTO_ALUNOS_ACADEMICO_V2 = 6;

    public function scopeContratoRematricula($query)
    {
        return $query->where('contexto', self::CONTEXTO_REMATRICULA);
    }

    public function getNomeAttribute()
    {
        return $this->nomedoc;
    }

    public $table = 'doceditor';
}
