<?php
include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

if ($action == "marcaFalta") {
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $datafalta)) {
        $dtfalta = explode("/", $datafalta);
        $datafalta = $dtfalta[2] . "-" . $dtfalta[1] . "-" . $dtfalta[0];
    }

    if ($faltou == "false") {
        $query1 = "DELETE FROM alunos_faltas_dia WHERE idaluno=$idaluno AND datafalta='$datafalta'";
    } else {
        $query1  = "INSERT INTO alunos_faltas_dia (idpessoalogin, idaluno, datahora, datafalta, dataatraso) VALUES ($idpessoalogin, $idaluno, '$agora', '$datafalta', '0000-00-00'  )";
    }

    if ($result1 = mysql_query($query1)) {
        if ($faltou == "false") {
            $query2 = "DELETE FROM alunos_ocorrencias WHERE idaluno=$idaluno AND idocorrencia=2 AND idunidade=$idunidade AND DATE(datahora) = '$datafalta'";
        } else {
            $query2 = "INSERT INTO alunos_ocorrencias(idunidade, idaluno, idocorrencia, iddepartamento, iddisciplina, idfuncionario, nummatricula, datahora, assunto) VALUES ( $idunidade, $idaluno, 2, 0, 0, 0, $nummatricula, '$datafalta', 'FALTA');";
        }

        $result2 = mysql_query($query2);


        if ($faltou == "false") {
            $result2 = mysql_query($query2);
        }

        echo "green|Falta do Aluno atualizada com sucesso";
        $msg = "Falta do Aluno atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar a falta do aluno.";
        $msg = "Erro ao atualizar a falta do aluno.";
        $status = 1;
    }
    $parametroscsv = $idaluno . ',' . $datafalta;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "marcaAtraso") {
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dataatraso)) {
        $dtatraso = explode("/", $dataatraso);
        $dataatraso = $dtatraso[2] . "-" . $dtatraso[1] . "-" . $dtatraso[0];
    }

    if ($atrasou == "false") {
        $query1 = "DELETE FROM alunos_faltas_dia WHERE idaluno=$idaluno AND dataatraso='$dataatraso'";
    } else {
        $query1 = "INSERT INTO alunos_faltas_dia (idpessoalogin, idaluno, datahora, datafalta, dataatraso) VALUES ($idpessoalogin, $idaluno, '$agora', '0000-00-00' , '$dataatraso'  )";
    }

    if ($result1 = mysql_query($query1)) {
        if ($atrasou == "false") {
            $query2 = "DELETE FROM alunos_ocorrencias WHERE idaluno=$idaluno AND idocorrencia=1 AND idunidade=$idunidade AND datahora='$dataatraso'";
        } else {
            $query2 = "INSERT INTO alunos_ocorrencias(idunidade, idaluno, idocorrencia, iddepartamento, iddisciplina, idfuncionario, nummatricula, datahora, assunto) VALUES ( $idunidade, $idaluno, 1, 0, 0, 0, $nummatricula, '$dataatraso', 'ATRASO');";
        }

        $result2 = mysql_query($query2);

        echo "green|Atraso do Aluno atualizado com sucesso";
        $msg = "Atraso do Aluno atualizado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar o atraso do aluno.";
        $msg = "Erro ao atualizar o atraso do aluno.";
        $status = 1;
    }
    $parametroscsv = $idaluno . ',' . $datafalta;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "buscaFaltasAtrasos") {
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $datafalta)) {
        $dtfalta = explode("/", $datafalta);
        $datafalta = $dtfalta[2] . "-" . $dtfalta[1] . "-" . $dtfalta[0];
    }

    echo '<tr>';
    echo '<th class="table-header-repeat line-left-2" colspan="2"><b>Aluno</b></th>';
    echo '<th class="table-header-repeat line-left-2" style="width:20px;"></th>';
    if ($diario == 0) {
        echo '<th class="table-header-repeat line-left-2" style="width:10px;"><b>Atrasou</b></th>';
    }
    echo '<th class="table-header-repeat line-left-2" style="width:10px;"><b>Faltou</b></th>';
    echo '</tr>';

    echo "|";
    $cnt = 1;
    $querAnoLetivo = ($idanoletivo > 0) ? " AND alunos_matriculas.anoletivomatricula=" . $idanoletivo . " " : "";

    $query  = "SELECT *, alunos.id as aid, pessoas.id as pid FROM alunos, alunos_matriculas, pessoas, turmas WHERE alunos_matriculas.turmamatricula=turmas.id AND alunos.idpessoa=pessoas.id AND alunos.id=alunos_matriculas.idaluno AND alunos_matriculas.status=1 AND alunos_matriculas.turmamatricula=" . $idturma . $querAnoLetivo . " ORDER BY nome";

//echo '<tr><td colspan=4>['.$query.']</td></tr>';
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $queryA = "SELECT id, datafalta, dataatraso FROM alunos_faltas_dia WHERE idaluno=" . $row['aid'] . " AND ( dataatraso='" . $datafalta . "' OR datafalta='" . $datafalta . "' )";
        $resultA = mysql_query($queryA);
        $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);  ?>
        <tr>
            <td class="text-center" style="padding:5px;width: 10px;">
                <?=$cnt?>
            </td>
            <td style="width: 400px;">
                <input type="hidden" name="idaluno[]" value="<?=$row['aid']?>" /><?=$row['nome']?>
            </td>
            <td class="text-center">
                <input type="button" class="btn primary-color" value="Ocorrências" onClick="editaOcorrencias('<?=$row['aid']?>', <?=$row['nummatricula']?>, <?=$row['idunidade']?>, <?=$row['pid']?>, <?=$row['turmamatricula']?>);" />
            </td>
            <?php

            if ($diario == 0) {
                ?>
                <td>


                    <input type="checkbox" name="atrasos[]" id="atrasos<?= $cnt ?>"
                           value="1" <?php if (($rowA['id']) && ($rowA['dataatraso'] != '0000-00-00')) {
                                echo "checked";
                                     } ?>
                           onClick="marcaAtraso(<?= $row['idunidade'] ?>, <?= $row['aid'] ?>, <?= $row['nummatricula'] ?>, this.checked);"/>
                    <label for="atrasos<?= $cnt ?>"><span></span></label>
                </td>
                <?php
            }
            ?>
            <td>
                <input type="checkbox" name="faltas[]" id="faltas<?=$cnt?>" value="1" <?php if (($rowA['id']) && ($rowA['datafalta'] != '0000-00-00')) {
                    echo "checked";
                                                                 } ?> onClick="marcaFalta(<?=$row['idunidade']?>, <?=$row['aid']?>, <?=$row['nummatricula']?>, this.checked);" />
                <label for="faltas<?=$cnt?>"><span></span></label>
            </td>
        </tr>
        <?php
        $cnt++;
    }
    echo '<tr><td colspan="5"><input type="hidden" name="idturma" value="' . $idturma . '" /><input type="hidden" name="cnt" value="' . $cnt . '"></td></tr>';
} elseif ($action == 'montaFaltaAluno') {
    /**
     * Populando um array com todos os alunos que compareceram a escola/curso com base
     * nas entradas gravadas na portaria filtrada pelo dia
     */
    $dataexp = explode('/', $databusca);
    $data = $dataexp[2] . '-' . $dataexp[1] . '-' . $dataexp[0];
    $query  = "SELECT * FROM portaria WHERE DATE(dataportaria) = '$data' ORDER BY dataportaria DESC";
    $result = mysql_query($query);
    $presentes = [];
    while ($alunoPresente = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $presentes[] = $alunoPresente['idaluno'];
    }

    /**
     * Buscando todos os alunos matriculados na turma passada por parâmetro
     * Depois um loop verifica se o aluno atual consta no array populado acima
     * Caso esteja no array, assumimos que o aluno compareceu. Do contrário, faltou
     * @author Ricardo Gama
     */
    $querAnoLetivo = ($idanoletivo > 0) ? " AND alunos_matriculas.anoletivomatricula=" . $idanoletivo . " " : "";
    $query  = "SELECT *, alunos.id as aid, pessoas.id as pid, turmas.id as tid FROM alunos, alunos_matriculas, pessoas, turmas WHERE alunos_matriculas.turmamatricula=turmas.id AND alunos.idpessoa=pessoas.id AND alunos.id=alunos_matriculas.idaluno AND alunos_matriculas.status=1 AND alunos_matriculas.turmamatricula=" . $idturma . $querAnoLetivo . " ORDER BY nome";
    $result = mysql_query($query);
    $totalPresenca = 0;
    $totalFalta = 0;
    echo '<table class="new-table table-striped" style="width: 100%;">';
    echo '<tr>';
    echo '<th class="table-header-repeat line-left-2"><b>Aluno</b></th>';
    echo '<th class="table-header-repeat line-left-2"><b>Presença / Falta</b></th>';
    echo '</tr>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<tr>';
        echo '<td>' . $row['nome'] . '</td>';
        if (in_array($row['aid'], $presentes)) {
            echo '<td>Compareceu</td>';
            $totalPresenca++;
        } else {
            echo '<td>Faltou</td>';
            $totalFalta++;
        }
        echo '</tr>';
    }
    echo '<tr><td colspan="2">Total de Presenças: ' . $totalPresenca . '</td>';
    echo '<tr><td colspan="2">Total de faltas: ' . $totalFalta . '</td>';
    echo '</table>';
} elseif ($action == 'processaFaltas') {
    /**
     * Populando um array com todos os alunos que compareceram a escola/curso com base
     * nas entradas gravadas na portaria filtrada pelo dia
     */
    $dataexp = explode('/', $databusca);
    $data = $dataexp[2] . '-' . $dataexp[1] . '-' . $dataexp[0];
    $query  = "SELECT * FROM portaria WHERE DATE(dataportaria) = '$data' ORDER BY dataportaria DESC";
    $result = mysql_query($query);
    $presentes = [];
    while ($alunoPresente = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $presentes[] = $alunoPresente['idaluno'];
    }

    /**
     * Buscando todos os alunos matriculados na turma passada por parâmetro
     * Depois um loop verifica se o aluno atual consta no array populado acima
     * Caso esteja no array, assumimos que o aluno compareceu. Do contrário, faltou
     * @author Ricardo Gama
     */
    $totalFaltaInserida = 0;
    for ($i = 0; $i < (is_countable($turmas) ? count($turmas) : 0); $i++) {
        $querAnoLetivo = ($idanoletivo > 0) ? " AND alunos_matriculas.anoletivomatricula=" . $idanoletivo . " " : "";
        $query  = "SELECT *, alunos.id as aid, pessoas.id as pid, turmas.id as tid FROM alunos, alunos_matriculas, pessoas, turmas WHERE alunos_matriculas.turmamatricula=turmas.id AND alunos.idpessoa=pessoas.id AND alunos.id=alunos_matriculas.idaluno AND alunos_matriculas.status=1 AND alunos_matriculas.turmamatricula=" . $turmas[$i] . $querAnoLetivo . " ORDER BY nome";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            if (!in_array($row['aid'], $presentes)) {
                $queryJaTemFalta = "SELECT * FROM alunos_ocorrencias WHERE idocorrencia = 2 AND idaluno = " . $row['aid'] . " AND DATE(datahora) = '$data'";
                $resultJaTemFalta = mysql_query($queryJaTemFalta);
                $rowJaTemFalta = mysql_fetch_array($resultJaTemFalta, MYSQL_ASSOC);
                if (!$rowJaTemFalta) {
                    $datahoraatual = date('Y-m-d H:i:s');
                    $dataatual = date('Y-m-d');
                    $alunoid = $row['aid'];
                    $nummatriculaaluno = $row['nummatricula'];
                    $sqlOcorrencia = "INSERT INTO alunos_ocorrencias (idunidade, idaluno, idocorrencia, iddepartamento, iddisciplina, idfuncionario, nummatricula, datahora, assunto) VALUES ($idunidade, $alunoid, 2, 0, 0, 0, '$nummatriculaaluno', '$datahoraatual', 'FALTA')";
                    $sqlAlunoFaltaDia = "INSERT INTO alunos_faltas_dia (idpessoalogin, idaluno, datahora, datafalta, dataatraso) VALUES ($idpessoalogin, $alunoid, '$datahoraatual', '$dataatual', '0000-00-00')";
                    mysql_query($sqlOcorrencia);
                    mysql_query($sqlAlunoFaltaDia);
                    $totalFaltaInserida++;
                }
            }
        }
    }
    echo "blue|Foram registradas $totalFaltaInserida falta(s)";
}

?>
