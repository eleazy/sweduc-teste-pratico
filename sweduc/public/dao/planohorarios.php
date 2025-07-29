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

if ($action == "cadastra") {
    //$query  = "SELECT COUNT(*) as cnt FROM planohorarios WHERE entrada='$entrada' AND saida='$saida'";
    //$result = mysql_query($query);
    //$row = mysql_fetch_array($result, MYSQL_ASSOC);
    //$cnt= $row['cnt'];

    //if ($cnt==0) {
//} else {
    //   echo "0|Plano de horário com esses horários de entrada e saída já existe!";
    //$msg="Plano de horário com esses horários de entrada e saída já existe!";
    // $status=0;
//}
    if ($valor != '') {
        $valor = str_replace(",", ".", str_replace(".", "", $valor));
        $query = "INSERT INTO planohorarios (codigo, valor, entrada, saida) VALUES ('$codigo', '" . $valor . "', '$entrada','$saida');";
    } else {
        $query = "INSERT INTO planohorarios (codigo, entrada, saida) VALUES ('$codigo', '$entrada','$saida');";
    }

    if (mysql_query($query)) {
        echo mysql_insert_id() . "|Plano de horário $codigo cadastrado.";

        $msg = "Plano de horário $codigo cadastrado.";
        $status = 1;
    } else {
        echo "0|Erro : cadastra plano de horário ";
        $msg = "Erro : cadastra plano de horário !";
        $status = 0;
    }
    $parametroscsv = $idunidade . ',' . $curso;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "SELECT COUNT(*) as cnt FROM alunos_matriculas WHERE alunos_matriculas.idplanohorario=" . $id;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($row['cnt'] == 0) {
        $query  = "DELETE FROM planohorarios WHERE id=$id";
        if ($result = mysql_query($query)) {
            echo "blue|Plano de horários removido.";
            $msg = "Plano de horários removido.";
            $status = 0;
        } else {
            echo "red|Erro ao remover Plano de horários.";
            $msg = "Erro ao remover Plano de horários.";
            $status = 1;
        }
        $parametroscsv = $id;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else {
        echo "red|Erro ao remover Plano de horários. Existem matrículas vinculadas a este Plano de horários.";
        $msg = "Erro ao remover Plano de horários. Existem matrículas vinculadas a este Plano de horários.";
        $status = 1;
        $parametroscsv = $id . ',' . $row['cnt'];
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
}
