<?php

declare(strict_types=1);

namespace App\Financeiro\Controller\Relatorios;

use App\Framework\Http\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SerasaController extends BaseController
{
    /**
     * Relatório de contatos
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->platesView('Financeiro/ContasAReceber/Relatorios/Serasa');
    }
}
