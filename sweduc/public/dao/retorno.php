<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "apagar") {
    $query  = "DELETE FROM financeiro_retornos WHERE id=$id";
    if ($result = mysql_query($query)) {
        $query  = "DELETE FROM financeiro_retornos_titulos WHERE idfinanceiro_retornos=$id";
        if ($result = mysql_query($query)) {
            echo "blue|Arquivo removido com sucesso.";
            $msg = "Arquivo removido com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro 1 ao remover retorno.";
            $msg = "Erro 1 ao remover retorno.";
            $status = 1;
        }
        $parametroscsv = $id;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else {
        echo "red|Erro 2 ao remover retorno.";
        $msg = "Erro 2 ao remover retorno.";
        $status = 1;
        $parametroscsv = $id;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
}
