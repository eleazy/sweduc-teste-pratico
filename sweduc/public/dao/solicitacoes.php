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
    if ($id == "0") {
        $query  = "INSERT INTO solicitacoes(idunidade, solicitacao, prazo, emails)  VALUES ($idunidade, '$solicitacao', $prazo, '$emails') ;";
        if ($result = mysql_query($query)) {
            echo "blue|Solicitação cadastrada com sucesso.";
            $msg = "Solicitação $solicitacao cadastrada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao cadastrar nova solicitação.";
            $msg = "Erro ao cadastrar nova solicitação: $solicitacao.";
            $status = 1;
        }
        $parametroscsv = $idanoletivo . ',' . $numordem . ',' . $disciplina . ',' . $abreviacao . ',' . $basenacional;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else { //UPDATE
        $query  = "UPDATE solicitacoes SET idunidade='$idunidade', solicitacao='$solicitacao', prazo='$prazo', emails='$emails' WHERE id=" . $id;

        if ($result = mysql_query($query)) {
            echo "blue|Solicitação atualizada com sucesso.";
            $msg = "$emails $solicitacao atualizada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ($erro) ao atualizar solicitação.";
            $msg = "Erro ($erro) ao atualizar solicitação.";
            $status = 1;
        }
        $parametroscsv = $id . ',' . $numordem . ',' . $disciplina . ',' . $abreviacao . ',' . $basenacional;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "apaga") {
    $query  = "DELETE FROM solicitacoes WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Solicitação removida com sucesso.";
        $msg = "Solicitação removida com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao remover solicitação.";
        $msg = "Erro ao remover solicitação.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeSolicitacoes") {
    $query  = "SELECT id, solicitacao FROM solicitacoes WHERE idunidade=$idunidade ORDER BY solicitacao";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "<option value='" . $row['id'] . "'>" . $row['solicitacao'] . "</option>";
    }
}
