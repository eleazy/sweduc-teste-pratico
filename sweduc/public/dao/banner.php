<?php

include('../headers.php');
include('connsys.inc.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
    ${$k} = $_POST[$k];
}

if ($action == "clicou") {
    $query1  = "INSERT INTO banner_dados(idbanner_clientes, datahora, escola, clique) VALUES ($id, '$agora', '$escola', 1)";
//echo $query1."<br />";
    $result1 = mysql_query($query1);
}
