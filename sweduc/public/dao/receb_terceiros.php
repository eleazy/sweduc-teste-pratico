<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$debug = 0;

$agora = date("Y-m-d H:i:s");
$hoje = date("Y-m-d");
$finaldoc = "A";


$desconto = str_replace(',', '.', str_replace('.', '', $desconto));
$multa = str_replace(',', '.', str_replace('.', '', $multa));
$juros = str_replace(',', '.', str_replace('.', '', $juros));
$valor = str_replace(',', '.', str_replace('.', '', $valor));
$valorpago = str_replace(',', '.', str_replace('.', '', $valorpago));

if ($datavencimento == "") {
    $datavencimento = "0000-00-00";
} else {
    $dtvencimento = explode("/", $datavencimento);
    $datavencimento = $dtvencimento[2] . "-" . $dtvencimento[1] . "-" . $dtvencimento[0];
}

if ($datapagamento == "") {
    $datapagamento = $hoje;
} else {
    $dtpagamento = explode("/", $datapagamento);
    $datapagamento = $dtpagamento[2] . "-" . $dtpagamento[1] . "-" . $dtpagamento[0];
}

if ($action == "cadastrar") {
    $erro = 0;
    if ($situacao == "0") {
        $datapagamento = "0000-00-00";
        $valorpago = 0;
        $multa = 0;
        $juros = 0;
        $desconto = 0;
    }
    if ($datavalidadeForma) {
        $datavalidadeF = explode("/", $datavalidadeForma);
        $datavalidadeForma = $datavalidadeF[2] . "-" . $datavalidadeF[1] . "-" . $datavalidadeF[0];
    } else {
        $datavalidadeForma = "0000-00-00";
    }

    $datavencimento = date('Y-m-d', strtotime($datavencimento));
    $datav = $datavencimento;
    $d = new DateTime($datavencimento);


    $evtfin = explode("@", $eventosAPagar);
    $codigoeventofinanceiro = $evtfin[0];
    $eventofinanceiro = $evtfin[1];

    if (!isset($chkparcelas) || $chkparcelas < 1) {
        $qtdeParcelas = 1;
    } else {
        $qtdeParcelas = (!isset($parcelas) || $parcelas == '') ? 1 : $parcelas;
    }

    for ($cnt = 0; $cnt < $qtdeParcelas; $cnt++) {
            $doc = ($qtdeParcelas > 1) ? $documento . '-' . ($cnt + 1) : $documento;

            $query  = "INSERT INTO financeiro_terceiros (
                                          idusuario,
                                          idempresa,
                                          idcontasbanco,
                                          idfornecedor,
                                          codigoeventofinanceiro,
                                          eventofinanceiro,
                                          descritivo,
                                          documento,
                                          parcelas,
                                          parcelanum,
                                          totalparcelas,
                                          datavencimento,
                                          valor,
                                          situacao,
                                          datareaberto,
                                          datacancelado,
                                          datapagamento,
                                          valorpago,
                                          desconto,
                                          multa,
                                          juros,
                                          numeroForma,
                                          datavalidadeForma,
                                          bancoForma,
                                          agenciaForma,
                                          contaForma,
                                          outroForma ) ";
            $query .= "VALUES (
                                          $idusuario,
                                          $idempresa,
                                          $idcontasbanco,
                                          $idfornecedor,
                                          $codigoeventofinanceiro,
                                          '$eventofinanceiro',
                                          '$descritivo',
                                          '$doc',
                                          $qtdeParcelas,
                                          " . ($cnt + 1) . ",
                                          " . ($valor * $qtdeParcelas) . ",
                                          '$datav',
                                          $valor,
                                          0,
                                          '0000-00-00',
                                          '0000-00-00',
                                          '0000-00-00',
                                          0,
                                          0,
                                          0,
                                          0,
                                          '',
                                          '0000-00-00',
                                          '',
                                          '',
                                          '',
                                          '')";


        if (!($result = mysql_query($query))) {
            $erro++;
        }


            $d = new DateTime($datavencimento);
            $d->modify('+' . ($cnt + 1) . ' month');
            $datav = $d->format('Y-m-d');
    }


    if ($erro == 0) {
        echo "blue|Lançamento efetuado com sucesso.";
        $msg = "Lançamento " . $eventofinanceiro . " efetuado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro($erro) ao efetuar o lançamento.";
        $msg = "Erro($erro) ao efetuar o lançamento.";
        $status = 1;
    }
    $parametroscsv = $idusuario . ',' . $idempresa . ',' . $idfornecedor . ',' . $idcontasbanco . ',' . $idformaspagamentos . ',' . $codigoeventofinanceiro . ',' . $eventofinanceiro . ',' . $documento . ',' . $datav . ',' . $valor . ',' . $situacao . ',' . $datapagamento . ',' . $valorpago . ',' . $desconto . ',' . $multa . ',' . $juros . ',' . $numeroForma . ',' . $datavalidadeForma . ',' . $bancoForma . ',' . $agenciaForma . ',' . $contaForma . ',' . $outroForma;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "editar") {
    $erro = 0;

    $evtfin = explode("@", $eventosAPagar);
    $codigoeventofinanceiro = $evtfin[0];
    $eventofinanceiro = $evtfin[1];

    if ($situacaooriginal == "0") {
        // SE O LANÇAMENTO NÃO ESTAVA PAGO AINDA ANTES DA EDIÇÃO( SITUACAOORIGINAL=0 )
        $query  = "UPDATE financeiro_terceiros set idempresa=$idempresa, idfornecedor=$idfornecedor, codigoeventofinanceiro='$codigoeventofinanceiro', eventofinanceiro='$eventofinanceiro', idcontasbanco=$idcontasbanco, datavencimento='$datavencimento', descritivo='$descritivo', valor=$valor WHERE id=$idlancamento";
        if (!$result = mysql_query($query)) {
            $erro++;
        }
    } else {
        // SE O LANÇAMENTO ESTAVA PAGO AINDA ANTES DA EDIÇÃO( SITUACAOORIGINAL=1 )
        $query  = "INSERT INTO financeiro_terceiros (idusuario, idempresa, idfornecedor,  idcolaborador, idcontasbanco, idformaspagamentos, documento, parcelas, repeticoes, totalparcelas, datavencimento, descritivo, valor ) (SELECT idusuario, idempresa, idunidade, idfornecedor,  idcolaborador, idcontasbanco, idformaspagamentos, documento, parcelas, repeticoes, totalparcelas, datavencimento, descritivo, valor FROM contasapagar WHERE id=$idlancamento )";
        if (!$result = mysql_query($query)) {
            $erro++;
        }
        $novoid = mysql_insert_id();

        $query  = "UPDATE financeiro_terceiros SET situacao=2, datacancelado='$hoje' WHERE id=$idlancamento";
        if (!$result = mysql_query($query)) {
            $erro++;
        }
    }
    $queryLancamentosTerceiros = "SELECT codigoeventofinanceiro,eventofinanceiro,documento FROM financeiro_terceiros WHERE id=$id";
    $resultadoQueryTerceiros = mysql_query($queryLancamentosTerceiros);
    while ($linhaLancamento = mysql_fetch_array($resultadoQueryTerceiros)) {
        $codigoAtualizado = $linhaLancamento['codigoeventofinanceiro'];
        $eventoAtualizado = $linhaLancamento['eventofinanceiro'];
        $documentoAtualizado = $linhaLancamento['documento'];
    }
    if ($erro == 0) {
        echo "blue|Lançamento efetuado com sucesso.";
        $msg = "Lançamento: Código: " . $codigoAtualizado . " Evento Financeiro: " . $eventoAtualizado . " Documento: " . $documentoAtualizado . " atualizado para Situação:2.";
        $status = 0;
    } else {
        echo "red|Erro($erro) ao efetuar o lançamento." . $query;
        $msg = "Erro($erro) ao efetuar o lançamento.";
        $status = 1;
    }
    $parametroscsv = $idlancamento . ',' . $idusuario . ',' . $idempresa . ',' . $idcontasbanco . ',' . $datavencimento . ',' . $valor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "pagar") {
    $PAGvalorpago = str_replace(',', '.', str_replace('.', '', $PAGvalorpago));
    $PAGdesconto = str_replace(',', '.', str_replace('.', '', $PAGdesconto));
    $PAGmulta = str_replace(',', '.', str_replace('.', '', $PAGmulta));
    $PAGjuros = str_replace(',', '.', str_replace('.', '', $PAGjuros));
    $PAGvalor = str_replace(',', '.', str_replace('.', '', $PAGvalor));


    if ($PAGdatapagamento == "") {
        $PAGdatapagamento = $hoje;
    } else {
        $dtpagamento = explode("/", $PAGdatapagamento);
        $PAGdatapagamento = $dtpagamento[2] . "-" . $dtpagamento[1] . "-" . $dtpagamento[0];
        $datapagamento = $dtpagamento[2] . "-" . $dtpagamento[1] . "-" . $dtpagamento[0];
    }

    if ($PAGdatavalidadeForma == "") {
        $PAGdatavalidadeForma = "0000-00-00";
    } else {
        $dtvalidadeForma = explode("/", $PAGdatavalidadeForma);
        $PAGdatavalidadeForma = $dtvalidadeForma[2] . "-" . $dtvalidadeForma[1] . "-" . $dtvalidadeForma[0];
    }

    if (trim($descontoparcelas) == "") {
        $descontoparcelas = 0;
    }
    $descontoparcelas = str_replace(",", ".", str_replace(".", "", $descontoparcelas));



  // $query  = "UPDATE contasapagar SET situacao=$situacao, idcontasbanco=$idcontapagamento, idformaspagamentos=$PAGidformaspagamentos, datapagamento='$datapagamento', valorpago=$PAGvalorpago, desconto=$PAGdesconto,  multa=$PAGmulta, juros=$PAGjuros, numeroForma='$PAGnumeroForma', datavalidadeForma='$PAGdatavalidadeForma', bancoForma='$PAGbancoForma', agenciaForma='$PAGagenciaForma', contaForma='$PAGcontaForma', outroForma='$PAGoutroForma' WHERE id=".$idlancamento;
    $query  = "UPDATE financeiro_terceiros SET
                              situacao=$situacao,
                              idformapagamento=$PAGidformaspagamentos,
                              datapagamento='$datapagamento',
                              valorpago=$PAGvalorpago,
                              desconto=$PAGdesconto,
                              multa=$PAGmulta,
                              juros=$PAGjuros,
                              numeroForma='$PAGnumeroForma',
                              datavalidadeForma='$PAGdatavalidadeForma',
                              bancoForma='$PAGbancoForma',
                              agenciaForma='$PAGagenciaForma',
                              contaForma='$PAGcontaForma',
                              outroForma='$PAGoutroForma'
                            WHERE id=" . $idlancamento;

    if (!($result = mysql_query($query))) {
        $erro++;
    }

  // $number = ($PAGvalor-$PAGdesconto+$PAGmulta+$PAGjuros);
  // if ($PAGvalorpago < round($number, 2) ){
  //   $query  = "INSERT INTO financeiro_terceiros (idusuario, idempresa, idfornecedor, idcontasbanco, idformaspagamentos, documento, parcelas, totalparcelas, datavencimento, valor, situacao, datapagamento, valorpago, desconto, multa, juros) (SELECT idusuario, idempresa, idfornecedor, idcontasbanco, idformaspagamentos, documento, parcelas, totalparcelas, datavencimento, valor, situacao, datapagamento, valorpago, desconto, multa, juros FROM contasapagar WHERE id=".$idlancamento. " )";
  //   $result = mysql_query($query);
  //   $novoid = mysql_insert_id();

  //   $query  = "UPDATE financeiro_terceiros SET valorpago=0, situacao=0, multa=0, desconto=0, juros=0, valor=".($PAGvalor-$PAGvalorpago).", parcelas=CONCAT ( LEFT(parcelas, LENGTH(parcelas)-1 ), CHAR ( ASCII ( RIGHT(parcelas,1)  )+1 )  ) WHERE id=".$novoid;
  //   if ( !($result = mysql_query($query)))   $erro++;

  // }
    $queryLancamentosTerceiros = "SELECT codigoeventofinanceiro,eventofinanceiro,documento FROM financeiro_terceiros WHERE id=$idlancamento";
    $resultadoQueryTerceiros = mysql_query($queryLancamentosTerceiros);
    while ($linhaLancamento = mysql_fetch_array($resultadoQueryTerceiros)) {
        $codigoPago = $linhaLancamento['codigoeventofinanceiro'];
        $eventoPago = $linhaLancamento['eventofinanceiro'];
        $documentoPago = $linhaLancamento['documento'];
    }
    if ($erro == 0) {
        echo "blue|Pagamento efetuado com sucesso.";
        $msg = "Pagamento efetuado com sucesso. Código : " . $codigoPago . " Evento Pago: " . $eventoPago . " Documento Pago: " . $documentoPago;
        $status = 0;
    } else {
        echo "red|Erro($erro) ao efetuar o pagamento.";
        $msg = "Erro($erro) ao efetuar o pagamento.";
        $status = 1;
    }
    $parametroscsv = $idlancamento . ',' . $situacao . ',' . $PAGidformaspagamentos . ',' . $datapagamento . ',' . $PAGvalorpago . ',' . $PAGdesconto . ',' . $PAGmulta . ',' . $PAGjuros . ',' . $PAGnumeroForma . ',' . $PAGdatavalidadeForma . ',' . $PAGbancoForma . ',' . $PAGagenciaForma . ',' . $PAGcontaForma . ',' . $PAGoutroForma;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cancelar") {
    $query  = "UPDATE financeiro_terceiros SET situacao=2, datacancelado='$hoje' WHERE id=$id";
    $queryLancamentosTerceiros = "SELECT codigoeventofinanceiro,eventofinanceiro,documento FROM financeiro_terceiros WHERE id=$id";
    $resultadoQueryTerceiros = mysql_query($queryLancamentosTerceiros);
    while ($linhaLancamento = mysql_fetch_array($resultadoQueryTerceiros)) {
        $codigoCancelado = $linhaLancamento['codigoeventofinanceiro'];
        $eventoCancelado = $linhaLancamento['eventofinanceiro'];
        $documentoCancelado = $linhaLancamento['documento'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Lançamento cancelado com sucesso.";
        $msg = "Lançamento cancelado com sucesso. Código: " . $codigoCancelado . " Evento Cancelado: " . $eventoCancelado . " Documento Cancelado: " . $documentoCancelado;
        $status = 0;
    } else {
        echo "red|Erro ao cancelar lançamento.";
        $msg = "Erro ao cancelar lançamento.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagar") {
    $query  = "DELETE FROM financeiro_terceiros WHERE id=$id";
    $queryLancamentosTerceiros = "SELECT codigoeventofinanceiro,eventofinanceiro,documento FROM financeiro_terceiros WHERE id=$id";
    $resultadoQueryTerceiros = mysql_query($queryLancamentosTerceiros);
    while ($linhaLancamento = mysql_fetch_array($resultadoQueryTerceiros)) {
        $codigoDeletado = $linhaLancamento['codigoeventofinanceiro'];
        $eventoDeletado = $linhaLancamento['eventofinanceiro'];
        $documentoDeletado = $linhaLancamento['documento'];
    }
     $msg = "Lançamento apagado com sucesso. Código: " . $codigoDeletado . "Evento: " . $eventoDeletado . " Documento: " . $documentoDeletado;
    if ($result = mysql_query($query)) {
        echo "blue|Lançamento apagado com sucesso.";

        $status = 0;
    } else {
        echo "red|Erro ao apagar lançamento.";
        $msg = "Erro ao apagar lançamento.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "reabrir") {
  // $query = "INSERT INTO contasapagar (idusuario, idempresa, idfornecedor, idfuncionario, idcontasbanco, idformaspagamentos, codigoeventofinanceiro, eventofinanceiro, documento, parcelas, repeticoes, totalparcelas, datavencimento, valor, situacao, datareaberto, datacancelado, datapagamento, valorpago, desconto, multa, juros, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma, descritivo) (SELECT idusuario, idempresa, idfornecedor, idfuncionario, idcontasbanco, idformaspagamentos, codigoeventofinanceiro, eventofinanceiro, documento, parcelas, repeticoes, totalparcelas, datavencimento, valor, situacao, datareaberto, datacancelado, datapagamento, valorpago, desconto, multa, juros, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma, descritivo FROM contasapagar WHERE id=$id )";
    $query = "INSERT INTO financeiro_terceiros (
                                          idusuario,
                                          idempresa,
                                          idcontasbanco,
                                          idfornecedor,
                                          codigoeventofinanceiro,
                                          eventofinanceiro,
                                          descritivo,
                                          documento,
                                          parcelas,
                                          parcelanum,
                                          totalparcelas,
                                          datavencimento,
                                          valor,
                                          situacao,
                                          datareaberto,
                                          datacancelado,
                                          datapagamento,
                                          valorpago,
                                          desconto,
                                          multa,
                                          juros,
                                          numeroForma,
                                          datavalidadeForma,
                                          bancoForma,
                                          agenciaForma,
                                          contaForma,
                                          outroForma)
                                       (SELECT
                                          idusuario,
                                          idempresa,
                                          idcontasbanco,
                                          idfornecedor,
                                          codigoeventofinanceiro,
                                          eventofinanceiro,
                                          descritivo,
                                          documento,
                                          parcelas,
                                          parcelanum,
                                          totalparcelas,
                                          datavencimento,
                                          valor,
                                          situacao,
                                          datareaberto,
                                          datacancelado,
                                          datapagamento,
                                          valorpago,
                                          desconto,
                                          multa,
                                          juros,
                                          numeroForma,
                                          datavalidadeForma,
                                          bancoForma,
                                          agenciaForma,
                                          contaForma,
                                          outroForma
                                        FROM financeiro_terceiros WHERE id=$id )";

  // print '*1*'.$query.'*1*';

    if ($result = mysql_query($query)) {
        $idnovo = mysql_insert_id();
        $query  = "UPDATE financeiro_terceiros SET situacao=3, datareaberto='$hoje' WHERE id=$id";
      // print '*2*'.$query.'**';
        if ($result = mysql_query($query)) {
            $query  = "UPDATE financeiro_terceiros SET
                                          situacao=0,
                                          datareaberto='0000-00-00',
                                          datacancelado='0000-00-00',
                                          datapagamento='0000-00-00',
                                          valorpago=0,
                                          desconto=0,
                                          multa=0,
                                          juros=0,
                                          numeroForma='',
                                          datavalidadeForma='0000-00-00',
                                          bancoForma='',
                                          agenciaForma='',
                                          contaForma='',
                                          outroForma=''
                                        WHERE id=$idnovo";

          // print '*3*'.$query.'**';
            $queryLancamentosTerceiros = "SELECT codigoeventofinanceiro,eventofinanceiro,documento FROM financeiro_terceiros WHERE id=$id";
            $resultadoQueryTerceiros = mysql_query($queryLancamentosTerceiros);
            while ($linhaLancamento = mysql_fetch_array($resultadoQueryTerceiros)) {
                $codigoReaberto = $linhaLancamento['codigoeventofinanceiro'];
                $eventoReaberto = $linhaLancamento['eventofinanceiro'];
                $documentoReaberto = $linhaLancamento['documento'];
            }
            if ($result = mysql_query($query)) {
                echo "blue|Lançamento reaberto com sucesso.";
                $msg = "Lançamento reaberto com sucesso. Código: " . $codigoReaberto . " Evento Financeiro: " . $eventoReaberto . " Documento: " . $documentoReaberto;
                $status = 0;
            } else {
                echo "red|Erro 3 ao reabrir lançamento.";
                $msg = "Erro 3 ao reabrir lançamento.";
                $status = 1;
            }
        } else {
            echo "red|Erro 2 ao reabrir lançamento.";
            $msg = "Erro 2 ao reabrir lançamento.";
            $status = 1;
        }
    } else {
        echo "red|Erro 2 ao reabrir lançamento.";
        $msg = "Erro 1 ao reabrir lançamento.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $idnovo;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
