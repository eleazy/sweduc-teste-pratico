<?php

declare(strict_types=1);

namespace App\Academico\Model;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    public $timestamps = false;
    protected $table = 'documentos';

    public function getTituloAttribute()
    {
        //
    }

    public function alunos()
    {
        return $this->belongsToMany(Aluno::class, 'alunos_documentos', 'iddocumento', 'idaluno');
    }
}
