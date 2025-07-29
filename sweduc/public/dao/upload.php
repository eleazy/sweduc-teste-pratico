<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "apaga") {
    $arquivo = ($arquivo);

    // print_r( scandir("../clientes/$cliente/upload"));
    echo $buscaArq = "SELECT
                            *
                        FROM
                            upload_arquivo
                        WHERE
                            id = '" . ($arquivo) . "' ORDER BY id DESC LIMIT 1";

    $execBusca = mysql_query($buscaArq);
    $row = mysql_fetch_array($execBusca, MYSQL_ASSOC);

    $sqlTurma =  $serie > 0 ? " AND series.id IN (" . $serie . ")" : '';
    $sqlunidade =  $unidade > 0 ? " AND cursos.idunidade IN (" . $unidade . ")" : '';

    $queryBuscaTurma = "SELECT DISTINCT
                                        GROUP_CONCAT(DISTINCT (turmas.id)) id,
                                        GROUP_CONCAT(DISTINCT (series.id)) idseries
                                    FROM
                                        series,
                                        turmas,
                                        cursos
                                    WHERE
                                        turmas.idserie = series.id and
                                        series.idcurso = cursos.id
                                            " . $sqlTurma . $sqlunidade;



    $resultBuscaTurma = mysql_query($queryBuscaTurma);
    $rowBuscaTurma = mysql_fetch_array($resultBuscaTurma, MYSQL_ASSOC);

    $queryArq = "DELETE FROM upload_arquivo WHERE  id = " . $arquivo;
    $resultArq = mysql_query($queryArq);

    $query = "DELETE FROM turmas_arquivos WHERE  idturma in (" . $rowBuscaTurma['id'] . ") and idarquivo=" . $arquivo;
    $result = mysql_query($query);
    $sqlTurma =  (is_countable($rowBuscaTurma['idseries']) ? count($rowBuscaTurma['idseries']) : 0) > 0 ? " AND idserie IN  (" . $rowBuscaTurma['idseries'] . ")" : '';


    $query = "DELETE FROM upload_serie WHERE  idupload=" . $arquivo . $sqlTurma;

    $result = mysql_query($query);

    $queryquant = "SELECT DISTINCT
                                        count(id) quant
                                    FROM
                                       upload_serie
                                    WHERE
                                       idupload =" . $arquivo;

    $resultquant = mysql_query($queryquant);
    $rowquant = mysql_fetch_array($resultquant, MYSQL_ASSOC);

    unlink("../clientes/$cliente/upload/" . $row['arquivo']);

    if ($rowquant['quant'] == 0) {
            $msg = "Arquivo $arquivo apagado2.";
            $status = 0;
    } else {
        $msg = "Arquivo $arquivo apagado.";
        $status = 0;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga2") {
    if (unlink("../clientes/$cliente/upload/$arquivo")) {
        $query  = "DELETE FROM turmas_arquivos WHERE arquivo='$arquivo'";
        $result = mysql_query($query);

        $msg = "Arquivo $arquivo apagado.";
        echo "Arquivo $arquivo apagado.";
        $status = 0;
    } else {
        print_r(error_get_last());
        $msg = "Erro ao remover arquivo $arquivo.";
        $status = 1;
        echo "Arquivo $arquivo não foi apagado.";
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
