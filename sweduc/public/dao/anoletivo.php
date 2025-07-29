<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$hoje = date("Y-m-d");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id, idunidade FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];
$idunidadefuncionario = $row1['idunidade'];

$valorvenda = str_replace(",", ".", str_replace(".", "", $valorvenda));

if ($action == "cadastra") {
    $query  = "INSERT INTO anoletivo(anoletivo) VALUES  ('$anoletivo');";
    if ($result = mysql_query($query)) {
        echo "blue|Ano Letivo ".$anoletivo." cadastrado.|" . mysql_insert_id();
        $msg = "Ano Letivo ".$anoletivo." cadastrado.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar ano letivo.|0";
        $msg = "Erro ao cadastrar ano letivo $anoletivo.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $anoletivo;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
/*
    $query  = "DELETE FROM anoletivo WHERE id IN ($id)";
    if($result = mysql_query($query)) {
        $query  = "UPDATE estoque_gr_mat SET idgrupo=0 WHERE idgrupo IN ($id)";
      $result = mysql_query($query);
      echo "blue|Grupo removido.";
      $msg="Grupo removido.";
      $status=0;
    } else {
      echo "red|Erro ao remover grupo.";
      $msg="Erro ao remover grupo.";
      $status=1;
    }
    $parametroscsv=$id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
*/
} elseif ($action == "anoletivoDeProspeccaoUnidade") {
    $query_result = mysql_query("SELECT u.anoletivo_prospeccao as anoletivo FROM anoletivo a JOIN unidades u ON anoletivo_prospeccao=anoletivo and u.id='" . $_REQUEST['id_unidade'] . "' ORDER BY anoletivo DESC");
    echo json_encode(mysql_fetch_array($query_result, MYSQL_ASSOC), JSON_THROW_ON_ERROR);
} elseif ($action == "atualizaAnoletivoMatricula") {
    $query  = "UPDATE unidades SET anoletivo_matricula='$anoletivo_matricula' WHERE id=1";
    $result = mysql_query($query);
    $msg = ($result) ? "Ano letivo ".$anoletivo." configurado como padrão para matrícula." : "Erro ao cadastrar ano letivo $anoletivo como padrão para matrícula.";
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, '', $msg);

    echo ($result) ? "blue|Ano Letivo da matrícula ".$anoletivo." cadastrado.|" . mysql_insert_id() : "red|Erro ao cadastrar ano letivo da matrícula.|0";
}
