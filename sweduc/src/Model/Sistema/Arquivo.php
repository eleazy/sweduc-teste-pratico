<?php

declare(strict_types=1);

namespace App\Model\Sistema;

use App\Academico\Model\Serie;
use App\Academico\Model\Turma;
use Illuminate\Database\Eloquent\Model;

class Arquivo extends Model
{
    public function series()
    {
        return $this->belongsToMany(Serie::class, 'upload_serie', 'idupload', 'idserie');
    }

    public function turmas()
    {
        return $this->belongsToMany(Turma::class, 'turmas_arquivos', 'idarquivo', 'idturma');
    }

    protected $table = 'upload_arquivo';
}
