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

if ($action == "cadastra") {
    $query  = "SELECT COUNT(*) as cnt FROM financeiro_tabeladescontos WHERE descricao='$descricao' AND percentual=$percentual";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $cnt = $row['cnt'];
    $id = 0;
    if ($cnt == 0) {
        $percentual = str_replace(',', '.', $percentual);
        $query  = "INSERT INTO financeiro_tabeladescontos (descricao, percentual) VALUES ('$descricao', $percentual);";
        $result = mysql_query($query);
        $id = mysql_insert_id();
        echo $id . "|Desconto cadastrado com sucesso.|" . $descricao . "|" . $percentual;
        $msg = 'Desconto: ' . $descricao . ' cadastrado com sucesso';
        $status = 0;
    } else {
        echo "0| |Desconto já existe!|" . $descricao . "|" . $percentual;
        $msg = 'Desconto já existe!';
        $status = 1;
    }
    $parametroscsv = $id . ',' . $nomeEvento . ',' . $planoparcelamento . ',' . $parcelas;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "DELETE FROM financeiro_tabeladescontos WHERE id=$id";
    $queryPegaDesconto = "SELECT descricao FROM financeiro_tabeladescontos WHERE id=$id";
    $resultadoQuery = mysql_query($queryPegaDesconto);
    while ($linhaDesc = mysql_fetch_array($resultadoQuery)) {
        $descricaoDeletada = $linhaDesc['descricao'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Desconto removido com sucesso.";
        $msg = 'Desconto: ' . $descricaoDeletada . ' removido com sucesso';
        $status = 0;
    } else {
        echo "red|Erro ao remover o Desconto.";
        $msg = 'Erro ao remover o Desconto.';
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
