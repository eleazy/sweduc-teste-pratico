<?php

declare(strict_types=1);

namespace App\Academico\Model;

use Illuminate\Database\Eloquent\Model;

class AlunoStatus extends Model implements \Stringable
{
    public $timestamps = false;
    protected $table = 'alunos_status';

    public function __toString(): string
    {
        return (string) $this->nome;
    }
}
