<?php

declare(strict_types=1);

namespace App\Model\Config;

use App\Model\Financeiro\Conta as Conta;

/**
 * @deprecated 3.0.0 Mude a visibilidade diretamente através do modelo ContasBanco
 */
class ContasBanco
{
    private Conta $cb;

    public function __construct($id)
    {
        $this->cb = Conta::withoutGlobalScopes()->find($id);
    }

    /**
     * Ativa ou desativa a visibilidade das contas bancárias
     *
     * @return bool Resultado da operação
     */
    public function mudarVisibilidade($ativar)
    {
        return $this->cb->mudarVisibilidade($ativar);
    }
}
