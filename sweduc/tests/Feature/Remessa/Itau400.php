<?php

declare(strict_types=1);

namespace Tests\Feature\Remessa;

use App\Academico\Model\Matricula;
use App\Model\Core\Funcionario;
use App\Model\Financeiro\Conta;
use App\Model\Financeiro\Titulo;
use App\Framework\Model;
use Carbon\CarbonImmutable;

use function App\Framework\logger;
use function Pest\Faker\faker;

function preparaTitulos() {
    Model::unguard();

    $conta = Conta::create([
        'id' =>  null,
        'idempresa' => '1',
        'idfuncionario' => '0',
        'tipo' => '0',
        'nomeb' => 'ITAU CEMS',
        'banconum' => '341',
        'banconome' => 'Itaú',
        'bancoarquivo' => 'itau',
        'agencia' => '0305',
        'dvagencia' => '0',
        'conta' => '61392-9',
        'carteira' => '109',
        'convenio' => '61392-9',
        'saldoinicial' => '0',
        'datainicial' => '0000-00-00',
        'saldoremanescente' => '0',
        'saldoatual' => '3554350',
        'dataatual' => '0000-00-00',
        'tarifaboleto' => '0',
        'mensagemboleto' =>  '<span style=\"color:white; font-size:1px\">juros</span>Após o vencimento cobrar multa de R$@(($valor*2)/100)\r\n<span style=\"color:white; font-size:1px\">juros</span>Após o vencimento cobrar juros ao dia de R$@(($valor*1)/100)/30',
        'mensagemboletopadrao' =>  '<span style=\"color:white; font-size:1px\">juros</span>Após o vencimento cobrar multa de R$@(($valor*2)/100)\r\n<span style=\"color:white; font-size:1px\">juros</span>Após o vencimento cobrar juros ao dia de R$@(($valor*1)/100)/30',
        'remessaarq' => '0',
        'remessalote' => '84',
        'codTransmissao' =>  '',
        'codComplemento' =>  '',
        'valor_baixa_min' => '30',
        'baixa_dias' => '30',
        'diautil' => '0',
        'diautil_acrescimo' => '0',
        'remessa_desc1_dia' => '0',
        'remessa_desc1_mesatual' => '0',
        'remessa_desc1_desc' => '0',
        'remessa_desc1_valor' => '0',
        'remessa_desc2_dia' => '0',
        'remessa_desc2_mesatual' => '0',
        'remessa_desc2_desc' => '0',
        'remessa_desc2_valor' => '0',
        'remessa_desc3_dia' => '0',
        'remessa_desc3_mesatual' => '0',
        'remessa_desc3_desc' => '0',
        'remessa_desc3_valor' => '0',
        'remessa_tipo' => '1',
        'retorno_tipo' => '16',
        'retorno_data_baixa' => '0',
        'desativado_em' =>  null,
        'pagamento_online_provedor' =>  null,
        'pagamento_online_ambiente_producao' => '0',
        'pagamento_online_token' =>  null,
        'pagamento_online_pv' =>  null,
        'pagamento_online_forma_pagamento_id' =>  null,
        'pagamento_online_funcionario_id' =>  null,
        'pagamento_online_tarifa' =>  null,
        'pagamento_online_baixa_dias' =>  null,
    ]);

    $funcionario = Funcionario::create([
        'id' => null,
        'idpessoa' => '2',
        'idunidade' => '2',
        'iddepartamento' => '8',
        'professor' => '0',
        'numeroprof' => '0',
        'cursoprof' => '0',
        'pis' => '',
        'grauescolar' => '',
        'formacaoescolar' => '',
        'comissao' => '0',
        'banco' => '',
        'conta' => '',
        'agencia' => '',
        'registro_mec' => '',
    ]);

    $matricula = Matricula::create([
        'id' => null,
        'idfuncionario' => '1',
        'idunidade' => '2',
        'idempresa' => '2',
        'anoletivomatricula' => '1',
        'idaluno' => '1',
        'status' => '4',
        'datastatus' => '2015-02-03 12:33:00',
        'turmamatricula' => '79',
        'nummatricula' => '1',
        'datamatricula' => '2014-08-19',
        'qtdparcelas' => '0',
        'valorAnuidade' => '0.00',
        'bolsa' => '0.00',
        'bolsapercentual' => '0.00',
        'bolsa_motivo' => null,
        'bolsa_motivo_id' => null,
        'idplanohorario' => '1',
        'seguroescolar' => '0',
        'recebereajuste' => '1',
        'reajustado' => '0',
        'datareajuste' => null,
        'escoladestino' => null,
        'obsSituacao' => null,
        'motivoSituacao' => null,
        'presencial' => '1',
        'created_at' => '2021-02-19 20:13:31',
        'updated_at' => null,
    ]);

    $vencimento = CarbonImmutable::now();

    $contagem = 10;

    foreach (range(1, $contagem) as $i) {
        Titulo::gerar(
            $conta,
            $funcionario,
            $matricula,
            $vencimento,
            faker()->randomFloat(2, 100, 1000),
        );
    }
}

test('gera remessa com sucesso', function () {
    preparaTitulos();
    // $banconum = '104';
    // $idffin = '1';
    // $idbancolote = 1;
    // $versao = null;
    // $tamanho = '400';
    // $desc3 = null;
    // $bruto = false;

    // 'idffin' => string '806802,817499,820052,822779,836489,859822,869214,882442,882478,882907,882933,893947,836842,846656,859783,864183,864856,872398,876502,876528,883164,817192' (length=153)
    // 'idbancolote' => string '164' (length=3)
    // 'versao' => string '0' (length=1)
    // 'tamanho' => string '240' (length=3)
    // 'desc3' => string '0' (length=1)
    // 'bruto' => string '0' (length=1)

    // remessa_retorno

    $http = logadoComoAdmin();

    $idffin = Titulo::all()->pluck('id')->join(',');

    $response = $http->post('/remessa_criarlote.php', [
        'form_params' => [
            'idffin' => $idffin,
            'idbancolote' => '164',
            'versao' => '0',
            'tamanho' => '240',
            'desc3' => '0',
            'bruto' => '0',
        ],
    ]);

    logger()->debug($response->getBody()->getContents());
});
