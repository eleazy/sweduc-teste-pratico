<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Sexo extends Model implements \Stringable
{
    public $timestamps = false;
    public $table = 'sexo';

    public function getTituloAttribute()
    {
        return $this->sexo;
    }

    public function __toString(): string
    {
        return (string) $this->sexo;
    }
}
