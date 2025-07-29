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


$varvar = $idfuncionarioprofessor;

//echo "<script> console.log(".$varvar.")</script>";


$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $erro = 0;
    foreach ($idturma as $idtur) {
        $query = "INSERT INTO grade (idanoletivo, idserie, idturma, iddisciplina, cargahoraria) VALUES ($idanoletivoCadastrar,$idserieCadastra,$idtur,$iddisciplina,'$cargahoraria');";
        $rrr = '';
        if ($result = mysql_query($query)) {
            $idgrade = mysql_insert_id();

            foreach ($idfuncionarioprofessor as $k) {
                $query1 = "INSERT INTO grade_funcionario (idgrade, idfuncionario) VALUES ( $idgrade, $k )";

                $result1 = mysql_query($query1);
                if (!$result1) {
                    $erro++;
                }
            }
        } else {
            $erro++;
        }


/*
        foreach ( $idavaliacao as $idaval) {
            $query  = "INSERT INTO disciplinaturma_avaliacoes(iddisciplinaturma, idavaliacao) VALUES ($iddisciplinaturma, $idaval);";
            $msg1 .= $query."<br>";
            if (!($result = mysql_query($query))) $erro++;
        }
*/
    }

    if ($erro == 0) {
        echo "blue|Grade cadastrada com sucesso.";
        $msg = "Grade cadastrada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ($erro) ao cadastrar grade. ";
        $msg = "Erro ($erro) ao cadastrar grade.";
        $status = 1;
    }
    $parametroscsv = $idgrade . ',' . $idanoletivo . ',' . $idserie . ',' . $idtur . ',' . $iddisciplina . ',' . $idfuncionario;
    salvaLog($idpessoalogin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "DELETE FROM grade WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        echo "green|Grade removida com sucesso.";
        $msg = "Grade removida com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro 1 ao remover grade.";
        $msg = "Erro 1 ao remover grade.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "atualiza") {
    $erro = 0;
    $query  = "DELETE FROM grade_funcionario WHERE idgrade=" . $id;
    $result = mysql_query($query);

    $qs = '';

    foreach ($idfuncionarioprofessor as $k) {
        $query1 = "INSERT INTO grade_funcionario (idgrade, idfuncionario) VALUES ( $id, $k );";
        $result1 = mysql_query($query1);

        $qs .= '** ' . $query1;
        if (!$result1) {
            $erro++;
        }
    }
    $query  = "UPDATE grade set cargahoraria='$cargahoraria' WHERE id=" . $id;
    $result = mysql_query($query);
    if ($erro == 0) {
        echo "blue|Composição atualizada com sucesso.";
        $msg = "Composição atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar composição.";
        $msg = "Erro ao atualizar composição.";
        $status = 1;
    }
    $idfprofessor = implode(",", $idfuncionarioprofessor);
    $parametroscsv = $idanoletivoCadastrar . ',' . $idserie . ',' . $idtur . ',' . $iddisciplina . ',' . $idfprofessor . ',' . $cargahoraria;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeGrade") {
    $sql = '';
    if (isset($idcurso)) {
        $sql .= " and cursos.id= " . $idcurso ;
    }

    $query = "SELECT
              grade.id as dtfid, serie, disciplina, turma, curso
            FROM
              grade, series, cursos, turmas, disciplinas
            WHERE
              series.idcurso=cursos.id AND
              turmas.idserie=series.id AND
              grade.idserie=series.id AND
              grade.iddisciplina=disciplinas.id AND
              grade.idturma=turmas.id AND
              grade.idanoletivo=$idanoletivoCadastrar AND
              cursos.idunidade=" . $idunidade . "
              " . $sql . "
            ORDER BY curso ASC,serie ASC,turma ASC,disciplina ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['dtfid'] . '">' . $row['curso'] . '::' . $row['serie'] . '::' . $row['turma'] . '::' . $row['disciplina'] . '</option>';
    }
} elseif ($action == "recebeGradeOrdem1") {
    $query = "SELECT grade.id as dtfid, serie, disciplina, turma, curso FROM grade, series, cursos, turmas, disciplinas WHERE series.idcurso=cursos.id AND turmas.idserie=series.id AND grade.idserie=series.id AND grade.iddisciplina=disciplinas.id AND grade.idturma=turmas.id AND grade.idanoletivo=$idanoletivoCadastrar AND cursos.idunidade=" . $idunidade . " ORDER BY curso ASC,serie ASC,disciplina ASC,turma ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['dtfid'] . '">' . $row['curso'] . '::' . $row['serie'] . '::' . $row['turma'] . '::' . $row['disciplina'] . '</option>';
    }
}
