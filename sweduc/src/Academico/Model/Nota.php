<?php

declare(strict_types=1);

namespace App\Academico\Model;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    public $timestamps = false;
    protected $table = 'alunos_notas';
    protected $guarded = [];

    public function media()
    {
        return $this->belongsTo(Media::class, 'idmedia');
    }

    public function avaliacao()
    {
        return $this->belongsTo(Avaliacao::class, 'idavaliacao');
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'idaluno');
    }
}
