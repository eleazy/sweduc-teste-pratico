<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "atualizaTipoFaltas") {
    $query  = "UPDATE configuracoes SET tipodefaltas=$tipodefaltas";
    if ($result = mysql_query($query)) {
        ($tipodefaltas == 0) ? $msg = "Controle de faltas atualizado para: Falta por dia " : $msg = "Controle de faltas atualizado para: Falta por disciplina. " ;
        echo "blue|Controle de faltas atualizado.";
    } else {
        $msg = "Erro na atualização.";
        echo "red|Erro na atualização.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "atualizaRelFin") {
    $query  = "UPDATE configuracoes SET relfin='$relfinanceiro'";
    if ($result = mysql_query($query)) {
        ($relfinanceiro == 1) ? $msg = "Relatório Financeiro atualizado para: Analítico" : $msg = "Relatório Financeiro atualizado para: Sintético" ;
        echo "blue|Relatório Financeiro atualizado.";//.$query."][".mysql_error();
    } else {
        $msg = "Erro na atualização.";
        echo "red|Erro na atualização.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "atualizaNotasDecimais") {
    $query  = "UPDATE configuracoes SET notasdecimais='$notasdecimais', casasdecimaisnotas='$casasdecimaisnotas'";
    if ($result = mysql_query($query)) {
        $msg = "Número de casas decimais da nota do boletim atualizado para: " . $notasdecimais;
        echo "blue|Número de casas decimais atualizado.";//.$query."][".mysql_error();
    } else {
        $msg = "Erro na atualização.";
        echo "red|Erro na atualização.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "msgrecibo") {
    $query  = "UPDATE configuracoes SET msgrecibo='$msgrecibo'";
    if ($result = mysql_query($query)) {
        $msg = "Mensagem atualizada com sucesso : " . $msgrecibo . ".";
        echo "blue|Mensagem atualizada.";
    } else {
        $msg = "Erro na atualização.";
        echo "red|Erro na atualização.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == 'cadastraCorLogin') {
    $query = "UPDATE configuracoes SET cor_identidade_visual = '$cor'";
    if ($result = mysql_query($query)) {
        echo '1';
    } else {
        echo '0';
    }
} elseif($action == 'cadastrarFeriado')
{
    $SQL = "INSERT INTO feriados(feriados,data_do_feriado) VALUES ('$feriado','$Datadoferiado');";
    if($resultado = mysql_query($SQL))
    {
        $msg = "Feriado cadastrado com sucesso : " . $feriado . ".";
        $SQLPegaData = "SELECT id, DATE_FORMAT(data_do_feriado,'%d/%m') as data FROM feriados WHERE id =". mysql_insert_id();
        $resdata = mysql_query($SQLPegaData);
        while($linha = mysql_fetch_array($resdata))
        {
            $data = $linha['data'];
            $id = $linha['id'];
        }
        echo $id."|"."blue|Feriado Cadastrado.|$feriado|$data";
    }else {
        $msg = "Erro no Cadastro.";
        echo "red|Erro no Cadastro.";
    }

}elseif( $action == 'atualizaFeriado')
{
    $query  = "UPDATE feriados SET feriados ='$novoFeriado' , data_do_feriado = '$novaData' WHERE id = '$id'";

    if ($result = mysql_query($query)) {
        $msg = "Feriado cadastrado com sucesso : " . $feriado . ".";
        $SQLPegaData = "SELECT DATE_FORMAT(data_do_feriado,'%d/%m') as data FROM feriados WHERE id =". $id;
        $resdata = mysql_query($SQLPegaData);
        while($linha = mysql_fetch_array($resdata))
        {
            $data = $linha['data'];
        }
        $msg = "Feriado atualizado com sucesso";
        echo "blue|Feriado atualizado|".$data."";
    } else {
        $msg = "Erro na atualização.";
        echo "red|Erro na atualização.";
    }
}elseif($action == 'apagaFeriado')
{
    $query  = "DELETE FROM feriados WHERE id = '$id'";
    if($result = mysql_query($query))
    {
        echo "blue|Feriado deletado com sucesso";
    }else {
        echo "red|Erro ao deletar.";
    }
}
