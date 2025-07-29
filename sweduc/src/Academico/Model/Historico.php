<?php

declare(strict_types=1);

namespace App\Academico\Model;

use App\Framework\Model;

class Historico extends Model
{
    public $timestamps = false;
    protected $table = 'alunos_historico';

    protected $guarded = false;

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'idaluno');
    }

    public function setAttribute($key, $value)
    {
        if (empty($value)) {
            $value = null;
        }

        return parent::setAttribute($key, $value);
    }
}
