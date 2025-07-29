<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastrar") {
    $dataate = ($dataate == '') ? $datade : $dataate;

    $dd = explode('/', $datade);
    $da = explode('/', $dataate);

    $nomearquivo = date('Ymd_His');

    $target_dir = "../clientes/" . $cliente . "/cardapio/";
    $imageFileType = pathinfo($_FILES["cardapio"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . "cardapio_" . $nomearquivo . '.' . $imageFileType;


    if (move_uploaded_file($_FILES["cardapio"]["tmp_name"], $target_file)) {
        $mensagem = "O arquivo " . basename($_FILES["cardapio"]["name"]) . " foi enviado.";
    } else {
        $mensagem = "Desculpe. Houve um erro ao enviar o arquivo.";
    }

    $query  = "INSERT INTO cardapio(idfuncionario, idunidade,datade, dataate, cardapio, idserie)
                    VALUES ($idfuncionario, $idunidade, '" . $dd[2] . "-" . $dd[1] . "-" . $dd[0] . "', '" . $da[2] . "-" . $da[1] . "-" . $da[0] . "', '" . substr($target_file, 3) . "', $idserie);";

    if ($result = mysql_query($query)) {
        echo "blue| cadastrado com sucesso. " . $mensagem;
        $msg = "Cadastrado com sucesso. " . $mensagem;
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar. " . $mensagem;
        $msg = "Erro ao cadastrar. " . $mensagem;
        $status = 1;
    }
} elseif ($action == "apagar") {
    $buscaCardapio = "SELECT id, cardapio FROM cardapio where id=$id";
    $resultBusca = mysql_query($buscaCardapio);
    $row = mysql_fetch_array($resultBusca, MYSQL_ASSOC);

    $filename = "../" . $row['cardapio'];

    $msgfile = '';

    if (file_exists($filename)) {
        unlink($filename);
        $msgfile .= ' Arquivo excluído.';
    }



    $query  = "DELETE FROM cardapio WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Cardápio removido com sucesso." . $msgfile;
        $msg = "Cardápio removido com sucesso." . $msgfile;
        $status = 0;
    } else {
        echo "red|Erro ao remover." . $msgfile;
        $msg = "Erro ao remover." . $msgfile;
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
