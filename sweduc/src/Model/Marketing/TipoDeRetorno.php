<?php

declare(strict_types=1);

namespace App\Model\Marketing;

use App\Framework\Model;

class TipoDeRetorno extends Model implements \Stringable
{
    protected $table = 'mkt_tiporetorno';
    public $timestamps = false;

    public function __toString(): string
    {
        return (string) $this->tipo;
    }
}
