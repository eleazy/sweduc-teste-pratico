<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model implements \Stringable
{
    public $timestamps = false;
    public $table = 'cidades';

    public function __toString(): string
    {
        return (string) $this->nom_cidade;
    }
}
