<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class Situacao extends Model implements \Stringable
{
    public function getSituacaoAttribute()
    {
        return $this->situacaotitulo;
    }

    public function __toString(): string
    {
        return (string) $this->situacaotitulo;
    }

    public $timestamps = false;
    protected $table = 'financeiro_situacaotitulos';
}
