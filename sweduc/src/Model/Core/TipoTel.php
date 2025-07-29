<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class TipoTel extends Model implements \Stringable
{
    public const TIPO_CELULAR = 1;

    public $timestamps = false;
    public $table = 'tipotel';

    public static function celular(): TipoTel
    {
        return self::find(self::TIPO_CELULAR);
    }

    public function __toString(): string
    {
        return (string) $this->tipotel;
    }
}
