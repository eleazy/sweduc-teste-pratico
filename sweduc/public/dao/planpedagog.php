<?php

include '../headers.php';
include 'conectar.php';
include_once 'logs.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php';

$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa = " . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$idAnoletivo = mysql_real_escape_string($idAnoletivo);
$turma = mysql_real_escape_string($turma);
$disciplina = mysql_real_escape_string($disciplina);
$periodode = mysql_real_escape_string($periodode);
$periodoate = mysql_real_escape_string($periodoate);
$conteudo = mysql_real_escape_string($conteudo);
$observacao = mysql_real_escape_string($observacao);
$trabalhocasa = mysql_real_escape_string($trabalhocasa);
$trabalhoaula = mysql_real_escape_string($trabalhoaula);
$idturma = mysql_real_escape_string($idturma);
$idserie = mysql_real_escape_string($idserie);
$iddisciplina = mysql_real_escape_string($iddisciplina);

if ($action == "cadastrar") {
    $periodode = explode("/", $periodode);
    $periodode = $periodode[2] . "-" . $periodode[1] . "-" . $periodode[0];

    $periodoate = explode("/", $periodoate);
    $periodoate = $periodoate[2] . "-" . $periodoate[1] . "-" . $periodoate[0];

    if ($trabalhocasa == "") {
        $org = "Planejamento";
    } else {
        $org = "Trabalho de casa";
    }

    $query = "INSERT INTO planejamentopedagogico(idturma, idserie, iddisciplina, periodode, periodoate, conteudo, observacao, trabalhocasa, trabalhoaula, concluido) VALUES ($idturma, $idserie, '$iddisciplina', '$periodode', '$periodoate', '$conteudo', '$observacao', '$trabalhocasa', '$trabalhoaula', '0000-00-00');";

    if ($result = mysql_query($query)) {
        echo "blue| $org cadastrado com sucesso.";
        $msg = $org . " cadastrado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar.";
        $msg = "Erro ao cadastrar.";
        $status = 1;
    }
    $parametroscsv = $idanoletivo . ',' . $numordem . ',' . $disciplina . ',' . $abreviacao . ',' . $basenacional;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cadastrar2") {
    $periodode = explode("/", $periodode);
    $periodode = $periodode[2] . "-" . $periodode[1] . "-" . $periodode[0];

    $periodoate = explode("/", $periodoate);
    $periodoate = $periodoate[2] . "-" . $periodoate[1] . "-" . $periodoate[0];

    $org = $trabalhocasa == "" ? "Planejamento" : "Trabalho de casa";

    foreach ($idDisciplina as $disciplina) {
        foreach ($idTurma as $turma) {
            $query = "SELECT
                          *
                      FROM
                          turmas
                      WHERE
                          id = " . $turma;
            $result = mysql_query($query);
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            $query  = "INSERT INTO planejamentopedagogico(idanoletivo, idturma, idserie, iddisciplina, periodode, periodoate, conteudo, observacao, trabalhocasa, trabalhoaula, concluido)
                            VALUES (" . $idAnoletivo . ",  " . $turma . ", " . $row['idserie'] . ", '$disciplina', '$periodode', '$periodoate', \"" . $conteudo .  "\", \"" . $observacao .  "\", '$trabalhocasa', '$trabalhoaula', '0000-00-00');";

            if ($result = mysql_query($query)) {
                echo "blue| $org cadastrado com sucesso.";
                $msg = $org . " cadastrado com sucesso.";
                $status = 0;
            } else {
                echo "red|Erro ao cadastrar.";
                $msg = "Erro ao cadastrar.";
                $status = 1;
            }
        }
    }

    $parametroscsv = $idanoletivo . ',' . $numordem . ',' . $disciplina . ',' . $abreviacao . ',' . $basenacional;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updatePlanPedagog") {
    $id = mysql_real_escape_string($id);
    $trabalhoaula = mysql_real_escape_string($trabalhoaula);

    $query  = "UPDATE planejamentopedagogico SET trabalhoaula = '$trabalhoaula' WHERE id = $id";

    if ($result = mysql_query($query)) {
        echo "blue|Planejamento atualizado com sucesso.";
        $msg = "Planejamento Pedagogico: Trabalho de Aula " . $trabalhoaula . " cadastrado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar.";
        $msg = "Erro ao atualizar.";
        $status = 1;
    }

    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "update2") {
    $periodode = explode("/", $periodode);
    $periodode = $periodode[2] . "-" . $periodode[1] . "-" . $periodode[0];

    $periodoate = explode("/", $periodoate);
    $periodoate = $periodoate[2] . "-" . $periodoate[1] . "-" . $periodoate[0];

    $org = $trabalhocasa == "" ? "Planejamento" : "Trabalho de casa";

    if ($idDisciplina > 1) {
        foreach ($idDisciplina as $disciplina) {
            foreach ($idTurma as $turma) {
                $query  = "SELECT
                                *
                            FROM
                                turmas
                            WHERE
                                id = " . $turma;
                $result = mysql_query($query);
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $query  = "UPDATE planejamentopedagogico SET
                                            idanoletivo = " . $idAnoletivo . ",
                                            idturma = " . $turma . ",
                                            idserie = " . $row['idserie'] . ",
                                            iddisciplina = " . $disciplina . ",
                                            periodode = '" . $periodode . "',
                                            periodoate = '" . $periodoate . "',
                                            conteudo = '" . $conteudo . "',
                                            observacao = '" . $observacao . "',
                                            trabalhocasa = '" . $trabalhocasa . "',
                                            trabalhoaula =  '" . $trabalhoaula . "',
                                            concluido = '0000-00-00' where id = " . $id;
            }
        }
    } else {
        $query  = "UPDATE planejamentopedagogico SET
        periodode = '" . $periodode . "',
        periodoate = '" . $periodoate . "',
        conteudo = '" . $conteudo . "',
        observacao = '" . $observacao . "',
        trabalhocasa = '" . $trabalhocasa . "',
        trabalhoaula =  '" . $trabalhoaula . "',
        concluido = '0000-00-00' where id = " . $id;
    }

    if ($result = mysql_query($query)) {
        echo "blue| $org cadastrado com sucesso.";
        $msg = $org . " cadastrado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar.";
        $msg = "Erro ao cadastrar.";
        $status = 1;
    }


    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updatePlanPedagogObs") {
    $query = "UPDATE planejamentopedagogico SET observacao = '$obs' WHERE id = $id";

    if ($result = mysql_query($query)) {
        echo "blue|Planejamento atualizado com sucesso.";
        $msg = "Planejamento Pedagogico: " . $obs . " atualizado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar.";
        $msg = "Erro ao atualizar.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateTrabCasa") {
    $query  = "UPDATE planejamentopedagogico SET trabalhocasa = '$trabalhocasa' WHERE id = $id";
    if ($result = mysql_query($query)) {
        echo "blue|Planejamento atualizado com sucesso.";
        $msg = "Planejamento Pedagogico: " . $trabalhocasa . " atualizado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar.";
        $msg = "Erro ao atualizar.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateTrabAula") {
    $query = "UPDATE planejamentopedagogico SET trabalhoaula = '$trabalhoaula' WHERE id = $id";

    if ($result = mysql_query($query)) {
        echo "blue|Planejamento atualizado com sucesso.";
        $msg = "Planejamento Pedagogico: " . $trabalhoaula . " atualizado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar.";
        $msg = "Erro ao atualizar.";
        $status = 1;
    }

    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateDate") {
    if ($cond == 'true') {
        $query  = "UPDATE planejamentopedagogico SET concluido = now() WHERE id = $id";
    } else {
        $query  = "UPDATE planejamentopedagogico SET concluido = '0000-00-00' WHERE id = $id";
    }


    if ($result = mysql_query($query)) {
        echo "blue|Planejamento atualizado com sucesso.";
        $msg = "Planejamento atualizado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar.";
        $msg = "Erro ao atualizar.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagar") {
    $query  = "DELETE FROM planejamentopedagogico WHERE id = $id";
    if ($result = mysql_query($query)) {
        echo "blue|Planejamento removido com sucesso.";
        $msg = "Planejamento removido com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao remover.";
        $msg = "Erro ao remover.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeSemanas") {
    $query  = "SELECT periodode, periodoate, DATE_FORMAT(periodode,'%d/%m/%Y') AS 'datade', DATE_FORMAT(periodoate,'%d/%m/%Y') AS 'dataate' FROM planejamentopedagogico WHERE trabalhocasa = '' AND idserie = $idserie AND iddisciplina = $iddisciplina";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value = "' . $row['periodode'] . '@' . $row['periodoate'] . '">' . $row['datade'] . ' a ' . $row['dataate'] . '</option>';
    }
} elseif ($action == "recebeSemanasTrabCasa") {
    $query  = "SELECT periodode, periodoate, DATE_FORMAT(periodode,'%d/%m/%Y') AS 'datade', DATE_FORMAT(periodoate,'%d/%m/%Y') AS 'dataate' FROM planejamentopedagogico WHERE trabalhocasa<>'' AND idserie = $idserie AND iddisciplina = $iddisciplina";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value = "' . $row['periodode'] . '@' . $row['periodoate'] . '">' . $row['datade'] . '</option>';
    }
} elseif ($action == "recebeSemanasTrabAula") {
    $query  = "SELECT periodode, periodoate, DATE_FORMAT(periodode,'%d/%m/%Y') AS 'datade', DATE_FORMAT(periodoate,'%d/%m/%Y') AS 'dataate' FROM planejamentopedagogico WHERE trabalhoaula<>'' AND idserie = $idserie AND iddisciplina = $iddisciplina";
    $result = mysql_query($query);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value = "' . $row['periodode'] . '@' . $row['periodoate'] . '">' . $row['datade'] . '</option>';
    }
}
