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

if ($action == "recebeDocumentos") {
    $query = "SELECT * FROM documentos WHERE idserie=" . $idserie . " OR  idserie=0 ORDER BY idserie ASC, documento ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['documento'] . '</option>';
    }
} elseif ($action == "cadastraDocumento") {
    $query  = "INSERT INTO documentos (idserie, documento) VALUES ($idserie, '$documento')";
    if ($result = mysql_query($query)) {
        echo "blue|Documento $documento cadastrado.";
        $msg = "Documento $documento cadastrado.";
        $status = 0;
    } else {
        echo htmlentities("red|Documento $documento já; existe!");
        $msg = "Documento $documento já; existe!";
        $status = 1;
    }
    $parametroscsv = $idserie . ',' . $documento;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaDocumento") {
    $query  = "DELETE FROM documentos WHERE id=$id";
    $queryDocumento = "SELECT documento FROM documentos WHERE id=" . $id;
    $resultadoDocumento = query_mysql($queryDocumento);
    while ($linhaDoc = mysql_fetch_array($resultadoDocumento)) {
        $Docnome = $linhaDoc['documento'];
    }
    $msg = "Documento: " . $Docnome . " removido.";
    if ($result = mysql_query($query)) {
        echo "blue|Documento removido.";

        $status = 0;
    } else {
        echo "red|Erro ao remover documento.";
        $msg = "Erro ao remover documento.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
