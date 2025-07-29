<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;
use App\Model\Financeiro\Titulo;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller de contas a receber
 */
class ContasAReceberAPIController extends Controller
{
    public function update(ServerRequestInterface $request, $args)
    {
        $input = $this->jsonInput($request);

        $fichaId = $args['id'];
        $ficha = Titulo::findOrFail($fichaId);

        if (isset($input->pagamentoOnline)) {
            $pagamentoOnline = filter_var($input->pagamentoOnline, FILTER_VALIDATE_BOOLEAN);
            $ficha->pagamento_cartao_online = $pagamentoOnline;
        }

        $ficha->saveOrFail();

        return $this->jsonResponse($ficha);
    }
}
