<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use App\Model\Core\Funcionario;
use Illuminate\Database\Eloquent\Model;

class Recebimento extends Model
{
    public $timestamps = false;
    protected $with = ['formaDePagamento'];

    protected $attributes = [
        'datacompensado' => '0000-00-00',
        'datareaberto' => '0000-00-00',
        'idfuncionarioreaberto' => 0,
        'datadevolvido' => '0000-00-00',
        'idfuncionariodevolvido' => 0,
        'datareapresentado' => '0000-00-00',
        'idfuncionarioreapresentado' => 0,
        'alinea' => '',
    ];

    public function fichaFinanceira()
    {
        return $this->belongsTo('App\Model\Financeiro\FichaFinanceira', 'idalunos_fichafinanceira');
    }

    public function titulo()
    {
        return $this->belongsTo('App\Model\Financeiro\FichaFinanceira', 'idalunos_fichafinanceira');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'idfuncionario');
    }

    public function formaDePagamento()
    {
        return $this->belongsTo(FormaDePagamento::class, 'formarecebido');
    }

    public function getPagoComAttribute()
    {
        return $this->formaDePagamento->formapagamento;
    }

    protected $table = 'alunos_fichasrecebidas';
}
