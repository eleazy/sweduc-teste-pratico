<?php

namespace App\Financeiro\Controller\Relatorios;

use App\Framework\Http\BaseController;
use DateTime;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Atualmente este controller apenas lida com o processamento de dados para o gráfico de balancete
class BalanceteController extends BaseController
{
    public function getBalanceteGrafData(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $empresasIds = implode(',', $data['idempresa']);
        $periodode = DateTime::createFromFormat('d/m/Y', $data['periodode'])->format('Y-m-d');
        $periodoate = DateTime::createFromFormat('d/m/Y', $data['periodoate'])->format('Y-m-d');
        $tipo = $data['tipo'];
        $condicao = $data['condicao'];

        //________________RECEBIMENTOS__________________

        if ($tipo == 'datavencimento') {  //VECIMENTO NO PERÍODO
            $condicoes = " ( situacao=0 OR situacao=1 OR situacao=6 ) AND (alunos_fichafinanceira.datavencimento BETWEEN '$periodode' AND '$periodoate') ";
            $condicoesTerceiros = " ( situacao=0 OR situacao=1 ) AND (financeiro_terceiros.datavencimento BETWEEN '$periodode' AND '$periodoate') ";
        } elseif ($tipo == 'datarecebimento') {
            if ($condicao == 'recebidos') {  //RECEBIDOS NO PERÍODO
                $condicoes = " ( situacao=1 OR situacao=6 ) AND (alunos_fichafinanceira.datarecebimento BETWEEN '$periodode' AND '$periodoate') ";
                $condicoesTerceiros = " situacao=1 AND datapagamento BETWEEN '$periodode' AND '$periodoate' ";
            } elseif ($condicao == 'vencendo') { //RECEBIDOS NO PERÍODO E COM VENCIMENTO NO PERÍODO
                $condicoes = " ( situacao=1 OR situacao=6 ) AND (alunos_fichafinanceira.datarecebimento BETWEEN '$periodode' AND '$periodoate') AND (alunos_fichafinanceira.datavencimento BETWEEN '$periodode' AND '$periodoate')";
                $condicoesTerceiros = " situacao=1 AND (datapagamento BETWEEN '$periodode' AND '$periodoate') AND (datavencimento BETWEEN '$periodode' AND '$periodoate')";
            } elseif ($condicao == 'antecipados') { //RECEBIDOS NO PERÍODO E COM VENCIMENTO APÓS O PERIODO
                $condicoes = " ( situacao=1 OR situacao=6 ) AND (alunos_fichafinanceira.datarecebimento BETWEEN '$periodode' AND '$periodoate') AND (alunos_fichafinanceira.datavencimento > '$periodoate') ";
                $condicoesTerceiros = " situacao=1 AND (datapagamento BETWEEN '$periodode' AND '$periodoate') AND (datavencimento > '$periodoate') ";
            } elseif ($condicao == 'atrasados') { //RECEBIDOS NO PERÍODO E COM VENCIMENTO ANTERIOR AO PERIODO
                $condicoes = " ( situacao=1 OR situacao=6 ) AND (alunos_fichafinanceira.datarecebimento BETWEEN '$periodode' AND '$periodoate') AND (alunos_fichafinanceira.datavencimento < '$periodode') ";
                $condicoesTerceiros = " situacao=1 AND (datapagamento BETWEEN '$periodode' AND '$periodoate') AND (datavencimento < '$periodode') ";
            }
        }

        $queryRecebidos = "SELECT
            SUM(valorrecebido) as totalrecebido
        FROM alunos_fichafinanceira
        INNER JOIN alunos_matriculas ON alunos_fichafinanceira.idaluno=alunos_matriculas.idaluno
        INNER JOIN contasbanco ON alunos_fichafinanceira.idcontasbanco=contasbanco.id
        WHERE $condicoes
        AND alunos_fichafinanceira.nummatricula=alunos_matriculas.nummatricula
        AND contasbanco.idempresa IN ($empresasIds)";

        $totalRecebidoResults = DB::select($queryRecebidos);

        $queryTerceiros = "SELECT SUM(valorpago) as totalTerceiros FROM financeiro_terceiros WHERE " . $condicoesTerceiros . " AND idempresa IN ($empresasIds)";
        $totalTerceirosResults = DB::select($queryTerceiros);

        //________________________________DESPESAS__________________________________

        if ($tipo == 'datavencimento') {  //VECIMENTO NO PERÍODO
            $condicoes = " ( situacao=0 OR situacao=1 OR situacao=6 ) AND (datavencimento BETWEEN '$periodode' AND '$periodoate') ";
        } elseif ($tipo == 'datarecebimento') {
            if ($condicao == 'recebidos') {  //RECEBIDOS NO PERÍODO
                $condicoes = " ( datapagamento BETWEEN '$periodode' AND '$periodoate') ";
            } elseif ($condicao == 'vencendo') { //RECEBIDOS NO PERÍODO E COM VENCIMENTO NO PERÍODO
                $condicoes = " ( datapagamento BETWEEN '$periodode' AND '$periodoate') AND ( datavencimento BETWEEN '$periodode' AND '$periodoate')";
            } elseif ($condicao == 'antecipados') { //RECEBIDOS NO PERÍODO E COM VENCIMENTO APÓS O PERIODO
                $condicoes = " ( datapagamento BETWEEN '$periodode' AND '$periodoate') AND ( datavencimento > '$periodoate') ";
            } elseif ($condicao == 'atrasados') { //RECEBIDOS NO PERÍODO E COM VENCIMENTO ANTERIOR AO PERIODO
                $condicoes = " ( datapagamento BETWEEN '$periodode' AND '$periodoate') AND ( datavencimento < '$periodode') ";
            }
        }

        $queryDespesas = "SELECT SUM(valorpago) as totalvalorpago FROM contasapagar WHERE idempresa IN ($empresasIds) AND situacao=1 AND codigoeventofinanceiro != 21180100 AND $condicoes";
        $totalDespesasResults = DB::select($queryDespesas);

        $totalRecebido = $totalRecebidoResults[0]->totalrecebido + $totalTerceirosResults[0]->totalTerceiros;
        $totalDespesas = $totalDespesasResults[0]->totalvalorpago;
        $response = $this->createResponse();
        $response->getBody()->write(json_encode(['recebidos' => $totalRecebido, 'despesas' => $totalDespesas]));
        return $response;
    }
}
