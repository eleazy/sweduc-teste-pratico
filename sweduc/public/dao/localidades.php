<?php

include('../headers.php');
include('conectar.php');
include('../lib/QueryBuilder.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

if ($_REQUEST['tipo'] == 'estados') {
    estados($_REQUEST['cod_pais']);
} elseif ($_REQUEST['tipo'] == 'cidades') {
    cidades($_REQUEST['cod_estado']);
} else {
    echo "Não foi possível completar a requisição";
    http_response_code(400);
}

function estados($pais_id)
{
    $estados = QueryBuilder::on('estados')
        ->select(['id', 'nom_estado as nome', 'sgl_estado as sigla'])
        ->where("cod_pais='$pais_id'")
        ->get(null);

    echo json_encode($estados, JSON_THROW_ON_ERROR);
}

function cidades($estado_id)
{
    $cidades = QueryBuilder::on('cidades')
        ->select(['id', 'nom_cidade as nome'])
        ->where("cod_estado='$estado_id'")
        ->get(null);

    echo json_encode($cidades, JSON_THROW_ON_ERROR);
}
