<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Controller\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Financeiro\Titulo;
use App\Asaas\Models\Asaas;
use App\Model\Financeiro\Conta;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller de contas bancárias, de funcionários e escola
 */
class ContasAPIController extends Controller
{
    public function index(ServerRequestInterface $request)
    {
        $query = $request->getQueryParams();
        $contas = Conta::query();

        if ($query['unidade']) {
            $filtroUnidade = filter_var($query['unidade'], FILTER_VALIDATE_INT);
            $contas->whereHas('unidades', fn($q) => $q->where('unidades.id', $filtroUnidade));
        }

        if ($query['tipo']) {
            switch ($query['tipo']) {
                case 'banco':
                    $contas->banco();
                    break;
                case 'escola':
                    $contas->escola();
                    break;
                case 'funcionario':
                    $contas->funcionarios();
                    break;
            }
        }

        return $contas->get();
    }

    /**
     * Criação e alteração de cadastro
     *
     * @param [type] $args
     * @return void
     */
    public function storeOrUpdate(ServerRequestInterface $request, $args)
    {
        $datainicial = null;
        $id = $args['id'] ?? null;

        $input = $this->jsonInput($request);
        $tipo = $input->tipo;

        if (isset($input->datainicial) && trim($input->datainicial)) {
            $dtinicial = explode("/", $input->datainicial);
            $datainicial = $dtinicial[2] . "-" . $dtinicial[1] . "-" . $dtinicial[0];
        }

        if ($tipo == Conta::TIPO_BANCO) {
            $banco = explode("@", $input->banco);
            $banconum = $banco[0];
            $banconome = $banco[1];
            $bancoarquivo = $banco[2];
        } elseif ($tipo == Conta::TIPO_FUNCIONARIO) {
            $banconum = 0;
            $banconome = "";
            $bancoarquivo = "";
        } elseif ($tipo == Conta::TIPO_ESCOLA) {
            $banconum = 0;
            $banconome = "CAIXA";
            $bancoarquivo = "carne";
        }

        $saldoinicial = str_replace(',', '.', str_replace('.', '', $input->saldoinicial));
        $tarifaboleto = str_replace(',', '.', str_replace('.', '', $input->tarifaboleto));

        $conta = !empty($id) ? Conta::find($id) : new Conta();
        $conta->nomeb = $input->nomeb;
        $conta->idempresa = $input->idempresa;
        $conta->idfuncionario = $input->idfuncionario ?? 0;
        $conta->tipo = $tipo;
        $conta->banconum = $banconum;
        $conta->banconome = $banconome;
        $conta->bancoarquivo = $bancoarquivo;
        $conta->remessaarq             = $input->remessaarq ?? '0';

        $conta->agencia                = !empty($input->agencia) ? $input->agencia : '';
        $conta->dvagencia              = $input->dvagencia ?? 0;
        $conta->conta                  = $input->conta ?? '';
        $conta->carteira               = $input->carteira ?? '';
        $conta->convenio               = $input->convenio ?? '';
        $conta->chave_pix              = !empty($input->chave_pix) ? $input->chave_pix : 0;
        $conta->numero_operacao        = !empty($input->numero_operacao) ? $input->numero_operacao : 0;
        $conta->saldoinicial           = $saldoinicial ?? 0;

        if ($datainicial) {
            $conta->datainicial = $datainicial;
        }

        $conta->tarifaboleto           = $tarifaboleto;
        $conta->codTransmissao         = $input->codTransmissao ?? null;
        $conta->codComplemento         = $input->codComplemento ?? null;
        $conta->diautil                = $input->diautil ?? 0;
        $conta->baixa_dias             = $input->baixa_dias ?? 0;
        $conta->diautil_acrescimo      = str_replace(',', '.', $input->diautil_acrescimo) ?: 0;

        $conta->mensagemboleto         = $input->mensagemboleto ?? '';
        $conta->mensagemboletopadrao   = $input->mensagemboletopadrao ?? $conta->mensagemboleto;

        $conta->remessa_desc1_dia      = $input->remessa_desc1_dia ?? 0;
        $conta->remessa_desc1_mesatual = $input->remessa_desc1_mesatual ?? 0;
        $conta->remessa_desc1_desc     = $input->remessa_desc1_desc ?? 0;
        $conta->remessa_desc1_valor    = str_replace(',', '.', (string) $input->remessa_desc1_valor ?? 0);

        $conta->remessa_desc2_dia      = $input->remessa_desc2_dia ?? 0;
        $conta->remessa_desc2_mesatual = $input->remessa_desc2_mesatual ?? 0;
        $conta->remessa_desc2_desc     = $input->remessa_desc2_desc ?? 0;
        $conta->remessa_desc2_valor    = str_replace(',', '.', (string) $input->remessa_desc2_valor ?? 0);

        $conta->remessa_desc3_dia      = $input->remessa_desc3_dia ?? 0;
        $conta->remessa_desc3_mesatual = $input->remessa_desc3_mesatual ?? 0;
        $conta->remessa_desc3_desc     = $input->remessa_desc3_desc ?? 0;
        $conta->remessa_desc3_valor    = str_replace(',', '.', (string) $input->remessa_desc3_valor ?? 0);

        $conta->remessa_tipo           = $input->remessa_tipo ?? null;
        $conta->retorno_tipo           = $input->retorno_tipo ?? null;
        $conta->retorno_data_baixa     = $input->retorno_data_baixa ?? 0;
        $conta->valor_baixa_min        = $input->valor_baixa_min ?? null;

        $conta->usabancoAPI            = in_array($banconome, ['asaas']);

        $conta->saveOrFail();

        if ($conta->banconum == 461) {
            // ATUALIZA O DESCONTO NO ASAAS
            //$this->atualizaDescontosAsaas($conta->id);
        }

        return $this->jsonResponse($conta);
        // $parametroscsv = $query;
        // salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }

    private function atualizaDescontosAsaas($idConta)
    {
        $titulosUsandoDescontoBanco = DB::select("
            SELECT af.id
            FROM alunos_fichafinanceira af
            JOIN asaas_cobrancas ac ON ac.id_alunos_fichafinanceira = af.id
            JOIN alunos a ON a.id = af.idaluno
            WHERE a.desconto_comercial = 0
            AND af.id_desconto_comercial IS NULL
            AND af.situacao = 0
            AND af.idcontasbanco = $idConta
        ");

        $asaas = new Asaas();
        foreach ($titulosUsandoDescontoBanco as $titulo) {
            try {
                $titulo = Titulo::find($titulo->id);
                $asaas->atualizaDescontos($titulo);
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
