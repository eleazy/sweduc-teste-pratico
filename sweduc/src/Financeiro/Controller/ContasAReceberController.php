<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;
use App\Model\Core\Usuario;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller de contas a receber
 */
class ContasAReceberController extends Controller
{
    /**
     * Retorna formulário de busca
     */
    public function buscar(): ResponseInterface
    {
        return $this->platesView('Financeiro/ContasAReceber/Buscar');
    }

    /**
     * Retorna listagem
     */
    public function listar(ServerRequestInterface $request): ResponseInterface
    {
        $usuario = $request->getAttribute(Usuario::class);

        return $this->platesView('Financeiro/ContasAReceber/Listar', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * Retorna formulario de lancamento
     */
    public function mostrarFormLancamento(): ResponseInterface
    {
        return $this->platesView('Financeiro/ContasAReceber/Lancamento');
    }
}
