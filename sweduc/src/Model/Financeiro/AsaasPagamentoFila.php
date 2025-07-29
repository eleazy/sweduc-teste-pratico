<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Financeiro\ContasAPagar;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsaasPagamentoFila extends Model
{
    use SoftDeletes;

    protected $table = 'asaas_fila_contas_a_pagar';

    protected $guarded = [];

    public static function processQueue()
    {
        $filaProntosParaProcessarQ =
            "SELECT
                af.*
            FROM
                asaas_fila_contas_a_pagar af
            WHERE
                af.status = 'PENDING'
            AND
                af.confirmed = 1
            AND
                af.eventofinanceiro_id IS NOT NULL
            ";

        $filaProntosParaProcessar = DB::select($filaProntosParaProcessarQ);
        foreach ($filaProntosParaProcessar as $item) {
            $asaasEvtId = $item->event_body_id;
            $requestBody = json_decode($item->request_body);
            $fornecedorId = $item->fornecedor_id;
            $eventoFinanceiroId = $item->eventofinanceiro_id;

            if ($asaasEvtId && $requestBody && $fornecedorId) {
                $contasAPagar = new ContasAPagar();
                $conta = $contasAPagar->createFromAsaas($requestBody, $fornecedorId, $eventoFinanceiroId);

                if ($conta->id) {
                    DB::table('asaas_fila_contas_a_pagar')->where('event_body_id', $asaasEvtId)->update(['status' => 'SUCCESS']);
                } else {
                    DB::table('asaas_fila_contas_a_pagar')->where('event_body_id', $asaasEvtId)->update(['status' => 'FAILED']);
                }
            }
        }
    }

    public function getData()
    {
        $body = json_decode($this->request_body);
        $data = [];

        if ($body->transfer) {
            $data['origem'] = 'transfer';
            $data['dataVencimento'] = $body->transfer->dateCreated;
            $data['valor'] = $body->transfer->value;
            $data['paymentDate'] = $body->transfer->dateCreated;
            $data['valorLiquido'] = $body->transfer->netValue;
            $data['descricao'] = $body->transfer->description;
            $data['pagamentoId'] = $body->transfer->id;
            $data['fornecedor'] = $body->transfer->bankAccount->ownerName;
        }
        if ($body->bill) {
            $data['origem'] = 'bill';
            $data['dataVencimento'] = $body->bill->dueDate;
            $data['valor'] = $body->bill->value;
            $data['paymentDate'] = $body->bill->paymentDate ?? $body->bill->scheduleDate;
            $data['valorLiquido'] = $body->bill->value;
            $data['descricao'] = "{$body->bill->description} - Link do Comprovante: {$body->bill->transactionReceiptUrl}";
            $data['pagamentoId'] = $body->bill->id;
            $data['desconto'] = $body->bill->discount;
            $data['multa'] = $body->bill->fine;
            $data['juros'] = $body->bill->interest;
            $data['fornecedor'] = $body->bill->companyName;
        }
        return $data;
    }
}
