<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

final class DescontoComercial extends Model implements \Stringable
{
    protected $table = 'financeiro_descontocomercial';

    public function __toString(): string
    {
        return (string) $this->titulo;
    }
}
