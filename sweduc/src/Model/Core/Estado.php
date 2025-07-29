<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model implements \Stringable
{
    public $timestamps = false;
    public $table = 'estados';

    public function cidades()
    {
        return $this->hasMany(Cidade::class, 'cod_estado', 'id');
    }

    public function __toString(): string
    {
        return (string) $this->nom_estado;
    }
}
