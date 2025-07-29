<?php

use Carbon\Carbon;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use App\Financeiro\Pix\GeradorPixSantander;
use App\Financeiro\Boleto\GeradorBoletoSantander;

include('../headers.php');
include('conectar.php');
include_once('logs.php');
include('../function/ultilidades.func.php');

require_once('trocasenha.php');
require_once("mysql.class.php");
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

if ($action == "resultadoCobranca") {

    $cobrancasPix = new GeradorPixSantander();
    $cobrancasBoletos = new GeradorBoletoSantander();

    $resultadoPix = $cobrancasPix->gerarCobrancas(
        $idffinII,
        $idbancoloteII,
        $banconome,
        $versao,
    );

    $resultadoBoletos = $cobrancasBoletos->gerarCobrancas(
        $idffinII,
        $idbancoloteII,
        $banconome,
        $versao,
    );

    $resultadoPix = explode("@", $resultadoPix);
    $sucessoPix = rtrim($resultadoPix[0], ', ');
    $erroPix = rtrim($resultadoPix[1], ', ');

    $resultadoBoletos = explode("@", $resultadoBoletos);
    $sucessoBoletos = rtrim($resultadoBoletos[0], ', ');
    $erroBoletos = rtrim($resultadoBoletos[1], ', ');

    if ($sucessoPix) {
        echo "green|Pix gerado(s) com sucesso dos Título(s) " . $sucessoPix . "|";
        $status = 0;
    }
    if ($erroPix) {
        echo "red|Erro ao gerar Pix do(s) Título(s) " . $erroPix;
        $status = 1;
    }

    echo "@";

    if ($sucessoBoletos) {
        echo "green|Boletos gerado(s) com sucesso dos Título(s) " . $sucessoBoletos . "|";
        $status = 0;
    }
    if ($erroBoletos) {
        echo "red|Erro ao gerar Boletos do(s) Título(s) " . $erroBoletos;
        $status = 1;
    }

    $parametroscsv = $sucessoPix. ',' . $erroPix . ',' . $sucessoBoletos. ',' . $erroBoletos;
    salvaLog($idfuncionario, __FILE__, $action, $status, $parametroscsv, $msg);

} elseif ($action == "removecobranca") {

    $query  = "UPDATE alunos_fichafinanceira SET pix_gerado=0, pix_criacao=NULL WHERE id IN ($idfichafinanceira)";
    if ($result = mysql_query($query)) {
        $msg = "Removido.|";
        echo "ok";
    } else {
        $msg = "Erro na atualização.|";
        echo "erro";
    }

    $query  = "UPDATE alunos_fichafinanceira SET boleto_gerado=0, boleto_criacao=NULL WHERE id IN ($idfichafinanceira)";
    if ($result = mysql_query($query)) {
        $msg = "Removido.|";
        echo "ok";
    } else {
        $msg = "Erro na atualização.|";
        echo "erro";
    }
}
