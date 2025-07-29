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

$dtinicial = explode("/", $datainicial);
$datainicial = $dtinicial[2] . "-" . $dtinicial[1] . "-" . $dtinicial[0];

if ($action == "cadastra") {
    $query  = "INSERT INTO fornecedores(fornecedor, cnpj, logradouro, numero, complemento, idpais, idestado, idcidade, bairro, contato, departamento, telefone1, telefone2, email1, email2, observacoes) VALUES ( '$fornecedor', '$cnpj', '$logradouro', '$numero', '$complemento', 1, $idestado, $idcidade, '$bairro', '$contato', '$departamento', '$telefone1', '$telefone2', '$email1', '$email2', '$observacoes' )";
    if ($result = mysql_query($query)) {
        echo htmlentities("blue|Novo fornecedor cadastrado com sucesso.");
        (empty($cnpj)) ? $msg = "Novo fornecedor ".$fornecedor." cadastrado com sucesso." : $msg = "Novo fornecedor ".$fornecedor." com o CNPJ: ".$cnpj." cadastrado com sucesso." ;
        $status = 0;
    } else {
        echo htmlentities("red|Erro ao cadastrar novo fornecedor!");
        $msg = "Erro ao cadastrar novo fornecedor!";
        $status = 1;
    }
    $parametroscsv = $fornecedor . ',' . $cnpj . ',' . $logradouro . ',' . $numero . ',' . $complemento . ',' . $idestado . ',' . $idcidade . ',' . $bairro . ',' . $contato . ',' . $departamento . ',' . $telefone1 . ',' . $telefone2 . ',' . $email1 . ',' . $email2 . ',' . $observacoes;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "edita") {
    $query  = "UPDATE fornecedores SET fornecedor='$fornecedor', cnpj='$cnpj', logradouro='$logradouro', numero='$numero', complemento='$complemento', idestado=$idestado, idcidade=$idcidade, bairro='$bairro', contato='$contato', departamento='$departamento', telefone1='$telefone1', telefone2='$telefone2', email1='$email1', email2='$email2', observacoes='$observacoes' WHERE id=$idfornecedor";

    if ($result = mysql_query($query)) {
        echo htmlentities("blue|Fornecedor atualizado com sucesso.");
        (empty($cnpj)) ? $msg = "Fornecedor ".$fornecedor." atualizado com sucesso." : $msg = "Fornecedor ".$fornecedor." com o CNPJ: ".$cnpj." atualizado com sucesso." ;
        $status = 0;
    } else {
        echo htmlentities("red|Erro ao atualizar fornecedor!");
        $msg = "Erro ao atualizar fornecedor!";
        $status = 1;
    }
    $parametroscsv = $idfornecedor . ',' . $fornecedor . ',' . $cnpj . ',' . $logradouro . ',' . $numero . ',' . $complemento . ',' . $idestado . ',' . $idcidade . ',' . $bairro . ',' . $contato . ',' . $departamento . ',' . $telefone1 . ',' . $telefone2 . ',' . $email1 . ',' . $email2 . ',' . $observacoes;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "DELETE FROM fornecedores WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Fornecedor removido com sucesso.";
        (empty($cnpj)) ? $msg = "Fornecedor ".$fornecedor." removido com sucesso." : $msg = "Fornecedor ".$fornecedor." com o CNPJ: ".$cnpj." removido com sucesso." ;
        $status = 0;
    } else {
        echo "red|Erro ao remover fornecedor.";
        $msg = "Erro ao remover fornecedor.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
