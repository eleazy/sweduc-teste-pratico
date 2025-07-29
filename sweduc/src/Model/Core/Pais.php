<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model implements \Stringable
{
    public $timestamps = false;
    public $table = 'paises';
    protected $primaryKey = 'cod_pais';

    public function estados()
    {
        return $this->hasMany(Estado::class, 'cod_pais', 'cod_pais');
    }

    public function getTituloAttribute()
    {
        return $this->nom_pais;
    }

    public function __toString(): string
    {
        return (string) $this->nom_pais;
    }
}
