<?php

use App\Academico\Model\Turma;

include('../headers.php');
include('conectar.php');
include_once('logs.php');
include_once('../function/ultilidades.func.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
include(__DIR__ . '/../permissoes.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

/**
 * Caso a requisição não seja do tipo POST verificar se a ação é
 * permitida por GET atráves da lista dentro do in_array
 */
if (!$action && in_array($_REQUEST['action'], ['recebeTurmasAutocomplete'])) {
    $action = $_REQUEST['action'];
}

$msgaluno = str_replace("\r\n", "<br />", $msgaluno ?? '');

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if (!empty($diasdasemana)) {
    $dias_da_semana = implode(',', $diasdasemana);
}

$data_abertura = !empty($abertura) ? implode('-', array_reverse(explode('/', $abertura))) : null;
$data_adiamento = !empty($adiamento) ? implode('-', array_reverse(explode('/', $adiamento))) : null;
$data_encerramento = !empty($encerramento) ? implode('-', array_reverse(explode('/', $encerramento))) : null;

if ($action == "cadastraTurma") {
    $query = "SELECT COUNT(*) as cnt FROM turmas WHERE idserie=$idserie AND turma='$turma'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($row['cnt'] == 0) {
        $query = "INSERT INTO turmas (idserie, idturno, turma, msgaluno, quantalunos, entrada, saida, iniciada_em, adiada_para, terminada_em, dias_da_semana) VALUES ($id2serie, $idturno, '$turma', '$msgaluno', '$quantalunos', '$entrada', '$saida', '$data_abertura',  '$data_adiamento', '$data_encerramento', '$dias_da_semana')";
        if ($result = mysql_query($query)) {
            echo "blue|Turma $turma cadastrada.";
            $msg = "Turma $turma cadastrada.";
        } else {
            echo "red|Erro ($erro) ao cadastrar turma $turma !" . $query;
            $msg = "Erro ($erro) ao cadastrar turma $turma !.";
        }
    } else {
        echo "red|Turma já cadastrada nesta s�rie.";
        $msg = "Turma já cadastrada nesta s�rie.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeSeries") {
    $query = "SELECT id, serie FROM series WHERE idcurso=$idcurso GROUP BY serie ORDER BY serie ASC";
    $result = mysql_query($query);
    $i = 0;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
        $i++;
    }
    if ($i == 0) {
        echo '<option value="0"> - </option>';
    }
} elseif ($action == "nummatriculas") {
    $datade = explode("/", $periodode);
    $datade = $datade[2] . "-" . $datade[1] . "-" . $datade[0];

    $dataate = explode("/", $periodoate);
    $dataate = $dataate[2] . "-" . $dataate[1] . "-" . $dataate[0];

    $query = "SELECT COUNT(*) as cnt FROM alunos_matriculas WHERE turmamatricula=$idturma AND datamatricula BETWEEN '$datade' AND '$dataate' ";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo $row['cnt'];
} elseif ($action == "updateTurma") {
    $turma = Turma::find($id);

    $result = $turma->updateOrFail([
        'turma' => $novovalor,
        'idturno' => $novoturno,
        'msgaluno' => $msgaluno,
        'quantalunos' => $quantalunos,
        'entrada' => $entrada,
        'saida' => $saida,
        'iniciada_em' => $data_abertura,
        'adiada_para' => $data_adiamento,
        'terminada_em' => $data_encerramento,
        'dias_da_semana' => $dias_da_semana,
        'proxima_turma_id' => $_REQUEST['proximaTurma'] ?: null,
    ]);

    if ($result) {
        $msg = "Turma: " . $novovalor . " atualizada.";
        echo "blue|Turma atualizada.";
    } else {
        $msg = "Erro na atualização.";
        echo "red|Erro na atualização.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "setaArquivo") {
   // $arquivo = str_replace("'", "\'", $arquivo);

    $query = "DELETE FROM turmas_arquivos WHERE idturma='$idturma' AND idarquivo='$arquivo'";

    if ($result = mysql_query($query)) {
        if ($chk == "true") {
            $query = "INSERT INTO turmas_arquivos (idturma,idarquivo) VALUES ('$idturma','$arquivo')";
            $result = mysql_query($query);
            $queryPegaTurma = "SELECT turma FROM turmas WHERE id=$idturma";
            $resultadoTurma = mysql_query($queryPegaTurma);
            while ($linhaTurma = mysql_fetch_array($resultadoTurma)) {
                $turmaArq = $linhaTurma['turma'];
            }
            $msg = "Arquivo associado da turma: " . $turmaArq . ".";
        } else {
            $msg = "Arquivo desassociado da turma: " . $turmaArq . ".";
        }
        echo "blue|Turma atualizada.";
    } else {
        $msg = "Erro na atualização.";
        echo "red|Erro na atualização.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "setaArquivo2") {
    $arquivo = str_replace("'", "\'", $arquivo);

    $query  = "DELETE FROM turmas_arquivos WHERE idturma='$idturma' AND arquivo='$arquivo'";
    $queryPegaTurma = "SELECT turma FROM turmas WHERE id=$idturma";
    $resultadoTurma = mysql_query($queryPegaTurma);
    while ($linhaTurma = mysql_fetch_array($resultadoTurma)) {
        $turmaArq = $linhaTurma['turma'];
    }
    if ($result = mysql_query($query)) {
        if ($chk == "true") {
            $query = "
                INSERT INTO turmas_arquivos (idturma, arquivo, idarquivo)
                VALUES (
                    '$idturma',
                    '$arquivo',
                    (SELECT id FROM upload_arquivo WHERE arquivo = '$arquivo' LIMIT 1)
                )
            ";
            if ($result = mysql_query($query)) {
                $msg = "Arquivo associado da turma: " . $turmaArq . ".";
            } else {
                $msg = "Erro na atualização.";
                echo "red|Erro na atualização.";
            }
        } else {
            $msg = "Arquivo desassociado da turma: " . $turmaArq . ".";
        }
        echo "blue|Turma atualizada.";
    } else {
        $msg = "Erro na atualiza��o.";
        echo "red|Erro na atualiza��o.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query = "DELETE FROM turmas WHERE id IN ( $id ) AND id NOT IN ( SELECT turmamatricula FROM alunos_matriculas WHERE turmamatricula IN ( $id ) );";
    $queryPegaTurma = "SELECT turma FROM turmas WHERE id=$id";
    $resultadoTurma = mysql_query($queryPegaTurma);
    while ($linhaTurma = mysql_fetch_array($resultadoTurma)) {
        $turmadeletada = $linhaTurma['turma'];
    }
    $msg = " Turma: " . $turmadeletada . " removida.";
    if ($result = mysql_query($query)) {
        echo "blue|" . mysql_affected_rows() . " Turma(s) removida.";
    } else {
        $msg = "Erro ao remover turma(s).";
        echo "red|Erro ao remover turma(s).";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cancelarTurma") {
    $queryAlunos = "SELECT
                        idaluno, nummatricula
                    FROM
                        alunos_matriculas
                    where
                         turmamatricula in (" . $id . ") and anoletivomatricula in(" . $anoletivo . ")  and status = '1'";


    $resultAlunos = mysql_query($queryAlunos);

    while ($rowAlunos = mysql_fetch_array($resultAlunos, MYSQL_ASSOC)) {
        //  $queryFinanceiro = "update alunos_fichafinanceira set situacao = '3', databaixado = now() where datavencimento > now() and idaluno = " . $rowAlunos['idaluno'] . " and nummatricula = " . $rowAlunos['nummatricula'] . " and situacao = 0";
        $queryFinanceiro = "update alunos_fichafinanceira set situacao = '3', databaixado = now() where idaluno = " . $rowAlunos['idaluno'] . " and nummatricula = " . $rowAlunos['nummatricula'] . " and situacao = 0";

        $resultFinanceiro = mysql_query($queryFinanceiro);
        $rowFinanceiro = mysql_fetch_array($resultFinanceiro, MYSQL_ASSOC);
    }

    $query = "update  alunos_matriculas set status = '3', datastatus = now(), obsSituacao = 'Turma cancelada'  where  turmamatricula in (" . $id . ") and anoletivomatricula in(" . $anoletivo . ") and status = '1'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($result = mysql_query($query)) {
        echo "blue|Alunos cancelados atualizado.";
        $msg = "Curso cancelado.";
        $status = 0;
    }
} elseif ($action == "recebeTurmas") {
    if (!$idserie = filter_var($_REQUEST['idserie'] ?? '', FILTER_VALIDATE_INT)) {
        return;
    }

    if ($anoletivomatricula) {
        $query = "SELECT
                count(*) as qtdemat,
                tm.*,
                DATE_FORMAT(iniciada_em, '%d/%m/%Y') as data_abertura,
                DATE_FORMAT(adiada_para, '%d/%m/%Y') as data_adiamento,
                DATE_FORMAT(terminada_em, '%d/%m/%Y') as data_encerramento,
                turno
            FROM turmas tm
            JOIN turnos ON turnos.id=idturno
            LEFT JOIN alunos_matriculas ON turmamatricula=tm.id AND anoletivomatricula=$anoletivomatricula AND status=1
            WHERE idserie=$idserie
            AND (terminada_em IS NULL OR terminada_em = 0 OR terminada_em > NOW())
            GROUP BY alunos_matriculas.turmamatricula
            ORDER BY tm.turma ASC";
    } else {
        $query = "SELECT
                tm.*,
                DATE_FORMAT(iniciada_em, '%d/%m/%Y') as data_abertura,
                DATE_FORMAT(adiada_para, '%d/%m/%Y') as data_adiamento,
                DATE_FORMAT(terminada_em, '%d/%m/%Y') as data_encerramento,
                turno
            FROM turmas tm
            JOIN turnos ON turnos.id=idturno
            WHERE idserie=$idserie
            AND (terminada_em IS NULL OR terminada_em = 0 OR terminada_em > NOW())
            ORDER BY turma ASC";
    }

    $result = mysql_query($query);

    if (!isset($_REQUEST['ocultarTodos'])) {
        echo '<option value="" selected="selected">SELECIONE A TURMA</option>';
    }

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $data_abertura = $row['data_abertura'];
        // TODO: Hack temporário pra ocultar data nula
        $data_adiamento = $row['data_adiamento'];
        $data_encerramento = $row['data_encerramento'];
        $dias_da_semana = naturalizaDiasDaSemana($row['dias_da_semana']);
        $turno = $row['turno'];
        $informacoes = [];

        foreach ([$data_abertura, $data_adiamento, $dias_da_semana, $turno] as $key => $value) {
            if ($value == '00/00/0000' || $value == '') {
                continue;
            }

            $informacoes[] = $value;
        }

        if ($anoletivomatricula) {
            $informacoes[] = 'Matriculados: ' . $row['qtdemat'];
        }

        $informacoes[] = 'Limite: ' . (($row['quantalunos'] == 0) ? "Sem limite" : $row['quantalunos']);

        echo '<option value="' . $row['id'] . '">' . $row['turma'] . ' (' . implode(' - ', $informacoes) . ')</option>';
    }
} elseif ($action == "recebeTurmas2") {
    // $query = "SELECT * FROM turmas WHERE idserie=".$idserie." GROUP BY turmas.id ORDER BY turma ASC";
    //$query = "SELECT * FROM turmas WHERE idserie=" . $idserie . " ORDER BY turma ASC";
    $sqlAnoB = '';
    if ($anoletivomatricula != 'todos') {
        $sqlAnoB = " AND (SELECT
                            COUNT(id) AS qtdemat
                        FROM
                            alunos_matriculas
                        WHERE
                            turmamatricula = turmas.id
                                AND anoletivomatricula = " . $anoletivomatricula . ") > 0 ";
    }

    $query = "SELECT
                    turmas.*, DATE_FORMAT(iniciada_em, '%d/%m/%Y') as data_abertura, DATE_FORMAT(adiada_para, '%d/%m/%Y') as data_adiamento, DATE_FORMAT(terminada_em, '%d/%m/%Y') as data_encerramento, turno
                FROM
                    turmas JOIN turnos ON idturno = turnos.id
                WHERE
                    idserie = " . $idserie .  $sqlAnoB . "

                ORDER BY turma ASC";

    $result = mysql_query($query);
    $sqlAno = '';
    if ($anoletivomatricula > 0) {
        $sqlAno = " and anoletivomatricula=" . $anoletivomatricula;
    }
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $data_abertura = ($row['data_abertura'] == '00/00/0000' || $row['data_abertura'] == '') ? null : $row['data_abertura'];
        $data_adiamento = ($row['data_adiamento'] == '00/00/0000' || $row['data_adiamento'] == '') ? null : $row['data_adiamento'];
        $data_encerramento = ($row['data_encerramento'] == '00/00/0000' || $row['data_encerramento'] == '') ? null : $row['data_encerramento'];

        $dias_da_semana = naturalizaDiasDaSemana($row['dias_da_semana']);
        $turno = $row['turno'];

        $qalunosnaturma = "SELECT count(*) as qtdemat FROM alunos_matriculas WHERE turmamatricula=" . $row['id'] . $sqlAno . " and  status = 1";
        $ralunosnaturma = mysql_query($qalunosnaturma);
        $qt = mysql_fetch_array($ralunosnaturma, MYSQL_ASSOC);

        $informacoes = array_filter([$data_abertura, $data_adiamento, $dias_da_semana, $turno]);
        $informacoes[] = '';

        echo '<option  value="' . $row['id'] . '">' . $row['turma'] . ' (' . implode(' - ', $informacoes) . 'Matriculados: ' . $qt['qtdemat'] . ' / Limite: ' . (($row['quantalunos'] == 0) ? "Sem limite" : $row['quantalunos']) . ')</option>';
    }
} elseif ($action == "recebeTurmasUnidade") {
    // $query = "SELECT * FROM turmas WHERE idserie=".$idserie." GROUP BY turmas.id ORDER BY turma ASC";
    $query = "SELECT
                    t.id, t.turma, u.unidade
                FROM
                    turmas t
                        INNER JOIN
                    series s ON t.idserie = s.id
                        INNER JOIN
                    cursos c ON s.idcurso = c.id
                        INNER JOIN
                    unidades u ON c.idunidade = u.id
                WHERE
                    idserie in(" . $idserie . ") ORDER BY turma ASC";

    $result = mysql_query($query);
    echo '<option  value=""></option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option  value="' . $row['id'] . '">' . $row['turma'] . ' - ' . $row['unidade'] . '</option>';
    }
} elseif ($action == "recebeTurmasUnidadeFechamento") {
    // $query = "SELECT * FROM turmas WHERE idserie=".$idserie." GROUP BY turmas.id ORDER BY turma ASC";
    $query = "SELECT
                    t.id, t.turma, u.unidade
                FROM
                    turmas t
                        INNER JOIN
                    series s ON t.idserie = s.id
                        INNER JOIN
                    cursos c ON s.idcurso = c.id
                        INNER JOIN
                    unidades u ON c.idunidade = u.id
                WHERE
                    idserie in(" . $idserie . ") ORDER BY turma ASC";

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
       // $qalunosnaturma = "SELECT count(*) as qtdemat FROM alunos_matriculas WHERE turmamatricula=" . $row['id'] . " and anoletivomatricula=" . $anoletivomatricula;
        $ralunosnaturma = mysql_query($qalunosnaturma);
        $qt = mysql_fetch_array($ralunosnaturma, MYSQL_ASSOC);

        echo '<input type="hidden" name="idTurma[]"  value="' . $row['id'] . '" data="' . $row['turma'] . '">';
    }
} elseif ($action == "quantsAlunos") {
    // $query = "SELECT * FROM turmas WHERE idserie=".$idserie." GROUP BY turmas.id ORDER BY turma ASC";
    if (isset($idturma) && $idturma > 0) {
        $query = "SELECT * FROM turmas WHERE id=" . $idturma;
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $limitealunos = $row['quantalunos'];

        $qalunosnaturma = "SELECT count(*) as qtdemat FROM alunos_matriculas WHERE turmamatricula=" . $idturma . " and anoletivomatricula=" . $anoletivomatricula;
        $ralunosnaturma = mysql_query($qalunosnaturma);
        $qt = mysql_fetch_array($ralunosnaturma, MYSQL_ASSOC);
        $jamatriculados = $qt['qtdemat'];
    } else {
        $limitealunos = "-1";
        $jamatriculados = "-1";
    }

    $hiddenfields = '<input type="hidden" name="quantlimitealunosturma" id="quantlimitealunosturma" value="' . $limitealunos . '">';
    $hiddenfields .= '<input type="hidden" name="quantalunosturma" id="quantalunosturma" value="' . $jamatriculados . '">';

    echo $hiddenfields;
} elseif ($action == "recebeTurmasDaUnidade") {
    $query = "SELECT turmas.id, turmas.turma FROM turmas, series,cursos,unidades WHERE turmas.idserie=series.id AND series.idcurso=cursos.id AND cursos.idunidade=" . $idunidade . " GROUP BY turmas.id ORDER BY turma ASC";
    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['turma'] . '</option>';
    }
} elseif ($action == "recebeTurmasSelecionando") {
    if ($idserie > 0) {
        $query = "SELECT turmas.id, turmas.turma, turnos.turno FROM turmas, turnos WHERE idserie=" . $idserie . " AND turmas.idturno=turnos.id ORDER BY turma ASC";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '" selected >' . $row['turma'] . '(' . $row['turno'] . ')</option>';
        }
    }
} elseif ($action == "recebeTurmasTurnos") {
    if ($idturno > 0) {
        $query = "SELECT * FROM turmas WHERE idserie=" . $idserie . " AND idturno=" . $idturno . " ORDER BY turma ASC";
    } else {
        $query = "SELECT * FROM turmas WHERE idserie=" . $idserie . " ORDER BY turma ASC";
    }
    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['turma'] . '</option>';
    }
} elseif ($action == 'recebeTurmasJson') {
    $sqlAnoB = '';
    if (isset($anoletivomatricula) && $anoletivomatricula != 'todos') {
        $sqlAnoB = " AND (SELECT
                            COUNT(id) AS qtdemat
                        FROM
                            alunos_matriculas
                        WHERE
                            turmamatricula = turmas.id
                                AND anoletivomatricula = " . $anoletivomatricula . ") > 0 ";
    }

    $query = "SELECT
            turmas.*,
            DATE_FORMAT(iniciada_em, '%d/%m/%Y') as data_abertura,
            DATE_FORMAT(adiada_para, '%d/%m/%Y') as data_adiamento,
            DATE_FORMAT(terminada_em, '%d/%m/%Y') as  data_encerramento,
            turno
        FROM
            turmas LEFT JOIN
            turnos ON idturno=turnos.id
        WHERE
            idserie = '$idserie'
            $sqlAnoB
        ORDER BY
            turma ASC"
    ;

    $result = mysql_query($query);
    $sqlAno = '';
    if (isset($anoletivomatricula) && $anoletivomatricula > 0) {
        $sqlAno = " and anoletivomatricula=" . $anoletivomatricula;
    }
    $turma = [];
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $data_abertura = ($row['data_abertura'] == '00/00/0000' || $row['data_abertura'] == '') ? null : $row['data_abertura'];
        $data_adiamento = ($row['data_adiamento'] == '00/00/0000' || $row['data_adiamento'] == '') ? null : $row['data_adiamento'];
        $data_encerramento = ($row['data_encerramento'] == '00/00/0000' || $row['data_encerramento'] == '') ? null : $row['data_encerramento'];

        $dias_da_semana = naturalizaDiasDaSemana($row['dias_da_semana']);
        $turno = $row['turno'];

        $turma[] = ['id' => $row['id'], 'turma' => $row['turma'], 'inicio' => $data_abertura, 'adiamento' => $data_adiamento, 'termino' => $data_encerramento, 'dias_semana' => $dias_da_semana, 'turno' => $turno, 'quantalunos' => $row['quantalunos']];
    }
    echo json_encode($turma, JSON_THROW_ON_ERROR);
} elseif ($action == 'recebeTurmasAutocomplete') {
    $term = $_REQUEST['term'];
    $query = "SELECT id, turma FROM turmas WHERE turmas.turma LIKE '%$term%' AND NOT terminada_em BETWEEN 1 AND CURDATE() ORDER BY turma ASC LIMIT 10";
    $result = mysql_query($query);

    $turmas = [];
    while ($row = mysql_fetch_assoc($result)) {
        $turmas[] = [
            "label" => $row['turma'],
            "id" => $row['id']
        ];
    }

    echo json_encode($turmas, JSON_THROW_ON_ERROR);
} elseif ($action == 'verificaturmaativa') {
    $idserie = $_REQUEST['serieId'];
    $idanoletivo = $_REQUEST['anoletivomatricula'];

    $query = "
            SELECT
            id,
            quantalunos
        FROM
            turmas
        WHERE
            idserie = " . $idserie . "
        AND
            quantalunos = -1";

    $result = mysql_query($query);

    $array = mysql_fetch_array($result, MYSQL_ASSOC);

    echo json_encode($array, JSON_THROW_ON_ERROR);
}
