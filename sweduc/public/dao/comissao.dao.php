<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
include '../function/notificacoes.func.php';
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

if ($action == "comissao") {
    if (isset($id)) {
    } else {
        $query = 'INSERT INTO comissao (tipo, totalgeral) VALUES ("' . $comissao . '", ' . $quanttotal . ');';

        if ($result = mysql_query($query)) {
            $msg = "blue|Comissão cadastrada com sucesso.";
        } else {
            $status = -1;
            $msg = "red|Erro ao cadastrada comissão.";
        }
    }
    echo $msg;
} elseif ($action == "comissaoDel") {
    $query = 'DELETE FROM  comissao where id=' . $id;


    if ($result = mysql_query($query)) {
        $status = 1;
    } else {
        $status = -1;
    }
    if ($status > 0) {
        $msg = "blue|Comissão deletado com sucesso.";
    } else {
        $msg = "red|Erro ao deletado comissão.";
    }
    echo $msg;
} elseif ($action == "comissaoRegrasDel") {
    $query = 'DELETE FROM  comissao_regras where id=' . $id;


    if ($result = mysql_query($query)) {
        $status = 1;
    } else {
        $status = -1;
    }
    if ($status > 0) {
        $msg = "blue|Comissão deletado com sucesso.";
    } else {
        $msg = "red|Erro ao deletado comissão.";
    }
    echo $msg;
} elseif ($action == "regras") {
    $valorfixo = str_replace(',', '.', str_replace('.', '', $valorfixo));
    if (isset($id)) {
    } else {
        $query = 'INSERT INTO comissao_regras (idcomissao,quantidade_min,porcentagem,valor_fixo)
                  VALUES ("' . $idcomissao . '", "' . $quantidade . '", "' . $valor . '", "' . $valorfixo . '");';

        if ($result = mysql_query($query)) {
            $msg = "blue|Comissão cadastrada com sucesso.";
        } else {
            $status = -1;
            $msg = "red|Erro ao cadastrada comissão.";
        }
    }
    echo $msg;
}
