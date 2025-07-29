<?php

declare(strict_types=1);

namespace App\Configuracoes\Controller\Financeiro;

use App\Controller\Controller;
use App\Model\Core\Funcionario;
use App\Model\Financeiro\CondicaoDeParcelamento;
use App\Model\Financeiro\Conta;
use App\Model\Financeiro\EventoFinanceiro;
use App\Model\Financeiro\FormaDePagamento;
use NumberFormatter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller de contas a receber
 */
class CartaoConfigController extends Controller
{
    public function __construct(ResponseFactoryInterface $responseFactory, protected NumberFormatter $numFmt)
    {
        parent::__construct($responseFactory);
    }

    public function index(ServerRequestInterface $request)
    {
        $this->request = $request;
        $contas = Conta::banco()->get();
        $funcionarios = Funcionario::with('pessoa')->where('idunidade', 0)->get();
        $formasDePagamento = FormaDePagamento::orderBy('formapagamento')->get();

        return $this->platesView('Config/Cartao/Listar', compact(
            'contas',
            'formasDePagamento',
            'funcionarios'
        ));
    }

    public function show(ServerRequestInterface $request, $args)
    {
        $id = $args['id'];
        $this->request = $request;
        $conta = Conta::findOrFail($id);
        $funcionarios = Funcionario::with('pessoa')->where('idunidade', 0)->get();
        $formasDePagamento = FormaDePagamento::orderBy('formapagamento')->get();
        $eventos = EventoFinanceiro::receita()->get();
        $condicoesDeParcelamento = CondicaoDeParcelamento::where('conta_id', $conta->id)->get();

        return $this->platesView('Config/Cartao/Ver', compact(
            'id',
            'conta',
            'formasDePagamento',
            'funcionarios',
            'eventos',
            'condicoesDeParcelamento',
        ));
    }

    public function store(ServerRequestInterface $request, $args)
    {
        $this->request = $request;
        $id = $args['id'];
        $input = $request->getParsedBody();

        try {
            $conta = Conta::findOrFail($id);
            $conta->pagamento_online_provedor = $input['pagamento-online-provedor'];
            $conta->pagamento_online_ambiente_producao = $input['pagamento-online-modo'] === 'producao';
            $conta->pagamento_online_pv = $input['pagamento-online-pv'];
            $conta->pagamento_online_token = $input['pagamento-online-token'];
            $conta->pagamento_online_forma_pagamento_id = $input['pagamento-online-forma-vinculada'];
            $conta->pagamento_online_funcionario_id = $input['pagamento-online-funcionario-vinculado'];
            $conta->pagamento_online_tarifa = $this->numFmt->parse($input['pagamento-online-tarifa']);
            $conta->pagamento_online_baixa_dias = $input['pagamento-online-baixa-dias'];
            $conta->saveOrFail();
        } catch (\Throwable $th) {
            return $this->errorJsonResponse($th->getMessage(), 500);
        }

        return $this->redirect('/config/financeiro/cartao/');
    }
}
