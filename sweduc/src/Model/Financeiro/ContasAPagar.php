<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;
use App\Model\Financeiro\AsaasConfig;
use Illuminate\Database\Capsule\Manager as DB;

class ContasAPagar extends Model
{
    public $timestamps = false;
    protected $table = 'contasapagar';
    protected $guarded = [];

    public function createFromAsaas(object $body, int $fornecedorId, int $eventoFinanceiroId): self
    {
        if ($body->transfer) {
            $dataVencimento = $body->transfer->dateCreated;
            $valor = $body->transfer->value;
            $paymentDate = $body->transfer->dateCreated;
            $valorLiquido = $body->transfer->netValue;
            $descricao = $body->transfer->description;
            $pagamentoId = $body->transfer->id;
        }
        if ($body->bill) {
            $dataVencimento = $body->bill->dueDate;
            $valor = $body->bill->value;
            $paymentDate = $body->bill->paymentDate ?? $body->bill->scheduleDate;
            $valorLiquido = $body->bill->value;
            $descricao = "{$body->bill->description} - Link do Comprovante: {$body->bill->transactionReceiptUrl}";
            $pagamentoId = $body->bill->id;
            $desconto = $body->bill->discount;
            $multa = $body->bill->fine;
            $juros = $body->bill->interest;
        }

        $bancoId = AsaasConfig::get('contas_a_pagar_banco_id') ?? 0;
        $empresaId = AsaasConfig::get('contas_a_pagar_empresa_id') ?? 0;
        $formaPagamentoId = AsaasConfig::get('contas_a_pagar_forma_pagamento') ?? 0;

        $eventoFinanceiro = DB::select("SELECT eventofinanceiro, codigo FROM eventosfinanceiros WHERE id = ?", [$eventoFinanceiroId]);
        $eventoFinanceiroNome = $eventoFinanceiro[0]->eventofinanceiro ?? '';
        $eventoFinanceiroCodigo = $eventoFinanceiro[0]->codigo ?? '';

        $usuarioIdQ = DB::select("SELECT id FROM usuarios WHERE is_internal_user = 1"); // temporary
        $usuarioId = $usuarioIdQ[0]->id ?? 0;

        $this->idusuario = $usuarioId;
        $this->idempresa = (int) $empresaId;
        $this->idfornecedor = (int) $fornecedorId;
        $this->idcolaborador = 0;
        $this->idcontasbanco = (int) $bancoId;
        $this->idformaspagamentos = (int) $formaPagamentoId;
        $this->codigoeventofinanceiro = $eventoFinanceiroCodigo;
        $this->eventofinanceiro = $eventoFinanceiroNome;
        $this->documento = "";
        $this->parcelas = 1;
        $this->repeticoes = 0;
        $this->totalparcelas = 1;
        $this->datavencimento = $dataVencimento;
        $this->valor = $valor;
        $this->situacao = 1; // pago
        $this->datapagamento = $paymentDate;
        $this->valorpago = $valorLiquido;
        $this->desconto = $desconto ?? 0;
        $this->multa = $multa ?? 0;
        $this->juros = $juros ?? 0;
        $this->numeroForma = "";
        $this->datavalidadeForma = "";
        $this->bancoForma = "Asaas";
        $this->agenciaForma = "";
        $this->contaForma = "";
        $this->outroForma = "";
        $this->descritivo = $descricao;
        $this->asaas_id = $pagamentoId;

        $this->save();

        return $this;
    }
}
