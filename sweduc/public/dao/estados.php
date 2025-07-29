<?php

include('../headers.php');
include('conectar.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

if ($action == "recebeCidades") {
    $query  = "SELECT * FROM cidades WHERE cod_estado=$idestado ORDER BY cod_estado ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['nom_cidade'] . '</option>';
    }
} elseif ($action == "recebeCidades@") {
    $query  = "SELECT * FROM cidades WHERE cod_estado=$idestado ORDER BY cod_estado ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '@' . $row['cod_municipio'] . '">' . $row['nom_cidade'] . '</option>';
    }
} elseif ($action == "recebeCidades@RJ") {
    $query  = "SELECT * FROM cidades WHERE cod_estado=$idestado ORDER BY cod_estado ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '@' . $row['cod_municipio'] . '" ';
        if (($row['id'] == '3631') && ($idestado == "19")) {
            echo " selected ";
        }
        echo '>' . $row['nom_cidade'] . '</option>';
    }
}
