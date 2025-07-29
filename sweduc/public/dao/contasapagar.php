<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

function convertDate($date)
{
    if (strstr($date, "-") || strstr($date, "/")) {
        $date = preg_split("/[\/]|[-]+/", $date);
        $date = $date[2] . "-" . $date[1] . "-" . $date[0];
        return $date;
    }
    return false;
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

if ($action == "cadastrar") {
    if (trim($descontoparcelas) == "") {
        $descontoparcelas = 0;
    }
    $descontoparcelas = str_replace(",", ".", str_replace(".", "", $descontoparcelas));
    $colaborador = $idcolaborador ?? 0;
    $fornecedor = $idfornecedor ?? 0;
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
    $data_competencia = convertDate($data_competencia);

    $evtfin = explode("@", $eventosAPagar);
    $codigoeventofinanceiro = $evtfin[0];
    $eventofinanceiro = $evtfin[1];

    if ($chkrepeticoes && $repeticoes > 1) {
        for ($cnt = 0; $cnt < $repeticoes; $cnt++) {
            $doc = $documento . $finaldoc;
            $finaldoc++;

            $query  = "INSERT INTO contasapagar (idusuario, idempresa, idfornecedor, idcolaborador, idcontasbanco, idformaspagamentos,
                codigoeventofinanceiro, eventofinanceiro, documento, parcelas, repeticoes, totalparcelas, datavencimento,
                valor, situacao, datapagamento, valorpago, desconto, multa, juros, numeroForma, datavalidadeForma, bancoForma,
                agenciaForma, contaForma, outroForma, descritivo, data_competencia) ";
            $parametros = [
                $idusuario,
                $idempresa,
                $fornecedor,
                $colaborador,
                $idcontasbanco,
                $idformaspagamentos,
                $codigoeventofinanceiro,
                $eventofinanceiro,
                $doc,
                '0',
                $cnt + 1,
                $repeticoes,
                $datav,
                $valor,
                $situacao,
                $datapagamento,
                $valorpago,
                $desconto,
                $multa,
                $juros,
                $numeroForma,
                $datavalidadeForma,
                $bancoForma,
                $agenciaForma,
                $contaForma,
                $outroForma,
                $descritivo,
                $data_competencia
            ];
            $parametros_escapados = array_map("mysql_real_escape_string", $parametros);
            $query .= "VALUES ('" . implode('\',\'', $parametros_escapados) . "')";
            $result = mysql_query($query);

            $d = new DateTime($datavencimento);
            $d->modify('+' . ($cnt + 1) . ' month');
            $datav = $d->format('Y-m-d');
        }
    } elseif ($chkparcelas && $parcelas > 1) {
        for ($cnt = 0; $cnt < $parcelas; $cnt++) {
            $query  = "INSERT INTO contasapagar (idusuario, idempresa, idfornecedor,  idcolaborador, idcontasbanco, idformaspagamentos, codigoeventofinanceiro, eventofinanceiro, documento, parcelas, repeticoes, totalparcelas, datavencimento, valor, situacao, datapagamento, valorpago, desconto, multa, juros, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma, descritivo, data_competencia) ";
            $parametros = [$idusuario, $idempresa, $fornecedor,  $colaborador, $idcontasbanco, $idformaspagamentos, $codigoeventofinanceiro, $eventofinanceiro, $documento, $cnt + 1, '0', $parcelas, $datav, $valor, $situacao, $datapagamento, $valorpago, $desconto, $multa, $juros, $numeroForma, $datavalidadeForma, $bancoForma, $agenciaForma, $contaForma, $outroForma, $descritivo, $data_competencia];
            $parametros_escapados = array_map("mysql_real_escape_string", $parametros);
            $query .= "VALUES ('" . implode('\',\'', $parametros_escapados) . "')";
            $result = mysql_query($query);

            $d = new DateTime($datavencimento);
            $d->modify('+' . ($cnt + 1) . ' month');
            $datav = $d->format('Y-m-d');
        }
    } elseif (!$chkrepeticoes && !$chkparcelas) {
        $parametros = [$idusuario, $idempresa, $fornecedor,  $colaborador, $idcontasbanco, $idformaspagamentos, $codigoeventofinanceiro, $eventofinanceiro, $documento, '0', '0', '0', $datav, $valor, $situacao, $datapagamento, $valorpago, $desconto, $multa, $juros, $numeroForma, $datavalidadeForma, $bancoForma, $agenciaForma, $contaForma, $outroForma, $descritivo, $data_competencia];

        $parametros_escapados = array_map("mysql_real_escape_string", $parametros);

        $query  = "INSERT INTO contasapagar (idusuario, idempresa, idfornecedor, idcolaborador, idcontasbanco, idformaspagamentos, codigoeventofinanceiro, eventofinanceiro, documento, parcelas, repeticoes, totalparcelas, datavencimento, valor, situacao, datapagamento, valorpago, desconto, multa, juros, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma, descritivo, data_competencia ) ";
        $query .= "VALUES ('" . implode('\',\'', $parametros_escapados) . "')";
        $result = mysql_query($query);
    } else {
        $result = false;
    }

    if ($result) {
        echo "blue|Lançamento efetuado com sucesso.|" . mysql_insert_id();
        $msg = "Lançamento do fornecedor " . $fornecedor . " efetuado com sucesso.";
        $status = 0;
    } else {
        $erro = mysql_error();
        echo "red|Erro($erro) ao efetuar o lançamento.";
        $msg = "Erro($erro) ao efetuar o lançamento.";
        $status = 1;
        http_response_code(400);
    }
    $parametroscsv = $idusuario . ',' . $idempresa . ',' . $fornecedor . ',' . $idcontasbanco . ',' . $idformaspagamentos . ',' . $codigoeventofinanceiro . ',' . $eventofinanceiro . ',' . $documento . ',' . $datav . ',' . $valor . ',' . $situacao . ',' . $datapagamento . ',' . $valorpago . ',' . $desconto . ',' . $multa . ',' . $juros . ',' . $numeroForma . ',' . $datavalidadeForma . ',' . $bancoForma . ',' . $agenciaForma . ',' . $contaForma . ',' . $outroForma;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "editar") {
    $erro = 0;
    $colaborador = $idcolaborador ?? 0;
    $fornecedor = $idfornecedor ?? 0;
    $evtfin = explode("@", $eventosAPagar);
    $codigoeventofinanceiro = $evtfin[0];
    $eventofinanceiro = $evtfin[1];
    $autorizado_por_id = ($autorizado_por_id == -1) ? 'null' : $autorizado_por_id;
    $data_competencia = convertDate($data_competencia);

    // Escapa variáveis para impedir caracteres especiais de quebrar o código e impedir SQL Injection
    $parametros = compact('idempresa', 'fornecedor', 'colaborador', 'codigoeventofinanceiro', 'eventofinanceiro', 'idcontasbanco', 'datavencimento', 'descritivo', 'valor', 'idlancamento', 'hoje');
    $parametros_escapados = array_map("mysql_real_escape_string", $parametros);
    extract($parametros_escapados);

    if ($situacaooriginal == "0") {
        // SE O LANÇAMENTO NÃO ESTAVA PAGO AINDA ANTES DA EDIÇÃO( SITUACAOORIGINAL=0 )
        $query  = "UPDATE contasapagar SET idempresa=$idempresa, idfornecedor=$fornecedor,  idcolaborador=$colaborador, codigoeventofinanceiro='$codigoeventofinanceiro', eventofinanceiro='$eventofinanceiro', documento='$documento', idcontasbanco=$idcontasbanco, datavencimento='$datavencimento', descritivo='$descritivo', autorizado_por_id=$autorizado_por_id, data_competencia='$data_competencia', valor=$valor WHERE id=$idlancamento";
        if (!$result = mysql_query($query)) {
            $erro++;
        }

        if ($situacao != $situacaooriginal) {
            echo "sitdiff";
            $query  = "UPDATE contasapagar SET documento='$documento', situacao=$situacao, idformaspagamentos=$idformaspagamentos, datapagamento='$datapagamento', valorpago=$valorpago, desconto=$desconto,  multa=$multa, juros=$juros, numeroForma='$numeroForma', datavalidadeForma='$datavalidadeForma', bancoForma='$bancoForma', agenciaForma='$agenciaForma', contaForma='$contaForma', outroForma='$outroForma', autorizado_por_id=$autorizado_por_id WHERE id=$idlancamento";
            if (!$result = mysql_query($query)) {
                $erro++;
            }
        }
    } else {
        // SE O LANÇAMENTO ESTAVA PAGO AINDA ANTES DA EDIÇÃO( SITUACAOORIGINAL=1 )
        $query  = "INSERT INTO contasapagar (idusuario, idempresa, idfornecedor,  idcolaborador, idcontasbanco, idformaspagamentos, documento, parcelas, repeticoes, totalparcelas, datavencimento, descritivo, valor ) (SELECT idusuario, idempresa, idunidade, idfornecedor,  idcolaborador, idcontasbanco, idformaspagamentos, documento, parcelas, repeticoes, totalparcelas, datavencimento, descritivo, valor FROM contasapagar WHERE id=$idlancamento )";
        if (!$result = mysql_query($query)) {
            $erro++;
        }
        $novoid = mysql_insert_id();

        $query  = "UPDATE contasapagar SET situacao=2, datacancelado='$hoje' WHERE id=$idlancamento";
        if (!$result = mysql_query($query)) {
            $erro++;
        }
    }
    if ($erro == 0) {
        echo "blue|Lançamento efetuado com sucesso.|$idlancamento";
        $msg = "Lançamento:" . $fornecedor . " efetuado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro($erro) ao efetuar o lançamento." . $query;
        $msg = "Erro($erro) ao efetuar o lançamento.";
        $status = 1;
    }
    $parametroscsv = $idlancamento . ',' . $idusuario . ',' . $idempresa . ',' . $idcontasbanco . ',' . $datavencimento . ',' . $valor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "pagar") {
    $autorizado_por_id = ($autorizado_por_id == -1) ? 'null' : $autorizado_por_id;

    $query  = "UPDATE contasapagar SET situacao=$situacao, idformaspagamentos=$PAGidformaspagamentos, datapagamento='$datapagamento', valorpago=$PAGvalorpago, desconto=$PAGdesconto,  multa=$PAGmulta, juros=$PAGjuros, numeroForma='$PAGnumeroForma', datavalidadeForma='$PAGdatavalidadeForma', bancoForma='$PAGbancoForma', agenciaForma='$PAGagenciaForma', contaForma='$PAGcontaForma', outroForma='$PAGoutroForma', autorizado_por_id=$autorizado_por_id WHERE id=" . $idlancamento;
    if (!($result = mysql_query($query))) {
        $erro++;
    }

    $number = ($PAGvalor - $PAGdesconto + $PAGmulta + $PAGjuros);
    if ($PAGvalorpago < round($number, 2)) {
        $query  = "INSERT INTO contasapagar (idusuario, idempresa, idfornecedor, idcontasbanco, idformaspagamentos, documento, parcelas, totalparcelas, datavencimento, valor, situacao, datapagamento, valorpago, desconto, multa, juros) (SELECT idusuario, idempresa, idfornecedor, idcontasbanco, idformaspagamentos, documento, parcelas, totalparcelas, datavencimento, valor, situacao, datapagamento, valorpago, desconto, multa, juros FROM contasapagar WHERE id=" . $idlancamento . " )";
        $result = mysql_query($query);
        $novoid = mysql_insert_id();

        $query  = "UPDATE contasapagar SET valorpago=0, situacao=0, multa=0, desconto=0, juros=0, valor=" . ($PAGvalor - $PAGvalorpago) . ", parcelas=CONCAT ( LEFT(parcelas, LENGTH(parcelas)-1 ), CHAR ( ASCII ( RIGHT(parcelas,1)  )+1 )  ) WHERE id=" . $novoid;
        if (!($result = mysql_query($query))) {
            $erro++;
        }
    }

    if ($erro == 0) {
        echo "blue|Pagamento efetuado com sucesso.";
        $msg = "Pagamento do fornecedor " . $fornecedor . " efetuado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro($erro) ao efetuar o pagamento." . $query;
        $msg = "Erro($erro) ao efetuar o pagamento.";
        $status = 1;
    }
    $parametroscsv = $idlancamento . ',' . $situacao . ',' . $PAGidformaspagamentos . ',' . $datapagamento . ',' . $PAGvalorpago . ',' . $PAGdesconto . ',' . $PAGmulta . ',' . $PAGjuros . ',' . $PAGnumeroForma . ',' . $PAGdatavalidadeForma . ',' . $PAGbancoForma . ',' . $PAGagenciaForma . ',' . $PAGcontaForma . ',' . $PAGoutroForma;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cancelar") {
    $query  = "UPDATE contasapagar SET situacao=2, datacancelado='$hoje' WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Lançamento cancelado com sucesso.";
        $msg = "Lançamento do " . $fornecedor . " foi cancelado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao cancelar lançamento.";
        $msg = "Erro ao cancelar lançamento.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagar") {
    $query  = "DELETE FROM contasapagar WHERE id=$id";
    $queryContasAPagar = "SELECT idfornecedor AS id FROM contasapagar WHERE id=$id";
    $resultadoIdFornecedor = mysql_query($queryContasAPagar);
    while ($idFornecedorApagado = mysql_fetch_array($resultadoIdFornecedor)) {
        $idParaProcurarFornecedor = $idFornecedorApagado['id'];
    }
    $queryFornecedorDeletado = "SELECT fornecedor,cnpj FROM fornecedores WHERE id= $idParaProcurarFornecedor";
    $resultadoFornecedorDeletado = mysql_query($queryFornecedorDeletado);
    while ($linhaFornDeletado = mysql_fetch_array($resultadoFornecedorDeletado)) {
        $fornecedorDeletado = $linhaFornDeletado['fornecedor'];
        $cnpjDeletado = $linhaFornDeletado['cnpj'];
    }
    $msg = "Conta a Pagar com o fornecedor: " . $fornecedorDeletado . " / CNPJ :" . $cnpjDeletado . " deletado com sucesso.";
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
    $query = "INSERT INTO contasapagar (idusuario, idempresa, idfornecedor, idcolaborador, idcontasbanco, idformaspagamentos, codigoeventofinanceiro, eventofinanceiro, documento, parcelas, repeticoes, totalparcelas, datavencimento, valor, situacao, datareaberto, datacancelado, datapagamento, valorpago, desconto, multa, juros, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma, descritivo) (SELECT idusuario, idempresa, idfornecedor, idcolaborador, idcontasbanco, idformaspagamentos, codigoeventofinanceiro, eventofinanceiro, documento, parcelas, repeticoes, totalparcelas, datavencimento, valor, situacao, datareaberto, datacancelado, datapagamento, valorpago, desconto, multa, juros, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma, descritivo FROM contasapagar WHERE id=$id )";

    // print '*1*'.$query.'*1*';

    if ($result = mysql_query($query)) {
        $idnovo = mysql_insert_id();
        $query  = "UPDATE contasapagar SET situacao=3, datareaberto='$hoje' WHERE id=$id";
        // print '*2*'.$query.'**';
        $queryDaContaReaberta = "SELECT codigoeventofinanceiro,eventofinanceiro,documento WHERE id=$id";
        $resultadoContaReaberta = mysql_query($queryDaContaReaberta);
        while ($contaReaberta = mysql_fetch_array($resultadoContaReaberta)) {
            $codigoEventoReaberto = $contaReaberta['codigoeventofinanceiro'];
            $eventoReaberto = $contaReaberta['eventofinanceiro'];
            $documentoReaberto = $contaReaberta['documento'];
        }
        if ($result = mysql_query($query)) {
            $query  = "UPDATE contasapagar SET situacao=0, datareaberto='0000-00-00', datacancelado='0000-00-00', datapagamento='0000-00-00', valorpago=0, desconto=0,multa=0, juros=0 WHERE id=$idnovo";
            // print '*3*'.$query.'**';
            if ($result = mysql_query($query)) {
                echo "blue|Lançamento reaberto com sucesso.";
                $msg = "Lançamento reaberto: Código do Evento: " . $codigoEventoReaberto . " Evento Financeiro: " . $eventoReaberto . " Documento Reaberto" . $documentoReaberto . " reaberto com sucesso.";
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
