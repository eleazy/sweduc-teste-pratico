<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;

/**
 * Controller de contas a receber
 */
class RecorrenciaController extends Controller
{
    /**
     * Retorna página de recebimentos de recorrência
     *
     * @return Response
     */
    public function mostrarRecebimentos()
    {
        return $this->platesView('Financeiro/Recorrencia/Recebimentos');
    }

    /**
     * Retorna página de planos de recorrência
     *
     * @return Response
     */
    public function mostrarPlanos()
    {
        return $this->platesView('Financeiro/Recorrencia/Planos');
    }

    /**
     * Retorna página de assinaturas de recorrência
     *
     * @return Response
     */
    public function mostrarAssinaturas()
    {
        return $this->platesView('Financeiro/Recorrencia/Assinaturas');
    }
}
