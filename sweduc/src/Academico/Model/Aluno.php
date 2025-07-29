<?php

declare(strict_types=1);

namespace App\Academico\Model;

use App\Framework\Model;
use App\Model\Core\Pessoa;
use App\Model\Financeiro\DescontoComercial;
use App\Model\Financeiro\Titulo;

class Aluno extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function getNumeroAttribute()
    {
        return $this->numeroaluno;
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idpessoa');
    }

    public function responsavelPedagogico()
    {
        return $this->hasOne(Responsavel::class, 'idaluno')->where('resppedag', 1);
    }

    public function responsavelFinanceiro()
    {
        return $this->hasOne(Responsavel::class, 'idaluno')->where('respfin', 1);
    }

    public function responsavelFinanceiro2()
    {
        return $this->hasOne(Responsavel::class, 'idaluno')->where('respfin2', 1);
    }

    public function responsaveis()
    {
        return $this->hasMany(Responsavel::class, 'idaluno');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'idaluno');
    }

    public function periodoLetivo()
    {
        return $this->hasOne(PeriodoLetivo::class, 'anoletivomatricula');
    }

    public function turmas()
    {
        return $this->belongsToMany(Turma::class, 'alunos_matriculas', 'idaluno', 'turmamatricula');
    }

    public function descontoComercial()
    {
        return $this->belongsTo(DescontoComercial::class, 'desconto_comercial_msg');
    }

    public function documentosEntregues()
    {
        return $this->belongsToMany(Documento::class, 'alunos_documentos', 'idaluno', 'iddocumento')->withPivot('id');
    }

    public function anamnese()
    {
        return $this->hasOne(Anamnese::class, 'idaluno');
    }

    public function titulos()
    {
        return $this->hasMany(Titulo::class, 'idaluno');
    }
}
