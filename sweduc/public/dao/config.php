<?php

include(__DIR__ . '/../headers.php');
include(__DIR__ . '/conectar.php');
require(__DIR__ . '/../function/config.php');
require(__DIR__ . '/../function/validar.php');
require_once(__DIR__ . '/../auth/injetaCredenciais.php');

$acoes = [
    'salvaConfig'
];

_validar([
    'Falta de parametros: [action]' => !$_REQUEST['action'],
    "Ação não identificada: {$_REQUEST['action']}" => !in_array($_REQUEST['action'], $acoes) || !function_exists($_REQUEST['action'])
], 400);

if (function_exists($_REQUEST['action'])) {
    call_user_func($_REQUEST['action']);
}

function salvaConfig()
{
    _validar([
        'Precisa do parametro \'chave\'' => !isset($_REQUEST['chave']),
        'Precisa do parametro \'valor\'' => !isset($_REQUEST['valor']),
    ], 400);

    $chave = $_REQUEST['chave'];
    $valor = $_REQUEST['valor'];

    setConfig($chave, $valor);
    echo 'Configuração salva!';
}
