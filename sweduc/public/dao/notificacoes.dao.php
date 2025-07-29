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


if ($action == "cadastra") {
    if (cadastroNotificacoes($id_notificacoes, $id_pessoas, $email) > 0) {
        echo $msg = "Avaliação cadastrada com sucesso.";
    } else {
        echo $msg = "Erro ao cadastrar avaliação.";
    }
}


if ($action == "motivo") {
    $query = 'INSERT INTO motivo (motivo,aplicacao) VALUES ("' . $motivo . '", "' . $aplicacao . '");';

    if ($result = mysql_query($query)) {
        $status = mysql_insert_id();
    } else {
        $status = -1;
    }


    if ($status > 0) {
        echo $msg = "Motivos cadastrada com sucesso.";
    } else {
        echo $msg = "Erro ao cadastrar motivos.";
    }
} elseif ($action == "motivoDel") {
    $query = 'DELETE FROM  motivo where id=' . $id;


    if ($result = mysql_query($query)) {
        $status = 1;
    } else {
        $status = -1;
    }
    if ($status > 0) {
        echo $msg = "Motivos deletado com sucesso.";
    } else {
        echo $msg = "Erro ao deletado motivos.";
    }
}
