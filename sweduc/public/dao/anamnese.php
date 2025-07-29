<?php

use App\Academico\AnamneseService;

include('../headers.php');
include('conectar.php');
include_once('logs.php');

require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$keys = array_keys($_POST);
$values = array_values($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

unset($keys[0]); // retirando o índice 0 que corresponde a action
unset($values[0]); // retirando o índice 0 que corresponde a action

if ($action == 'cadastrar') {
    if (AnamneseService::salvar($idaluno, $_REQUEST)) {
        echo "success|Ficha cadastrada com sucesso";
    } else {
        echo "error|Ocorreu um erro ao tentar cadastrar a ficha. Tente novamente";
    }
} elseif ($action == 'atualizar') {
    if (AnamneseService::salvar($idaluno, $_REQUEST)) {
        echo "success|Ficha alterada com sucesso";
    } else {
        echo "error|Ocorreu um erro ao tentar alterar a ficha. Tente novamente";
    }
}
