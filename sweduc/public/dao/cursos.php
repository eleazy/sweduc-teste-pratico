<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
include(__DIR__ . '/../permissoes.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
    ${$k} = $_POST[$k];
}

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $auxiliar = filter_var($_REQUEST['auxiliar'], FILTER_VALIDATE_BOOL) ? 1 : 0;

    $query = "SELECT COUNT(*) as cnt FROM cursos WHERE curso='$curso' AND idunidade=$idunidade";  //AND idanoletivo=$idanoletivo
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];

    if ($cnt == 0) {
        $query = "INSERT INTO cursos (curso, idunidade, tipoperiodo, auxiliar, cargahoraria, cargahorariaminutos) VALUES ('$curso', '$idunidade', '$tipoPeriodo', '$auxiliar','$cargahoraria', '$cargahorariaminutos');";
        $result = mysql_query($query);

        $tipoPer = ($tipoPeriodo == 2) ? 'Bimestre' : "Trimestre";

        echo mysql_insert_id() . "|" . $curso . "|Curso $curso cadastrado.|" . $tipoPer;
        $msg = "Curso $curso cadastrado.";
        $status = 0;
    } else {
        echo "0|$curso|Curso" . $curso . " já existe!";
        $msg = "Curso $curso já existe!";
        $status = 1;
    }
    $parametroscsv = $idunidade . ',' . $curso;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateCurso") {
    $auxiliar = filter_var($_REQUEST['auxiliar'], FILTER_VALIDATE_BOOL) ? 1 : 0;
    $documentoId = filter_var($_REQUEST['documentoId'], FILTER_VALIDATE_INT) ?: null;
    $signatarioId = filter_var($_REQUEST['signatarioId'], FILTER_VALIDATE_INT) ?: null;
    $cargaFiltrada = filter_var($novacargahoraria, FILTER_VALIDATE_INT);
    $cargaminuto = filter_var($novacargahorariaminutos, FILTER_VALIDATE_INT);

    $query = "UPDATE cursos
        SET curso='$novovalor',
            tipoperiodo='$novovalor2',
            auxiliar='$auxiliar',
            boletim_documento_id='$documentoId',
            signatario_id='$signatarioId',
            cargahoraria='$cargaFiltrada',
            cargahorariaminutos='$cargaminuto',
            anoLimite=$anoLimite
        WHERE id=$id";

    if ($result = mysql_query($query)) {
        $query = "SELECT
                group_concat(DISTINCT t.id) idturma
            FROM
                turmas t
                    INNER JOIN
                series s ON t.idserie = s.id
                    INNER JOIN
                cursos c ON s.idcurso = c.id
                    INNER JOIN
                unidades u ON c.idunidade = u.id
            WHERE
                c.id = " . $id . "
            GROUP BY c.id";

        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        if ($result = mysql_query($query)) {
            echo "blue|Curso atualizado.";
            $msg = "Curso: " . $novovalor . " atualizado.";
            $status = 0;
        }
    } else {
        echo "red|Erro ao atualizar o curso.";
        $msg = "Erro ao atualizar curso.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaCurso") {
    $getSeries = "SELECT COUNT(*) as total FROM cursos c
    INNER JOIN series s ON c.id = s.idcurso
    WHERE c.id = " . $id;
    $resultSeries = mysql_query($getSeries);
    $rowSeries = mysql_fetch_array($resultSeries, MYSQL_ASSOC);

    if ($rowSeries['total'] >= '1') {
        echo "red|Erro ao remover o curso. Existem séries ligadas a esse curso";
        $msg = "Erro ao remover curso.";
        $status = 1;
    } else {
        $query = "SELECT COUNT(*) as cnt FROM alunos_matriculas, turmas, series WHERE alunos_matriculas.turmamatricula=turmas.id AND turmas.idserie=series.id AND series.idcurso=" . $id;
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        if ($row['cnt'] == 0) {
            $query = "DELETE FROM cursos WHERE id=$id";
            $queryPegaCurso = "SELECT curso FROM cursos WHERE id=$id";
            $resCurso = mysql_query($queryPegaCurso);
            while ($linhaC = mysql_fetch_array($resCurso)) {
                $cursoDeletado = $linhaC['curso'];
            }
            if ($result = mysql_query($query)) {
                echo "blue|Curso removido.";
                $msg = "Curso: " . $cursoDeletado . " removido.";
                $status = 0;
            } else {
                echo "red|Erro ao remover curso.";
                $msg = "Erro ao remover curso.";
                $status = 1;
            }
            $parametroscsv = $id;
            salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
        } else {
            echo "red|Erro ao remover curso. Curso contém alunos matriculados.";
            $msg = "Erro ao remover curso. Curso contém alunos matriculados.";
            $status = 1;
            $parametroscsv = $id . ',' . $row['cnt'];
            salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
        }
    }
} elseif ($action == "recebeCursos") {
    $whereAnoLimite = "";
    if (isset($_REQUEST['usaAnoLimite']) && isset($_REQUEST['anoLetivo'])) {
        $anoLetivo = (float)str_replace(' ', '', $_REQUEST['anoLetivo']);
        $whereAnoLimite = " AND (anoLimite > " . $anoLetivo . " OR anoLimite = 0)";
    }
    $query = "SELECT * FROM cursos WHERE idunidade='$idunidade' $whereAnoLimite ORDER BY curso ASC";
    $result = is_numeric($idunidade) ? mysql_query($query) : false;

    if (!isset($_REQUEST['ocultarTodos'])) {
        echo '<option value="todos" selected="selected">TODOS</option>';
    }

    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['curso'] . '</option>';
        }
    }
} elseif ($action == "cancelarCurso") {
    $query = "SELECT
                        group_concat(DISTINCT t.id) idturma
                    FROM
                        cursos c
                            LEFT JOIN
                        series s ON s.idcurso = c.id
                            LEFT JOIN
                        turmas t ON t.idserie = s.id
                            LEFT JOIN
                        unidades u ON c.idunidade = u.id
                    WHERE
                        c.id = " . $id . "
                    GROUP BY c.id";

    $queryCursoCancelado = "SELECT curso FROM cursos WHERE id=$id";
    $resCursoCancelado = mysql_query($queryCursoCancelado);
    while ($linhaCursoCancelado = mysql_fetch_array($resCursoCancelado)) {
        $cursoCancelado = $linhaCursoCancelado['curso'];
    }
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $queryAlunos = "SELECT
                        idaluno, nummatricula
                    FROM
                        alunos_matriculas
                    where
                         turmamatricula in (" . $row['idturma'] . ") and status = '1'";

    $resultAlunos = mysql_query($queryAlunos);

    while ($rowAlunos = mysql_fetch_array($resultAlunos, MYSQL_ASSOC)) {
      //  $queryFinanceiro = "update alunos_fichafinanceira set situacao = '3', databaixado = now() where datavencimento > now() and idaluno = " . $rowAlunos['idaluno'] . " and nummatricula = " . $rowAlunos['nummatricula'] . " and situacao = 0";
        $queryFinanceiro = "update alunos_fichafinanceira set situacao = '3', databaixado = now() where idaluno = " . $rowAlunos['idaluno'] . " and nummatricula = " . $rowAlunos['nummatricula'] . " and situacao = 0";

        $resultFinanceiro = mysql_query($queryFinanceiro);
        $rowFinanceiro = mysql_fetch_array($resultFinanceiro, MYSQL_ASSOC);
    }

    $query = "update  alunos_matriculas set status = '3', datastatus = now(), obsSituacao = 'Curso cancelada'  where turmamatricula in (" . $row['idturma'] . ") and status = '1'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($result = mysql_query($query)) {
        echo "blue|Alunos cancelados atualizado.";
        $msg = "Curso: " . $cursoCancelado . " foi cancelado.";
        $status = 0;
    }
} elseif ($action == "recebeCursos2") {
    $w = '';
    if ($idunidade > 0) {
        $w = " WHERE idunidade in (" . $idunidade . ")";
    }

    $query = "SELECT GROUP_CONCAT(cursos.id) id, cursos.curso FROM cursos " . $w . " GROUP BY curso ORDER BY  curso ASC";
    $result = mysql_query($query);
    // echo '<option value=" - " selected="selected"> </option>';
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['curso'] . '</option>';
        }
    }
    /*

      } else if ($action=="recebeCursosDaUnidade") {
      $query = "SELECT * FROM cursos WHERE idunidade=".$idunidade." ORDER BY curso ASC";    // idanoletivo=".$idanoletivo."
      $result = mysql_query($query);
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) echo '<option value="'.$row['id'].'">'.$row['curso'].'</option>';


      } else if ($action=="recebeCursosComTurmas") {
      //if ($idanoletivo!="todos")  $anoletivo=" AND cursos.idanoletivo=".$idanoletivo; else $anoletivo="";

      $query = "SELECT *, cursos.id as cid FROM cursos, turmas, series WHERE turmas.idserie=series.id AND series.idcurso=cursos.id AND cursos.idunidade=".$idunidade."  GROUP BY curso ORDER BY curso ASC";   //AND cursos.idanoletivo=".$idanoletivo."
      $result = mysql_query($query);
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) echo '<option value="'.$row['cid'].'">'.$row['curso'].'</option>';
     */
} elseif ($action == "recebeCursos2") {
    $query = "SELECT curso,  GROUP_CONCAT(id) id FROM cursos
            group by curso
            ORDER BY curso ASC";
    $result = mysql_query($query);
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['curso'] . '</option>';
        }
    }
} elseif ($action == "recebeCursosDiario") {
    $sql = "";
    if ($idpermissoes != 1 && !in_array($academico[15], $arraydo2)) {
        $sql =  " AND  idfuncionario = " . $idfuncionario ;
        echo '<option value="">' . in_array($academico[15], $arraydo2) . '</option>';
    }

    $query = "SELECT
                    c.curso, c.id
                FROM
                    grade_funcionario gf
                        INNER JOIN
                    grade g ON gf.idgrade = g.id
                        INNER JOIN
                    series s ON g.idserie = s.id
                        INNER JOIN
                    cursos c ON s.idcurso = c.id
                WHERE
                    idunidade =  " . $idunidade . $sql . "  AND idanoletivo = " . $idanoletivo . "
                GROUP BY c.id order by c.curso";



    $result = mysql_query($query);
    // echo '<option value=" - " selected="selected"> </option>';
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['curso'] . '</option>';
        }
    }
} elseif ($action == "recebeEmpresas") {
    $query = "SELECT e.id as id, e.razaosocial as empresa from unidades_empresas ue
            INNER JOIN empresas e ON ue.idempresa=e.id
            WHERE idunidade=" . $idunidade . " ORDER BY e.razaosocial ASC";
    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    echo '<option value=" - " selected="selected"> - </option>';

    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['empresa'] . '</option>';
        }
    }
} elseif ($action == "recebeContasBanco") {
    // $query = "SELECT cb.id as id, cb.nomeb as nomebanco FROM contasbanco cb WHERE cb.idempresa=".$idunidade." AND cb.tipo=0 ORDER BY cb.nomeb ASC";
    $query = "SELECT cb.id as id, cb.nomeb as nomebanco from contasbanco cb
            INNER JOIN unidades_empresas ue on cb.idempresa=ue.idempresa
            WHERE ue.idunidade=" . $idunidade . " AND cb.tipo in (0,2) AND cb.desativado_em IS NULL ORDER BY nomebanco ASC";
    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    echo '<option value=" - " selected="selected"> - </option>';

    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['nomebanco'] . '</option>';
        }
    }
}
