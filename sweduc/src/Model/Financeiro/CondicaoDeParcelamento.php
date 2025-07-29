<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

final class CondicaoDeParcelamento extends Model
{
    public function conta()
    {
        return $this->belongsTo(Conta::class, 'conta_id');
    }

    public function evento()
    {
        return $this->belongsTo(EventoFinanceiro::class, 'evento_id');
    }

    protected $table = 'financeiro_pagamento_online_condicoes_parcelamento';
}
