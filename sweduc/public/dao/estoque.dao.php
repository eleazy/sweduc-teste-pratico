<?php

include('../headers.php');

include('conectar.php');
include_once('logs.php');
include '../function/estoque.func.php';
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}




//function balancoAltera($quant, $valorCompra = 0, $valorVenda = 0, $unidade, $id) {

if ($action == 'atualizaPorLinha') {
    $valorCompra = str_replace('.', '', $valorCompra);
    $valorCompra = str_replace(',', '.', $valorCompra);
    $valorVenda = str_replace('.', '', $valorVenda);
    $valorVenda = str_replace(',', '.', $valorVenda);
    if (balancoAltera($_POST['quantidade'], $valorCompra, $valorVenda, $unidade, $id_estoque) > 0) {
        echo $msg = "blue|Estoque cadastrado com sucesso.";
    } else {
        echo $msg = "red|Erro ao cadastrar estoque.";
    }
}


if ($action == "atulalizar") {
    foreach ($novo as $key => $valores) {
        if ($valores['quant'] != '' || $valores['valorcompra'] != '' || $valores['valorvenda'] != '') {
            if (balancoAltera($valores['quant'], $valores['valorcompra'], $valores['valorvenda'], $unidade, $key) > 0) {
                echo $msg = "blue|Estoque cadastrado com sucesso.";
            } else {
                echo $msg = "red|Erro ao cadastrar estoque.";
            }
        }
    }
}
if ($action == "duplicado") {
    $msgok = "blue|";
    $msgErro = "red|";
    foreach ($estoqueDuplciar as $item) {
        $sql = "SELECT * FROM estoque where id = " . $item;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $query = "INSERT INTO estoque
                        (idunidadescontagem,
                        idunidade,
                        idfuncionario,
                        idgrupo,
                        datacadastro,
                        codigo,
                        descricao,
                        estoqueseguranca,
                        consumodiario,
                        loteecocompra,
                        valorcompra,
                        valorvenda)
                        VALUES
                        (" . $row['idunidadescontagem'] . ",
                        " . $row['idunidade'] . ",
                        " . $row['idfuncionario'] . ",
                        " . $row['idgrupo'] . ",
                        " . $row['datacadastro'] . ",
                        99" . $row['codigo'] . ",
                        '" . $row['descricao'] . " - Novo Modelo',
                        " . $row['estoqueseguranca'] . ",
                        " . $row['consumodiario'] . ",
                        " . $row['loteecocompra'] . ",
                        " . $row['valorcompra'] . ",
                        " . $row['valorvenda'] . ")";

            if ($result = mysql_query($query)) {
                $msgok .= $row['descricao'] . " - Novo Modelo, ";
                $status = 0;
            } else {
                $msgErro .= $row['descricao'] . " - Novo Modelo, ";
                $status = 1;
            }
        }
    }


    echo $msgok . '@' . $msgErro;
}
