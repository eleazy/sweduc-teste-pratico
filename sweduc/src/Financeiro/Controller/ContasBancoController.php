<?php

namespace App\Financeiro\Controller;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Controller\Controller;
use App\Model\Financeiro\Conta;

class ContasBancoController extends Controller
{
    public function listaContasDaUnidade(ServerRequestInterface $request): ResponseInterface
    {

        $conta = new Conta();

        try {
            $contasUnidade = $conta->contasDaUnidade($request->getAttribute('unidadeId'));
        } catch (Exception $e) {
            $response = $this->createResponse(500);
            $response->getBody()->write(json_encode(['message' => $e->getMessage()]));
            return $response;
        }
        $response = $this->createResponse(200);
        $response->getBody()->write(json_encode($contasUnidade));

        return $response;
    }
}
