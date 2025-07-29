<?php

include_once('conectar.php');

function salvaLog($idfuncionario, $arquivo, $acao, $status, $parametroscsv, $descricao)
{

    $idfuncionario = ($idfuncionario > 0) ? $idfuncionario : '999999';


  // if ($idfuncionario>0) {
    $datahora = date("Y-m-d H:i:s");
    $parametroscsv = addslashes($parametroscsv);
    $descricao = addslashes($descricao);

    $query = "INSERT INTO logs (idfuncionario, datahora, acao, parametroscsv, descricao, status)
        VALUES ('$idfuncionario', '$datahora', '$arquivo@$acao', '$parametroscsv', '$descricao', '$status');";
    $result = mysql_query($query);
    return mysql_insert_id();
  // }
}
