<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;
use App\Model\Financeiro\Titulo;

class AsaasCobranca extends Model
{
    protected $fillable = [
        'id_alunos_fichafinanceira',
        'billing_type',
        'link_cobranca',
        'data_excluida',
    ];

    public function titulo()
    {
        return $this->belongsTo(Titulo::class, 'id_aluno_ficha_financeira');
    }

    protected $table = 'asaas_cobrancas';
}
