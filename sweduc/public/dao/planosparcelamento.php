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
    $query  = "SELECT COUNT(*) as cnt FROM planosparcelamento WHERE planoparcelamento='$planoparcelamento' AND parcelas=$parcelas";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];
    $id = 0;
    if ($cnt == 0) {
        $query  = "INSERT INTO planosparcelamento (planoparcelamento, parcelas) VALUES ('$planoparcelamento', $parcelas);";
        $result = mysql_query($query);
        $id = mysql_insert_id();
        echo $id . "|Plano de pagamento " . $nomeEvento . " cadastrado com sucesso.|" . $planoparcelamento . "|" . $parcelas;
        $msg = 'Plano de pagamento ' . $planoparcelamento . ' cadastrado com sucesso';
        $status = 0;
    } else {
        echo "0| |Plano de pagamento $nomeEvento já existe!|" . $planoparcelamento . "|" . $parcelas;
        $msg = 'Plano de pagamento $nomeEvento já existe!';
        $status = 1;
    }
    $parametroscsv = $id . ',' . $nomeEvento . ',' . $planoparcelamento . ',' . $parcelas;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "DELETE FROM planosparcelamento WHERE id=$id";
    $queryParaPegarPlano = "SELECT planoparcelamento AS plano FROM planosparcelamento WHERE id=$id";
    $resultadoPlano = mysql_query($queryParaPegarPlano);
    while ($linhaPlano = mysql_fetch_array($resultadoPlano)) {
        $plano = $linhaPlano['plano'];
    }
    $msg = 'Plano de Pagamento: ' . $plano . ' removido com sucesso';
    if ($result = mysql_query($query)) {
        echo "blue|Plano de Pagamento removido com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao remover o Plano de Pagamento.";
        $msg = 'Erro ao remover o Plano de Pagamento.';
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebePlanos") {
    $msg = '<option value="-1">Escolha um plano ou preencha os dados abaixo...</option>';
    $query1 = "SELECT *, planosparcelamento.id as pid, planosparcelamento.parcelas as pparcelas FROM planosparcelamento, serie_plano, series WHERE serie_plano.idplanosparcelamento=planosparcelamento.id AND serie_plano.idserie=series.id AND series.id=" . $idserie . " ORDER BY parcelas ASC";
    $result1 = mysql_query($query1);
    $tem = 0;
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        $tem = 1;
        $msg .= '<option value="' . $row1['pparcelas'] . '">' . $row1['planoparcelamento'] . '</option>';
    }
    if ($tem == 0) {
        $msg = "<option>Esta Série não tem planos de pagamento.</option>";
    }
    if ($idserie == 0) {
        $msg = "<option>SELECIONE PRIMEIRO A SÉRIE DO ALUNO.</option>";
    }
    echo $msg;
}
