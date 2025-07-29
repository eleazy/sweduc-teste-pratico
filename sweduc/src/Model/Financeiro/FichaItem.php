<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

/** @deprecated v6.14.0 Use ItemTitulo instead */
class FichaItem extends Model
{
    public $timestamps = false;

    public function fichaFinanceira()
    {
        return $this->belongsTo('App\Model\Financeiro\FichaFinanceira', 'idalunos_fichafinanceira');
    }

    public function evento()
    {
        return $this->belongsTo(EventoFinanceiro::class, 'codigo', 'codigo');
    }

    protected $table = 'alunos_fichaitens';
}
