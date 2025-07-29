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

if ($action == "cadastraTurno") {
    $query  = "SELECT COUNT(*) as cnt FROM turnos WHERE turno='$nomeTurno'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];
    if ($cnt == 0) {
        $query  = "INSERT INTO turnos (turno) VALUES ('$nomeTurno');";
        $result = mysql_query($query);
        echo mysql_insert_id() . "|" . $turno . "|Turno $turno cadastrado com sucesso.";
        $msg = "Turno $nomeTurno cadastrado com sucesso.";
        $status = 0;
    } else {
        echo "0|$nomeTurno|Turno $nomeTurno já existe!";
        $msg = "Turno $nomeTurno já existe!";
        $status = 1;
    }
    $parametroscsv = $cnt . ',' . $idanoletivo . ',' . $nomeTurno;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateTurno") {
    $query  = "UPDATE turnos SET turno='$novovalor' WHERE id=$id";
    $pegarTurnoParaSerAtualizado = "SELECT turno FROM turnos WHERE id=$id";
    $resultado1 = mysql_query($pegarTurnoParaSerAtualizado);
    while ($linhaTurnoSerAtualizado = mysql_fetch_array($resultado1)) {
        $turnoParaSerAtualizado = $linhaTurnoSerAtualizado['turno'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Turno atualizado com sucesso.";
        $msg = "Turno " . $turnoParaSerAtualizado . " atualizado com sucesso para " . $novovalor . ".";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar turno.";
        $msg = "Erro ao atualizar turno.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaTurno") {
    $query = "DELETE FROM turnos WHERE id=$id";
    $pegarTurno = "SELECT turno FROM turnos WHERE id=$id";
    $resultado = mysql_query($pegarTurno);
    while ($linhaTurno = mysql_fetch_array($resultado)) {
        $turnoDeletado = $linhaTurno['turno'];
    }
    $msg = "Turno " . $turnoDeletado . " removido com sucesso.";
    if ($result = mysql_query($query)) {
        echo "blue|Turno removido com sucesso.";

        $status = 0;
    } else {
        echo "red|Erro ao remover turno.";
        $msg = "Erro ao remover turno.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
