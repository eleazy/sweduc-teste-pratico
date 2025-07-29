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

if ($action == "cadastraSala") {
    $query  = "SELECT unidade FROM unidades WHERE id=$idunidade";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $unidade = $row['unidade'];

    $query  = "SELECT COUNT(*) as cnt FROM salas WHERE sala='$sala' AND idunidade=$idunidade";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $cnt = $row['cnt'];

    if ($cnt == 0) {
        $query  = "INSERT INTO salas (sala, idunidade) VALUES ('$sala', $idunidade);";
        $result = mysql_query($query);
        echo mysql_insert_id() . "|" . $unidade . "|Sala $sala cadastrada com sucesso.";
        $msg = "Sala $sala cadastrada com sucesso.";
        $status = 0;
    } else {
        echo htmlentities("0|$unidade|Sala $sala já existe!");
        $msg = "Sala $sala já existe!";
        $status = 1;
    }
    $parametroscsv = $cnt . ',' . $id . ',' . $idunidade . ',' . $unidade . ',' . $sala;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateSala") {
    $query  = "UPDATE salas SET sala='$sala' WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Sala atualizada com sucesso.";
        $msg = "Sala: " . $sala . " atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar a sala!";
        $msg = "Erro ao atualizar a sala!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $sala;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaSala") {
    $query  = "DELETE FROM salas WHERE id=$id";
    $querySalaParaSerDeletada = "SELECT sala FROM salas WHERE id=" . $id;
    $resultadoQuery = mysql_query($querySalaParaSerDeletada);
    while ($linhaSala = mysql_fetch_array($resultadoQuery)) {
        $sala = $linhaSala['sala'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Sala removida com sucesso.";
        $msg = "Sala " . $sala . " foi removida com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao remover a sala!";
        $msg = "Erro ao remover a sala!";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
