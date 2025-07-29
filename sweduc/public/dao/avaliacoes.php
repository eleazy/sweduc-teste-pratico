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

if ($action == "cadastra") {
    $idcurso = '';
    foreach ($idcursos as $curso) {
            $idcurso .= $curso . ', ';
    }

    $idcurso = substr($idcurso, 0, -2);


    [$idanoletivo, $ano] = explode('|', $anoletivo);

    $query  = "SELECT COUNT(*) as cnt FROM avaliacoes WHERE avaliacao='$novonome' AND sigla='$novasigla' AND idanoletivo=$idanoletivo";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];
    $insertid = 0;
    if ($cnt == "0") {
        $query  = "INSERT INTO avaliacoes (avaliacao, sigla, idanoletivo, idcurso) VALUES ('$novonome', '$novasigla', $idanoletivo, '$idcurso');";


        if ($result = mysql_query($query)) {
            $msg = "Avaliação " . $novonome . " cadastrada com sucesso.";
            $status = 0;
            $insertid = mysql_insert_id();
        } else {
            $status = 1;
            $msg = "Erro ao cadastrar avaliação.";
        }


        $parametroscsv = $insertid . "," . $novonome . "," . $novasigla . "," . $idanoletivo . "," . $query;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        echo $insertid . "|" . $novonome . "|" . $novasigla . "|" . $ano . htmlentities("|Avaliação") . " $novonome cadastrada.";
    } else {
        echo "0|$novonome|$novasigla|$ano" . htmlentities("|Avaliação ") . $novonome . htmlentities(" já existe!.");
        $status = 1;
        $msg = "Avaliação $novonome já existe !";
        $parametroscsv = $cnt . "," . $novonome . "," . $novasigla . "," . $idanoletivo;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "updateAvaliacao") {
    $idcurso = '';
    foreach ($curso as $cursos) {
        $idcurso .= $cursos . ', ';
    }

    $idcurso = substr($idcurso, 0, -2);



    $query  = "UPDATE avaliacoes SET avaliacao='$novonome', sigla='$novasigla', idcurso = '" . $idcurso . "' WHERE id=$id";
    if ($result = mysql_query($query)) {
        $query1  = "SELECT
                    GROUP_CONCAT( DISTINCT cursos.curso)  cursos
                FROM
                    cursos
                WHERE
                    cursos.id IN ($idcurso)";
        $result1 = mysql_query($query1);
        $c = '';
        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
            $c .= $row1['cursos'] . ',';
        }

        echo htmlentities("blue|Avaliação atualizada.|" . rtrim($c, ','));
        $msg = "Avaliação " . $novonome . " atualizada com sucesso.";
        $status = 0;
    } else {
        echo htmlentities("red|Erro na atualização.");
        $status = 1;
        $msg = "Erro ao atualizar avaliação.";
    }
    $parametroscsv = $id . "," . $novonome . "," . $novasigla;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeGradeAvaliacoes") {
    $sql = '';
    if (isset($idcurso)) {
        $sql .= " WHERE FIND_IN_SET(" . $idcurso . ", idcurso)";
    }

    $query  = "SELECT * FROM avaliacoes   $sql ORDER BY avaliacao";

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
        <input type="button" name="#A<?=$row['id']?>@" class="button insQuadro" value="  <?=$row['avaliacao']?> => #A<?=$row['id']?>@ " onClick="$('#quadro').val($('#quadro').val()+$(this).attr('name'));" /><br /><br />
        <?php
    }
} elseif ($action == "apaga") {
    $query  = "DELETE FROM avaliacoes WHERE id=$id";
    $queryAv = "SELECT avaliacao FROM avaliacoes WHERE id=" . $id;
    $resultadoAv = mysql_query($queryAv);
    while ($linhaAv = mysql_fetch_array($resultadoAv)) {
        $avaliacao = $linhaAv['avaliacao'];
    }
    if ($result = mysql_query($query)) {
        echo htmlentities("blue|Avaliação removida.");
        $msg = "Avaliação " . $avaliacao . " apagada com sucesso.";
        $status = 0;
    } else {
        echo htmlentities("red|Erro ao remover avaliação.");
        $status = 1;
        $msg = "Erro ao apagar avaliação.";
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}

?>
