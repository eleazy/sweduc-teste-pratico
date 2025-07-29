<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastrar") {
    $datacircular = date('Y-m-d');

    $nomearquivo = date('Ymd_His');

    $target_dir = "../clientes/" . $cliente . "/circulares/";
    $imageFileType = pathinfo($_FILES["conteudo"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . "circular_" . $nomearquivo . '.' . $imageFileType;


    if (move_uploaded_file($_FILES["conteudo"]["tmp_name"], $target_file)) {
        $mensagem = "O arquivo " . basename($_FILES["conteudo"]["name"]) . " foi enviado.";
    } else {
        $mensagem = "Desculpe. Houve um erro ao enviar o arquivo.";
    }

    $query  = "INSERT INTO circulares(idfuncionario, idunidade,idcurso, idserie, idturma, datacircular, titulo, conteudo)
                    VALUES ($idfuncionario, $idunidade,$idcurso, $idserie, $idturma, '$datacircular', '$titulo', '" . substr($target_file, 3) . "');";


    if ($result = mysql_query($query)) {
        echo "blue| $org cadastrado com sucesso." . $mensagem;
        $msg = "Cadastrado com sucesso." . $mensagem;
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar." . $mensagem;
        $msg = "Erro ao cadastrar." . $mensagem;
        $status = 1;
    }
} elseif ($action == "apagar") {
    $buscaC = "SELECT id, conteudo FROM circulares where id=$id";
    $resultBusca = mysql_query($buscaC);
    $row = mysql_fetch_array($resultBusca, MYSQL_ASSOC);

    $filename = "../" . $row['conteudo'];

    $msgfile = '';

    if (file_exists($filename)) {
        unlink($filename);
        $msgfile .= ' Arquivo excluído.';
    }


    $query  = "DELETE FROM circulares WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Circular removida com sucesso." . $msgfile;
        $msg = "Circular removida com sucesso." . $msgfile;
        $status = 0;
    } else {
        echo "red|Erro ao remover." . $msgfile;
        $msg = "Erro ao remover." . $msgfile;
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
