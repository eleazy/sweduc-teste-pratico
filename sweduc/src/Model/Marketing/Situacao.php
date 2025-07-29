<?php

declare(strict_types=1);

namespace App\Model\Marketing;

use App\Framework\Model;

class Situacao extends Model implements \Stringable
{
    protected $table = 'prospeccao_situacoes';
    public $timestamps = false;

    public function getSituacaoAttribute()
    {
        return $this->nome;
    }

    public function __toString(): string
    {
        return (string) $this->nome;
    }
}
