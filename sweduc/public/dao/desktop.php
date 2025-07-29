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

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $dataevento = explode("/", $dataevento);
    $dataevento = $dataevento[2] . "-" . $dataevento[1] . "-" . $dataevento[0];
    if ($idevento > 0) {
        $query = "UPDATE eventos SET data='$dataevento', titulo='$titulo', descricao='$descricao' WHERE id=" . $idevento;
        if ($result = mysql_query($query)) {
            echo "blue|Evento atualizado.";
            $msg = "Evento atualizado.";
            $status = 0;
        } else {
            echo "red|Erro ao atualizar evento.";
            $msg = "Erro ao atualizar evento.";
            $status = 1;
        }
    } else {
        $query = "INSERT INTO eventos(idfuncionario, idpessoa, idserie, data, tipo, titulo, descricao, idturmas, idunidades) VALUES ('$idfuncionario', '$idpessoa','[$idserie]','$dataevento', '$tipo', '$titulo', '$descricao', '', '1,2,3,4,5,6,7,8,9')";// temporario, só para não quebrar essa funcionalidade
        if ($result = mysql_query($query)) {
            echo "blue|Evento cadastrado.";
            $msg = "Evento cadastrado.";
            $status = 0;
        } else {
            echo "red|Erro ao cadastrar evento.";
            $msg = "Erro ao cadastrar evento.";
            $status = 1;
        }
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query = "DELETE FROM eventos WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Evento removido com sucesso.";
        $msg = "Evento removido com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao remover evento.";
        $msg = "Erro ao remover evento.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
