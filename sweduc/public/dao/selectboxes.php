<?php

include("conectar.php");
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

if ($acao == "lerCidades") {
    $query = "SELECT * FROM cidades WHERE cod_estado=" . $id . " ORDER BY cod_estado ASC, nom_cidade ASC";
    $result = mysql_query($query);

    $json_names = [];

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $json_names[] = '{"optionValue" : "' . $row['cod_cidade'] . '", "optionDisplay" : "' . utf8_encode($row['nom_cidade']) . '"}';
    }
    //$json_names[] = '{"optionValue":'.$row['cod_cidade'].', "optionDisplay": "'.utf8_encode($row['nom_cidade']).'"}';

    echo '[' . implode(',', $json_names) . ']';
} elseif ($acao == "lerCursos") {
    $query = "SELECT * FROM cursos WHERE idanoletivo=" . $id . " ORDER BY curso ASC";
    $result = mysql_query($query);

    $json_names = [];

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $json_names[] = '{"optionValue" : "' . $row['id'] . '", "optionDisplay" : "' . utf8_encode($row['curso']) . '"}';
    }
    //$json_names[] = '{"optionValue":'.$row['cod_cidade'].', "optionDisplay": "'.utf8_encode($row['nom_cidade']).'"}';

    echo '[' . implode(',', $json_names) . ']';
}
