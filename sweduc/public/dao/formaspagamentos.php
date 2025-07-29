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
$permite_parcelamento = $_REQUEST['permite_parcelamento'] == 'true' ? '1' : '0';

$query1  = "SELECT id FROM funcionarios WHERE idpessoa = " . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    if ($id == "") {
        $query  = "INSERT INTO formaspagamentos (formapagamento,diascredito,taxaoperadora,tipo,permite_parcelamento) "
                . "VALUES ('$formapagamento', '$diascredito', '$taxaoperadora', '$tipo', $permite_parcelamento);";

        if ($result = mysql_query($query)) {
            $id = mysql_insert_id();
            echo $id . "|" . $formapagamento . "|Pagamento $formapagamento cadastrado.";
            $msg = "Forma de pagamento: " . $formapagamento . " cadastrado.";
            $status = 0;
        } else {
            $err = mysql_error();
            $suporte = $_SESSION['permissao'] == 1 && $_SESSION['login'] == "suporte";

            echo "$err | Forma de pagamento não cadastrado";
            echo "Forma de pagamento não cadastrado";
        }
    } else {
        if ($tipo == "dinheiro" || $tipo == "cheque") {
            unset($tipo);
        }

        $updateTipo = $tipo ? ", tipo='$tipo'" : "";
        $query  = "UPDATE formaspagamentos SET
                   formapagamento='$formapagamento',
                   taxaoperadora='$taxaoperadora',
                   diascredito='$diascredito',
                   permite_parcelamento=$permite_parcelamento
                   $updateTipo
                   WHERE id=$id";
        if ($result = mysql_query($query)) {
            echo "blue|Forma de pagamento atualizada com sucesso.";
            $msg = "Forma de pagamento: " . $formapagamento . " atualizada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro na atualização da forma de pagamento.";
            $msg = "Erro na atualização da forma de pagamento.";
            $status = 1;
        }
    }

    $parametroscsv = $id . ',' . $valor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "DELETE FROM formaspagamentos WHERE id=$id";
    $queryFormaDePagamento = "SELECT formapagamento AS formadepagamento FROM formaspagamentos WHERE id=$id";
    $resultadoQuery = mysql_query($queryFormaDePagamento);
    while ($linhaPag = mysql_fetch_array($resultadoQuery)) {
        $formadepagamento = ['formadepagamento'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Forma de pagamento removida com sucesso.";
        $msg = "Forma de pagamento: " . $formadepagamento . " removida com sucesso.";
        $status = 0;
    } else {
        echo htmlentities("red|Erro ao remover forma de pagamento.");
        $msg = "Erro ao remover forma de pagamento.";
        $status = 1;
    }

    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
