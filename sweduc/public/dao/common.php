<?php

/**
 * Retorna uma mensagem em json e opcionalmente mais informações
 */
function response($msg, $debug_msg = null, $status_code = 200)
{
    $response_body = [];
    $response_body['msg'] = $msg;

    if ($_SESSION['permissao'] == 1) {
        $response_body['debug_msg'] = $debug_msg;
    }

    echo json_encode($response_body, JSON_THROW_ON_ERROR);
    http_response_code($status_code);
}
