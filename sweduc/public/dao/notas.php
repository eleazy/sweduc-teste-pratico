<?php

use App\Academico\Model\Aluno;
use App\Academico\Model\MediaCalculada;

include('../headers.php');
include('conectar.php');
include_once('logs.php');
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}
include('../permissoes.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");
$debug = 0;

$query1 = "SELECT id, professor FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];
$professor = $row1['professor'];

$queryconfig  = "SELECT *,DATE_FORMAT(atualizadoem,'%d-%m-%Y') as atualizadoem FROM configuracoes";
$resultconfig = mysql_query($queryconfig);
$rowconfig = mysql_fetch_array($resultconfig, MYSQL_ASSOC);
$casasdecimaisnotas = $rowconfig['casasdecimaisnotas'];

try {
    $query = "SELECT iddisciplina FROM grade WHERE id=" . $idgrade;
    $result = mysql_query($query);
    if ($result) {
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $iddisciplina = $row['iddisciplina'];
    }
} catch (\Throwable) {
    $iddisciplina = null;
}

try {
    $query = "SELECT disciplina FROM disciplinas WHERE id=" . $row['iddisciplina'];
    $result = mysql_query($query);
    $disciplina = null;
    if ($result) {
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $disciplina = $row['disciplina'];
    }
} catch (\Throwable) {
    $disciplina = null;
}

try {
    $query = "SELECT periodo FROM periodos WHERE id=" . $idperiodo;
    $result = mysql_query($query);
    $periodo = null;
    if ($result) {
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $periodo = $row['periodo'];
    }
} catch (\Throwable) {
    $periodo = null;
}

try {
    $queryal = "SELECT anoletivo FROM anoletivo WHERE id=" . $idanoletivo;
    $resultal = mysql_query($queryal);
    $anoletivo = null;
    if ($resultal) {
        $rowal = mysql_fetch_array($resultal, MYSQL_ASSOC);
        $anoletivo = trim($rowal['anoletivo']);
    }
} catch (\Throwable) {
    $anoletivo = null;
}

try {
    // curso
    $querycur = "SELECT serie,curso FROM grade
        INNER JOIN series ON grade.idserie=series.id
        INNER JOIN cursos ON series.idcurso=cursos.id WHERE grade.id=" . $idgrade;
    $resultcur = mysql_query($querycur);
    $cursoaluno = null;
    if ($resultcur) {
        $rowcur = mysql_fetch_array($resultcur, MYSQL_ASSOC);
        $cursoaluno = $rowcur['curso'];
    }
} catch (\Throwable) {
    $cursoaluno = null;
}

$curso_ei = strpos($cursoaluno, 'nfantil');
$alunosnotas = 'alunos_notas';

function verificaInscritoEletiva($idaluno, $nummatricula, $ideletiva, $idanoletivo)
{
    $queryel = "SELECT * FROM alunos_eletivas WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";

    $resultel = mysql_query($queryel);
    $rel = mysql_fetch_array($resultel, MYSQL_ASSOC);

    $info = ['obs' => $rel['obs'], 'quant' => mysql_num_rows($resultel)];

    return $info;
}

function selecionaGrade($idanoletivo, $idturma, $iddisciplina)
{
    $queryel = "SELECT id FROM grade WHERE idanoletivo=$idanoletivo AND idturma=$idturma AND iddisciplina=$iddisciplina";

    $resultel = mysql_query($queryel);
    $rel = mysql_fetch_array($resultel, MYSQL_ASSOC);

    return $rel['id'];
}

if ($action == "salvanota") {
// ***** CONTROLE DE NOTAS ************ //
    $i = 0;
    $salva = 0;
    $atualiza = 0;
    $mantida = 0;
    $salvaF = 0;
    $atualizaF = 0;
    $mantidaF = 0;
    $salvamsg = "";
    $atualizamsg = "";
    $msg1 = "";

    foreach ($idaluno as $ida) {
        $query = "SELECT nome FROM pessoas, alunos WHERE alunos.idpessoa=pessoas.id AND alunos.id=" . $ida;
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $nomealuno = $row['nome'];

        $coluna = 0;
        $notaavalia = $_POST['nota'];
        $notaava = str_replace(',', '.', $notaavalia[$ida]);
        $idgrade = (isset($gradel[$ida]) && $gradel[$ida] > 0) ? $gradel[$ida] : $idgrade;

        foreach ($idavaliacao[$ida] as $idav) {
            if ($cond[$ida][$idav] == true) {
                $query = "SELECT " . $alunosnotas . ".id, nota, medias.id as mid FROM " . $alunosnotas . ",medias WHERE medias.id=" . $alunosnotas . ".idmedia AND medias.idgrade=" . $idgrade . " AND " . $alunosnotas . ".idavaliacao=$idav AND medias.idperiodo=$idperiodo AND " . $alunosnotas . ".idaluno=$ida";
                $result = mysql_query($query);
                if ($debug) {
                    echo $query . "<br />";
                }
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $idnota = $row['id'];
                $nota = $row['nota'];
                $idmedia = $row['mid'];

                $query = "SELECT avaliacao FROM avaliacoes WHERE id=" . $idav;
                $result = mysql_query($query);
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $avaliacao = $row['avaliacao'];

                if ($idnota == "") {
                    if ($notaava[$coluna] != "") {
                        $queryMedia = "SELECT id FROM medias WHERE idgrade=" . $idgrade . " AND idperiodo=" . $idperiodo;
                        $resultMedia = mysql_query($queryMedia);
                        if ($debug) {
                            echo $queryMedia . "<br />";
                        }
                        $rowMedia = mysql_fetch_array($resultMedia, MYSQL_ASSOC);
                        $idmedia = $rowMedia['id'];

                        $salva++;
                        $query1 = "INSERT INTO " . $alunosnotas . "(idmedia, idavaliacao, idaluno, datahora, nota, faltas) VALUES ($idmedia, $idav, $ida, '$agora', '$notaava[$coluna]',0);";
                        $result1 = mysql_query($query1);

                        $salvamsg .= "Aluno:" . $nomealuno . " - Período: " . $periodo . " - Disciplina: " . $disciplina . " - Avaliação: " . $avaliacao . " - Nota: " . $notaava[$coluna] . "<br />";

                        if ($debug) {
                            echo $query1 . "<br />";
                        }
                    }
                } else {
                    if ($nota != $notaava[$coluna]) {
                        if ($notaava[$coluna] == "") {
                            $aluno = Aluno::find($ida);
                            foreach ($aluno->matriculas as $matricula) {
                                MediaCalculada::where('matricula_id', $matricula->id)->delete();
                            }
                            $query1 = "DELETE FROM " . $alunosnotas . " WHERE id=" . $idnota;
                        } else {
                            $query1 = "UPDATE " . $alunosnotas . " SET nota='$notaava[$coluna]', datahora='$agora' WHERE id=" . $idnota;
                        }
                        $result1 = mysql_query($query1);
                        if ($debug) {
                            echo $query1 . "<br />";
                        }
                        $atualiza++;

                        $atualizamsg .= "Aluno:" . $nomealuno . " - Período: " . $periodo . " - Disciplina: " . $disciplina . " - Avaliação: " . $avaliacao . " - Nota anterior: " . $nota . " - Nota atualizada: " . $notaava[$coluna] . "<br />";
                    } else {
                        $mantida++;
                    }
                }
            } else {
                $mantida++;
            }
            $coluna++;
        }
        //$i++;
        // ****** CONTROLE DE FALTAS POR DISCIPLINA *****************************


        $faltasAluno = $faltas[$i];
        $query = "SELECT " . $alunosnotas . ".id, faltas, medias.id as mid FROM " . $alunosnotas . ", medias WHERE medias.id=" . $alunosnotas . ".idmedia AND medias.idgrade=" . $idgrade . " AND medias.idperiodo=" . $idperiodo . " AND " . $alunosnotas . ".idavaliacao=0 AND idaluno=" . $ida;
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $idmedia = $row['mid'];

        if ($row['id'] == "") {
            $queryMedia = "SELECT id FROM medias WHERE idgrade=" . $idgrade . " AND idperiodo=" . $idperiodo;
            $resultMedia = mysql_query($queryMedia);
            if ($debug) {
                echo $queryMedia . "<br />";
            }
            $rowMedia = mysql_fetch_array($resultMedia, MYSQL_ASSOC);
            $idmedia = $rowMedia['id'];

            if ($faltasAluno != "") {
                $query1 = "INSERT INTO " . $alunosnotas . "(idmedia, idavaliacao, idaluno, datahora, nota, faltas) VALUES ($idmedia, 0, $ida, '$agora', 0,'$faltasAluno');";
                if ($result1 = mysql_query($query1)) {
                    $salvaF++;
                }
            }
        } else {
            if ($faltasAluno != $row['faltas']) {
                $query1 = "UPDATE " . $alunosnotas . " SET faltas='$faltasAluno', datahora='$agora' WHERE idmedia=$idmedia AND idavaliacao=0 AND idaluno=$ida";
                if ($result1 = mysql_query($query1)) {
                    $atualizaF++;
                }
            } else {
                $mantidaF++;
            }
        }
        $i++;
    }
    $msg1 = "";
    if (($salvaF > 0) || ($atualizaF > 0) || ($mantidaF > 0)) {
        $msg1 = $salvaF . " faltas salvas.<br />" . $atualizaF . " faltas atualizadas." . $mantidaF . " faltas inalteradas.";
        $status = 0;
    } else {
        $msg1 = "";
        $status = 1;
    }



    $msg = $salva . " notas salvas<br /><br />" . $salvamsg . "<hr />" . $atualiza . " notas atualizadas.<br /><br />" . $atualizamsg . "<hr />" . $mantida . " notas inalteradas." . $msg1;
    $parametroscsv = '';
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    // echo "blue|$salva notas salvas. $atualiza notas atualizadas. $mantida notas inalteradas." . $msg1;
    echo "blue|Notas salvas.";
} elseif ($action == "buscaTurmaSerie") {
    $query1 = "SELECT *, turmas.id as tid FROM turmas, series, cursos WHERE series.idcurso=cursos.id AND cursos.idunidade=$idunidade AND turmas.idserie=series.id ORDER BY curso,serie,turma ASC";
    $result1 = mysql_query($query1);
    echo '<option value="-1"> - </option>';
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo '<option value="' . $row1['tid'] . '">' . $row1['curso'] . " :: " . $row1['serie'] . " :: " . $row1['turma'] . '</option>';
    }
} elseif ($action == "buscaTurmaDisciplinas") {
    $sql = "";
    if ($perfil != "ADMIN") {
        if ($professor == 1) {
            $sqlfrom = " , grade_funcionario ";
            $sql = " AND grade_funcionario.idgrade=grade.id AND grade_funcionario.idfuncionario=" . $idfuncionario;
        }
    }

    // lança notas infantil/fund e medio
    $cursosEdInf = '';
    $qq = "SELECT group_concat(id) as cursosid FROM cursos where curso LIKE '%nfantil%'";
    $rr = mysql_query($qq);
    $ro = mysql_fetch_array($rr, MYSQL_ASSOC);

    if ($edinfantil == 1) {
        $cursosEdInf .= (!$ro['cursosid']) ? "" : " AND cursos.id IN (" . $ro['cursosid'] . ") ";
    } else {
        $cursosEdInf .= (!$ro['cursosid']) ? "" : " AND cursos.id NOT IN (" . $ro['cursosid'] . ") ";
    }

    $disc_eletiva = (isset($eletiva) && $eletiva == 1) ? " AND numordem BETWEEN 1000 AND 1099 " : " AND numordem < 1000 ";
    $group_eletiva = (isset($eletiva) && $eletiva == 1) ? " GROUP BY disciplinas.id " : " ";

    if (isset($eletiva) && $eletiva == 1) {
        $query1 = "SELECT
                      GROUP_CONCAT(grade.id) as did, disciplina,curso FROM grade, disciplinas, turmas, series, cursos $sqlfrom
                    WHERE
                      grade.idserie=series.id AND series.idcurso=cursos.id AND cursos.idunidade=$idunidade AND grade.idturma=turmas.id " . $sql . "
                      AND grade.iddisciplina=disciplinas.id AND grade.idanoletivo=" . $idanoletivo .  $cursosEdInf . " " . $disc_eletiva . $group_eletiva . "  ORDER BY curso ASC, serie ASC, turma ASC, disciplina ASC";
        $result1 = mysql_query($query1);
        echo "<option value='-1'> - </option>";
        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
            echo '<option value="' . $row1['did'] . '">' . $row1['curso'] . " :: " . $row1['disciplina']  . '</option>';
        }
    } else {
        $query1 = "SELECT
                      *, grade.id as did FROM grade, disciplinas, turmas, series, cursos $sqlfrom
                    WHERE
                      grade.idserie=series.id AND series.idcurso=cursos.id AND cursos.idunidade=$idunidade AND grade.idturma=turmas.id " . $sql . "
                      AND grade.iddisciplina=disciplinas.id AND grade.idanoletivo=" . $idanoletivo .  $cursosEdInf . " " . $disc_eletiva . $group_eletiva . "  ORDER BY curso ASC, serie ASC, turma ASC, disciplina ASC";
        $result1 = mysql_query($query1);
        echo "<option value='-1'> - </option>";
        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
            $showvalue = (isset($eletiva) && $eletiva == 1) ? $row1['curso'] . " :: " . $row1['disciplina'] : $row1['curso'] . " :: " . $row1['serie'] . " :: " . $row1['turma'] . " :: " . $row1['disciplina'];
            echo '<option value="' . $row1['did'] . '">' . $showvalue . '</option>';
        }
    }
} elseif ($action == "buscaTurmaDisciplinas2") {
    $sql = "";
    if ($perfil != "ADMIN" && $professor == 1) {
        $sqlfrom = " , grade_funcionario ";
        $sql = " AND grade_funcionario.idgrade=grade.id AND grade_funcionario.idfuncionario='$idfuncionario'";
    }

    // lança notas infantil/fund e medio
    $qq = "SELECT group_concat(id) as cursosid FROM cursos where curso LIKE '%nfantil%'";
    $rr = mysql_query($qq);
    $ro = mysql_fetch_array($rr, MYSQL_ASSOC);

    $query1 = "SELECT *, grade.id as did
        FROM grade, disciplinas, turmas, series, cursos
        $sqlfrom
        WHERE grade.idserie=series.id
        AND series.idcurso=cursos.id
        AND cursos.idunidade='$idunidade'
        AND grade.idturma=turmas.id
        $sql
        AND grade.iddisciplina=disciplinas.id
        AND grade.idanoletivo='$idanoletivo'
        AND grade.iddisciplina = '{$_POST['iddisciplina']}'
        AND grade.idturma = '$idturma'
        ORDER BY curso ASC, serie ASC, turma ASC, disciplina ASC";

    $result1 = mysql_query($query1);
    $er = mysql_error();

    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo $row1['did'] ?? '';
    }
} elseif ($action == "buscaAvaliacoes") {
    $query1 = "SELECT avaliacoes.* FROM disciplinas_avaliacoes
                    INNER JOIN avaliacoes ON avaliacoes.id=disciplinas_avaliacoes.idavaliacao
                    WHERE idgrade IN ($idgrade) AND idperiodo=" . $idperiodo;

    $result1 = mysql_query($query1);
    $opts = '<option value="0">Selecione a Avaliação</option>';
    $resp = '';
    $avals = '';
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        $resp .= '<option value="' . $row1['id'] . '">' . $row1['avaliacao'] . '</option>';
        $avals .= $row1['id'] . ',';
    }

    $optAll = '<option value="' . rtrim($avals, ',') . '">Visualização Geral</option>';
    // $optAll = '';

    echo $opts . $optAll . $resp;
} elseif ($action == "buscaAvaliacoesEi") {
    $query1 = "SELECT avaliacoes.* FROM disciplinas_avaliacoes
                    INNER JOIN avaliacoes ON avaliacoes.id=disciplinas_avaliacoes.idavaliacao
                    WHERE idgrade=$idgrade AND idperiodo=" . $idperiodo;

    $result1 = mysql_query($query1);
    $opts = '<option value="">Selecione a Avaliação</option>';
    $resp = '';
    $avals = '';
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        $resp .= '<option value="' . $row1['id'] . '">' . $row1['avaliacao'] . '</option>';
        $avals .= $row1['id'] . ',';
    }

    $optAll = '<option value="' . rtrim($avals, ',') . '">Visualização Geral</option>';
    // $optAll = '';

    echo $opts . $optAll . $resp;
} elseif ($action == "buscaAvaliacoesElet") {
    $query1 = "SELECT avaliacoes.* FROM disciplinas_avaliacoes
                    INNER JOIN avaliacoes ON avaliacoes.id=disciplinas_avaliacoes.idavaliacao
                    WHERE idgrade IN ($idgrade) AND idperiodo=" . $idperiodo . " GROUP BY id";

    $result1 = mysql_query($query1);
    $opts = '<option value="0">Selecione a Avaliação</option>';
    $resp = '';
    $avals = '';
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        $resp .= '<option value="' . $row1['id'] . '">' . $row1['avaliacao'] . '</option>';
        $avals .= $row1['id'] . ',';
    }

    echo $opts . $optAll . $resp;
} elseif ($action == "buscaProf") {
    $query1 = "SELECT nome FROM pessoas, funcionarios, grade_funcionario WHERE idgrade=" . $idgrade . " AND grade_funcionario.idfuncionario=funcionarios.id AND funcionarios.idpessoa=pessoas.id";
    $result1 = mysql_query($query1);
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo $row1['nome'] . "";
    }
} elseif ($action == "cadastra_diversificacao") {
    if (isset($id) && $id > 0) {
        $query1 = "UPDATE boletim_diversificada
                   SET nota = '$nota',
                       diversificada = '$diversificacao',
                       significado = '$significado',
                       descricao = '$descricao'
                   WHERE id = $id";

        if (mysql_query($query1)) {
            echo 'blue|Cadastrado com sucesso';
        } else {
            echo 'red|Erro ao cadastrar';
        }
    } else {
        $query1 = "INSERT INTO boletim_diversificada(nota, diversificada, significado, descricao)
                   VALUES ('$nota', '$diversificacao', '$significado', '$descricao');";

        if (mysql_query($query1)) {
            echo 'blue|Cadastrado com sucesso';
        } else {
            echo 'red|Erro ao cadastrar';
        }
    }
} elseif ($action == "apaga_diversificacao") {
    if (isset($id) && $id > 0) {
        $query1 = "DELETE FROM boletim_diversificada WHERE  id = " . $id;
        if (mysql_query($query1)) {
            echo 'blue|Apagado com sucesso';
        } else {
            echo 'red|Erro ao apagar';
        }
    }
} elseif ($action == "buscaNotasElet") {
    //ticket com as notas para tirar as medias

    echo '<tr><th class="table-header-repeat line-left-2" colspan="2"><b>Aluno</b></th>';
    echo ($edinfantil == 0) ? '<th class="table-header-repeat line-left-2 noPrint"></th>' : '';
    if ($tipodefaltas == '1') {
        echo '<th class="table-header-repeat line-left-2" style="width: 20px;"><b>Faltas</b></th>';
    }

    if (strpos($idavaliacao, ',')) {
        $query = "SELECT avaliacoes.* FROM disciplinas_avaliacoes
                INNER JOIN avaliacoes ON avaliacoes.id=disciplinas_avaliacoes.idavaliacao
                WHERE idgrade in ($idgrade) AND idperiodo=" . $idperiodo;
    } else {
        $query = "SELECT * FROM avaliacoes WHERE avaliacoes.id IN ($idavaliacao) ORDER BY id ASC";
    }

    $result = mysql_query($query);

    $qdisc = "SELECT iddisciplina FROM grade WHERE id in (" . $idgrade . ") GROUP BY iddisciplina";
    $rdisc = mysql_query($qdisc);
    $rowdisc = mysql_fetch_array($rdisc, MYSQL_ASSOC);

    $iddisciplina = $rowdisc['iddisciplina'];
    //echo '<th><td colspan=4>'.$query.' ['.$idavaliacao.']</td></th>';

    $qalunoselet = "SELECT alunos_eletivas.nummatricula as nummatricula,disciplinas.id as discid
                            FROM alunos_eletivas
                            INNER JOIN disciplinas ON disciplinas.id=alunos_eletivas.ideletiva
                            INNER JOIN grade ON grade.iddisciplina=disciplinas.id
                            INNER JOIN alunos_matriculas ON alunos_matriculas.nummatricula=alunos_eletivas.nummatricula
                            WHERE grade.iddisciplina=" . $iddisciplina . "
                            AND alunos_matriculas.idunidade=" . $idunidade . "
                            GROUP BY alunos_eletivas.nummatricula";


    $ralunoselet = mysql_query($qalunoselet);
    $alunos_inscritos_eletiva = '';
    while ($re = mysql_fetch_array($ralunoselet, MYSQL_ASSOC)) {
        $alunos_inscritos_eletiva .= $re['nummatricula'] . ',';
    }

    // $alunos_inscritos_eletiva = $rowalunoselet['nummatricula'];
    $disciplina_eletiva = $rowalunoselet['discid'];

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<th class="table-header-repeat line-left-2"><b>' . $row['avaliacao'] . '</b></th>';
        $idaval[] = $row['id'];
    }
    //$idavaliacao = implode(",",$idava);

    // echo '<th class="table-header-repeat line-left-2"><b>Observações</b></th>';

    echo '</tr>';

    echo "|";

    $finalquery = "";
    // if ($idturma != "todos")
    //     $finalquery = "AND alunos_matriculas.turmamatricula=(SELECT idturma FROM grade WHERE id=$idgrade)";


    $query = "SELECT *, alunos.id as aid FROM alunos, alunos_matriculas, pessoas, turmas WHERE alunos_matriculas.status<2 AND alunos_matriculas.turmamatricula=turmas.id AND alunos.idpessoa=pessoas.id AND alunos.id=alunos_matriculas.idaluno " . $finalquery . " AND alunos_matriculas.anoletivomatricula=" . $idanoletivo . " AND alunos_matriculas.nummatricula IN (" . rtrim($alunos_inscritos_eletiva, ',') . ") ORDER BY nome ASC";
    $result = mysql_query($query);
    $cnt = 0;
    // echo "<tr><td colspan='10'>".$query."</td></tr>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $idturma = $row['turmamatricula'];

        $verificaElAl = verificaInscritoEletiva($row['aid'], $row['nummatricula'], $disciplina_eletiva, $row['anoletivomatricula']);

        $idgradeAluno = selecionaGrade($idanoletivo, $idturma, $iddisciplina);

        ?>
<tr><td width="10px" class="text-center" style="padding:5px;"><?= ($cnt + 1) ?></td><td width="400px"><input type="hidden" name="idaluno[]" value="<?= $row['aid'] ?>" /><?= $row['nome'] ?></td>

        <?php if ($edinfantil == 0) { ?>
    <td class="noPrint"><input type="button" class="btn green-color" id="medias<?= $row['aid'] ?>" onclick="medias('<?= $idturma ?>', '<?= $row['anoletivomatricula'] ?>', '<?= $row['nome'] ?>',<?= $row['aid'] ?>,<?= $row['nummatricula'] ?>,<?= $idgradeAluno ?>);" value="Médias" /></td>
        <?php } ?>

        <?php
        if ($tipodefaltas == '1') {
            $queryA = "SELECT faltas FROM " . $alunosnotas . ", medias WHERE idaluno=" . $row['aid'] . " AND " . $alunosnotas . ".idmedia=medias.id AND medias.idgrade=" . $idgradeAluno . " AND medias.idperiodo=" . $idperiodo . " AND " . $alunosnotas . ".idavaliacao=0";
            $resultA = mysql_query($queryA);
            $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
            echo '<td width="30px"><input type="text" name="faltas[]" id="faltas' . $cnt . '" class="form-control peq" style="padding:0;" value="' . $rowA['faltas'] . '"  /></td>';
        }

        $id_grade = 0;
        $id_periodo = 0;

        foreach ($idaval as $idava) {
            $queryA = "SELECT nota FROM " . $alunosnotas . ", medias WHERE idaluno=" . $row['aid'] . " AND idmedia=medias.id AND medias.idgrade=" . $idgradeAluno . " AND medias.idperiodo=" . $idperiodo . " AND idavaliacao =" . $idava;
            $resultA = mysql_query($queryA);
            $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);

            $id_grade = $idgradeAluno;
            $id_periodo = $idperiodo;

            // echo '<td>' . "" . '<input type="text" name="nota[' . $row['aid'] . '][]" id="nota' . $cnt . '" data-id="' . $cnt . '" class="inp-form peq noPrint soma' . $idava . ' camponota" value="' . $rowA['nota'] . '" onclick="notaEditar('. $idava . ')" onblur="if($.trim(this.value)<>\'\') if (!(isNaN(this.value))) this.value=parseFloat(this.value).toFixed(' . $notasdecimais . ')" />';
            ?>

        <td>

            <?php if ($curso_ei === false) { ?>
                <!-- <input type="text" name="nota[<?= $row['aid']?>][]" id="nota<?= $cnt ?>" data-id="<?= $cnt ?>" class="inp-form peq noPrint soma<?= $idava ?> camponota" value="<?= number_format($rowA['nota'], 2, '.', '') ?>" onclick="notaEditar(<?= $idava ?>)" onfocus="if(this.value=='0.00') this.value=''" onblur="if($.trim(this.value)!='') if (!(isNaN(this.value))) this.value=parseFloat(this.value).toFixed(<?= $casasdecimaisnotas ?>)" onkeypress="return isNumberKey(event)" style="width:70px;" /> -->

                <input type="text" name="nota[<?= $row['aid']?>][]" id="nota<?= $cnt ?>" data-id="<?= $cnt ?>" class="form-control peq noPrint soma<?= $idava ?> camponota" value="<?= (!empty($rowA['nota'])) ? number_format($rowA['nota'], 2, '.', '') : '' ?>" onclick="notaEditar(<?= $idava ?>)" onblur="if($.trim(this.value)!='') if (!(isNaN(this.value))) this.value=parseFloat(this.value).toFixed(<?= $casasdecimaisnotas ?>)" onkeypress="return isNumberKey(event)" style="width:70px;" />



            <?php } else { ?>
                <input type="text" name="nota[<?= $row['aid']?>][]" id="nota<?= $cnt ?>" data-id="<?= $cnt ?>" class="form-control peq noPrint soma<?= $idava ?> camponota" value="<?= $rowA['nota'] ?>" onclick="notaEditar(<?= $idava ?>)" style="width:70px;" />

            <?php } ?>

            <?php
            echo '<input type="hidden" name="cond[' . $row['aid'] . '][' . $idava . ']" id="cond' . $idava . '" class="form-control peq noPrint soma' . $idava . '" value="false"  />';
            echo '<input type="hidden" name="gradel[' . $row['aid'] . ']" id="gradel' . $idava . '" class="form-control peq noPrint" value="' . $idgradeAluno . '"  />';
            echo '<input type="hidden" debug="' . $idanoletivo . '*' . $idturma . '*' . $iddisciplina . '"  />';

            $mediadaturma[$idava] = $mediadaturma[$idava] + $rowA['nota'];

            echo '<span class="impressao">' . $rowA['nota'] . '</span>';
            echo '<input type="hidden" name="idavaliacao[' . $row['aid'] . '][]" id="idavaliacao' . $cnt . '" class="form-control peq" value="' . $idava . '" /></td>';
            ?>
        <script>
            $(".soma<?= $idava ?>").blur(function () {
                valor = 0;
                $(".soma<?= $idava ?>").each(function () {
                    valor = parseFloat($(this).val()) + valor;
                });

                valor = valor / $("#totalalunos").val();
                $('#media<?= $idava ?>').html(valor.toFixed(<?= $casasdecimaisnotas ?>)); //( ((valor)/($("#cnt").val())) );
            });
        </script>
            <?php
        }
        ?>
    <!-- eletivas observação -->
    <!-- <td>
        <input type="text" id="obseletiva<?= $row['aid'] ?>" name="obseletiva<?= $row['aid'] ?>" class="form-control inline-form" value="<?=$verificaElAl['obs']?>" style="display:inline-block;" />
        <input type="button" class="btn primary-color inline-form" id="btnobseletiva<?= $row['aid'] ?>" value="Atualizar" style="display:inline-block;" />
    </td>

    <script>
        $('#btnobseletiva<?= $row['aid'] ?>').on('click',function(){
            var obs = $('#obseletiva<?= $row['aid'] ?>').val();
            cadastraAlunoEletiva(<?= $disciplina_eletiva ?>,<?= $row['aid'] ?>,<?= $row['nummatricula'] ?>,<?= $row['anoletivomatricula'] ?>,obs);
        });
    </script>  -->
    <!-- //eletivas observação -->

        <?php
        echo '</tr>';
        $cnt++;
    }

    /**
     * Inicio da rotina para calcular as medias
     * @author Fabio Souza
     */
    //echo "<script>alert('$action');</script>";

    if ($edinfantil == 0) {
        $notav = 0;
        $notal = 0;
        $notaa = 0;
        $notab = 0;


        echo "<tr>";

        echo "<td class='noPrint'></td><td></td><td><strong>MÉDIA:</strong> </td>";

        foreach ($idaval as $idava) {
            $query = "SELECT * FROM avaliacoes WHERE avaliacoes.id IN ($idava) ORDER BY id ASC";
            $result = mysql_query($query);

            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $sql = "SELECT nota
                FROM " . $alunosnotas . ", medias
                WHERE idmedia = medias.id
                AND medias.idgrade = $idgrade
                AND medias.idperiodo = $idperiodo
                AND idavaliacao = $idava ";

                $result2 = mysql_query($sql);

                $total_notas = 0;
                $media = 0;

                while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
                    $total_notas = $total_notas + $row2['nota'];

                    if ($row2['nota'] != "") {
                        $media++;
                    }

                    $notav = ($row2['nota'] >= 0  && $row2['nota'] <= 30 ) ? $notav + 1 : $notav;
                    $notal = ($row2['nota'] > 30 && $row2['nota'] <= 50 ) ? $notal + 1 : $notal;
                    $notaa = ($row2['nota'] > 50 && $row2['nota'] < 65 ) ? $notaa + 1 : $notaa;
                    $notab = ($row2['nota'] >= 65 ) ? $notab + 1 : $notab;
                }

                echo '<td>' . "" . round($total_notas / $media, 2) . '</td>';
            }
        }

        echo "</tr>";

        echo "<tr><td class='noPrint'></td><td colspan='2' style='text-align:right;padding-right:30px;'><strong>Quantidade de notas maiores ou igual a 65:</strong> </td><td style='background-color:#FFFFFF;'>" . $notab . "</td></tr>";
        echo "<tr><td class='noPrint'></td><td colspan='2' style='text-align:right;padding-right:30px;'><strong>Quantidade de notas entre 51 e 65:</strong> </td><td style='background-color:#f0ad4e;'>" . $notaa . "</td></tr>";
        echo "<tr><td class='noPrint'></td><td colspan='2' style='text-align:right;padding-right:30px;'><strong>Quantidade de notas entre 31 e 50:</strong> </td><td style='background-color:#F18548;'>" . $notal . "</td></tr>";
        echo "<tr><td class='noPrint'></td><td colspan='2' style='text-align:right;padding-right:30px;'><strong>Quantidade de notas menores ou igual 30:</strong> </td><td style='background-color:#d9534f;'>" . $notav . "</td></tr>";
    }
}
