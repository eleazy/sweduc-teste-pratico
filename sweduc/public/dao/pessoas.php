<?php

include('../headers.php');
include('conectar.php');

if (isset($_POST['action'])) {
    $response = match ($_POST['action']) {
        'pessoa_por_id' => isset($_POST['id']) ? pessoaPorId($_POST['id']) : 'Faltando parametro id',
        default => json_decode('Não foi possível encontrar a ação'),
    };
    echo $response;
    exit;
}

function pessoaPorId($id)
{
    $pessoa = mysql_query("SELECT * FROM pessoas WHERE pessoas.id = $id");
    return json_encode(mysql_fetch_array($pessoa, MYSQL_ASSOC), JSON_THROW_ON_ERROR);
}
