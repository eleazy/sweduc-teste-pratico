<?php
function salvaLog($idfuncionario, $arquivo, $acao, $status, $parametroscsv, $descricao) {
  if ($idfuncionario>0) {
    $datahora = date("Y-m-d H:i:s");
    $parametroscsv=addslashes($parametroscsv);
    $descricao=addslashes($descricao);

    $query = "INSERT INTO logs (idfuncionario, datahora, acao, parametroscsv, descricao) VALUES ('$idfuncionario', '$datahora', '$arquivo@$acao', '$parametroscsv', '$descricao');";
    $result = mysql_query($query);
    return mysql_insert_id();
  }
}
?>