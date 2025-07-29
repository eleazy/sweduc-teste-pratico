<?php

declare(strict_types=1);

namespace App\Model\Marketing;

use App\Academico\Model\Curso;
use App\Academico\Model\Serie;
use App\Academico\Model\Turno;
use App\Model\Core\Unidade;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    protected $dates = ['nascido_em'];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'id_unidade');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class, 'id_serie');
    }

    public function turno()
    {
        return $this->belongsTo(Turno::class, 'id_turno');
    }

    public function prospeccao()
    {
        return $this->belongsTo(Prospeccao::class, 'id_prospeccao_ficha');
    }

    public $timestamps = false;
    protected $table = 'prospeccao_alunos';
}
