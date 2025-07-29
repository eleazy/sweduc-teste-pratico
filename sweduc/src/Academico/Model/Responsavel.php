<?php

declare(strict_types=1);

namespace App\Academico\Model;

use App\Model\Core\Parentesco;
use App\Model\Core\Pessoa;
use Illuminate\Database\Eloquent\Model;

/**
 * TL;DR; esse modelo é um pivot, use o modelo pessoa
 *
 * Esse modelo tem alguns problemas, a tabela responsaveis
 * na verdade representa a ligação entre uma pessoa e um aluno
 * então esse modelo seria um pivot, sendo assim não é possível
 * acessar os alunos e matrículas de um responsável por aqui
 * em vez disso use o modelo pessoa
 */
class Responsavel extends Model
{
    public $timestamps = false;
    protected $table = 'responsaveis';
    protected $guarded = [];

    public function scopePedagogico($query)
    {
        return $query->where('resppedag', 1);
    }

    public function scopeFinanceiro($query)
    {
        return $query->where('respfin', 1);
    }

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'idaluno');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idpessoa');
    }

    public function parentesco()
    {
        return $this->belongsTo(Parentesco::class, 'idparentesco');
    }
}
