<?php

declare(strict_types=1);

namespace App\Academico\Model;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $table = 'grade';

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'idturma');
    }

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class, 'iddisciplina');
    }

    public function periodoLetivo()
    {
        return $this->belongsTo(PeriodoLetivo::class, 'idanoletivo');
    }

    public function professores()
    {
        return $this->belongsToMany(Professor::class, 'grade_funcionario', 'idgrade', 'idfuncionario');
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'idgrade');
    }

    public function periodos()
    {
        return $this->belongsToMany(Periodo::class, 'medias', 'idgrade', 'idperiodo');
    }
}
