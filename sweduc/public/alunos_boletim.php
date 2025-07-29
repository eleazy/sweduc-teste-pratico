<?php

set_time_limit(900);

require_once 'headers.php';
require_once 'dao/conectar.php';

require_once '../vendor/autoload.php';

use App\Model\AlunosMatricula;
use App\Model\Formula;
use App\Model\Grade;
use App\Model\Notas;
use App\Model\Periodos;
use App\Model\Serie;

$periodo = new Periodos();
$alunos_matricula = new AlunosMatricula();
$grade = new Grade();
$serie = new Serie();
$formulas = new Formula();
$nota = new Notas();

include 'helper_notas.php';

$keys = array_keys($_REQUEST);
foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}
$debug = 0;

$rowtmp = $alunos_matricula->buscarAlunoMatricula($idaluno, $nummatricula, $idunidade);
$idturma = $rowtmp['turmamatricula'];

$res = mysql_query("SELECT idcurso, series.limiterecuperacoes, series.limiteprovasfinais, series.dependencias FROM turmas JOIN series ON series.id = idserie WHERE turmas.id = '$idturma' LIMIT 1;");
$contexto_turma = mysql_fetch_assoc($res);
$curso_id = $contexto_turma['idcurso'];
$limite_de_recuperacoes = $contexto_turma['limiterecuperacoes'];
$limite_dependencias = $contexto_turma['dependencias'];
$limite_provasfinais = $contexto_turma['limiteprovasfinais'];

$ano_letivo = $anoletivo = getAnoLetivo($idanoletivo);
$tipoperiodo = getTipoPeriodoPorTurma($idturma);
$mudancaperiodo = getAnoMudanca($cliente);
$indice_boletim = ($tipoperiodo == "3" && $ano_letivo > $mudancaperiodo) ? " colunaboletim between 41 and 100 " : " colunaboletim between 1 and 40 ";

$periodos = ($usaPeriodosPorCursos) ? $periodo->buscarPeriodosPorCurso($curso_id, $indice_boletim) : $periodo->buscarPeriodosMes(13, $indice_boletim);
$possui_provafinal = count((array) array_filter($periodos, fn($periodo) => $periodo['provafinal'])) > 0;
?>
<input type="hidden" periodo_trimestre="<?=$periodo_trimestre?>" ano_letivo="<?=$ano_letivo?>" indice_boletim="<?=$indice_boletim?>" cliente="<?=$cliente?>">
<table <?php echo ($geraBoletim == 1) ? 'style="border:1px solid #000;padding:5px;"' : 'class="new-table table-striped"' ?> style="width: 100%;" id="tabela-boletim">
    <thead>
    <tr>
        <th width="12%" <?php echo ($geraBoletim == 1) ? 'style="border:1px solid #000;padding:5px;text-align:center;"' : 'class="table-header-repeat line-left-2"' ?>><b>Base Nacional Comum</b></th>
        <?php
        foreach ($periodos as $row1) {
            $classeCssTh = ($geraBoletim == 1) ? 'style="border:1px solid #000;padding:5px;text-align:center;"' : 'class="table-header-repeat line-left-2"';
            $classeCssTd = ($geraBoletim == 1) ? 'style="border:1px solid #000;padding:5px;text-align:center;"' : '';
            if (($mostrapontos == 1) && ($foipontosaprovacao == 0) && (($row1['datade'] == "0000-00-00") || ($row1['datade'] == "") || ($row1['dataate'] == "0000-00-00") || ($row1['dataate'] == ""))) {
                $foipontosaprovacao = 1;
                echo '<th ' . $classeCssTh . '><b>Total<br />Pontos</b></th><th class="table-header-repeat line-left-2"><b>Pontos<br />Aprovação</b></th>';
            }

            echo '<th ' . $classeCssTh . '><b>' . $row1['periodo'] . '</b></th>';
            if (($pontosparaPF == 1) && ($row1['mediaanual'] == 1)) {
                ?>
                <th <?php echo $classeCssTh ?>><b>Pontos para PF</b></th>

                <?php
            }
        }
        ?>
    </tr>
    </thead>
    <tbody>
    <?php
    $alunosnotas = bancoNotas($idanoletivo, 0, $idturma);

    if ($debug) {
        echo $querytmp . "]<br />";
    }

    unset($totalfaltasPeriodo);

    $linhas = 0;
    $linha = 0;
    $linhaREC = 0;
    $linhaPF = 0;
    $linhaREP = 0;
    $linhasdisciplinas = 0;


    foreach ($grade->buscargradeDisciplina($idanoletivo, $idturma, 0, 100, 0) as $row) {
        $serie = new Serie();
        $periodo = new Periodos();
        $formulas = new Formula();
        $nota = new Notas();


        $linhasdisciplinas++;
        $mediaaprovacaorec = -1;
        $notapf = -1;

        $rowserie = $serie->buscarSerieParam($row['grid']);

        $rowserie = $rowserie[0];
        $mediaaprovacao = $rowserie['mediaaprovacao'];
        $mediaaprovacaorec = $rowserie['mediaaprovacaorec'];
        $mediaaprovacaopf = $rowserie['mediaaprovacaopf'];
        $pontosaprovacao = $rowserie['pontosaprovacao'];
        $mediarecuperacao = $rowserie['mediarecuperacao'];
        $dependencias = $rowserie['dependencias'];
        $notavermelha = $mediaaprovacaorec > 0 ? $mediaaprovacaorec : $mediaaprovacao;

        ?>
        <tr>
            <td <?php echo $classeCssTd ?>><b><?= $row['abreviacao'] ?></b></td>
            <?php
            $pontosaprovacaodisciplina = 0;
            $foipontosaprovacao = 0;
            $sitdisc = "- ";

            $cont = 0;

            foreach ($periodos as $row1) {
                if (($mostrapontos == 1) && ($foipontosaprovacao == 0) && (($row1['datade'] == "0000-00-00") || ($row1['datade'] == "") || ($row1['dataate'] == "0000-00-00") || ($row1['dataate'] == ""))) {
                    $foipontosaprovacao = 1;

                    if (($pontosaprovacao - $pontosaprovacaodisciplina) <= 0) {
                        echo '<td ' . $classeCssTd . '>' . $pontosaprovacaodisciplina . '</td><td>Aprov.</td>';
                    } else {
                        echo '<td style="border:1px solid #000;padding:5px;">' . $pontosaprovacaodisciplina . '</td><td style="border:1px solid #000;padding:5px;">' . ($pontosaprovacao - $pontosaprovacaodisciplina) . '</td>';
                    }

                    $pontosaprovacaodisciplina = 0;
                }


                $row2 = $formulas->buscarFormulaDisciplina($row1['id'], $idanoletivo, $idturma, $row['gid']);

                //fabio souza - aqui sera onde irei tratar para não aparecer maos notas alem do esperado

                $cont++;

                echo '<td class="fundotdboletim" ' . $classeCssTd . '>';

                if ($cont <= $databoletim) {
                    $formula = $row2['formula'];
                    $idmedia = $row2['mid'];

                    //falta ver
                    if (($tipodefaltas == '1') && ($comfaltas == 1)) {
                        $queryFLT = "SELECT faltas FROM " . $alunosnotas . " WHERE idavaliacao=0 AND idmedia=" . $idmedia . " AND idaluno=" . $idaluno;

                        $resultFLT = mysql_query($queryFLT);
                        $rowFLT = mysql_fetch_array($resultFLT, MYSQL_ASSOC);
                        if ($rowFLT['faltas'] == "") {
                            $faltaslida = 0;
                        } else {
                            $faltaslida = $rowFLT['faltas'] + 0;
                        }
                        $totalfaltasPeriodo[$row1['id']] = $totalfaltasPeriodo[$row1['id']] + $faltaslida + 0;
                    }

                    if ($debug) {
                        echo $row1['id'] . "=>" . $totalfaltasPeriodo[$row1['id']] . "<br />";
                    }

                    $matches[0] = " ";
                    $pattern = "/#M([0-9]*)@/";
                    while ($matches[0] != null) {
                        preg_match($pattern, $formula, $matches);

                        //$formula = new Formula();// ver ser tem alguma forma melhor
                        $rowIN = $formulas->buscarFormula($matches[1]);

                        if ($rowIN['formula']) {
                            // $formulaIN = "number_format(" . $rowIN['formula'] . ",".$notasdecimais." )";
                            $formulaIN = $rowIN['formula'];
                        }

                        $patternAVA = "/#A([0-9]*)@/";
                        $matchesAVA[0] = " ";
                        $rowAVAnota = "A";
                        while ($matchesAVA[0] != null) {
                            preg_match($patternAVA, $formulaIN, $matchesAVA);

                            $rowAVA = $nota->buscarNotasAvaliacao($matchesAVA[1], $matches[1], $idaluno, $alunosnotas);

                            $rowAVAnota = $rowAVAnota . $rowAVA['nota'];

                            //echo $queryAVA;
                            if (trim($rowAVA['nota']) == "") {
                                $notalida = "0";
                            } else {
                                $notalida = $rowAVA['nota'];
                                if ($mostraAvaliacao == 1) {
                                    echo "<b>" . $rowAVA['disciplina'] . "</b>: " . $rowAVA['avaliacao'] . ": " . $rowAVA['nota'] . "<br />";
                                }
                            }


                            if ($debug) {
                                echo $queryAVA . " ][ " . $rowAVA['nota'] . "][" . $notalida . "   <br />";
                            }
                            $formulaIN = str_replace($matchesAVA[0], $notalida, $formulaIN);
                        }

                        $formula = str_replace($matches[0], $formulaIN, $formula);
                    }


                    $patternAVA = "/#A([0-9]*)@/";
                    $matchesAVA[0] = " ";
                    while ($matchesAVA[0] != null) {
                        preg_match($patternAVA, $formula, $matchesAVA);

                        $nota = new Notas();
                        $rowAVA = $nota->buscarNotasAvaliacao($matchesAVA[1], $row2['mid'], $idaluno, $alunosnotas);

                        $rowAVAnota = $rowAVAnota . $rowAVA['nota'];

                        if (trim($rowAVA['nota']) == "") {
                            $notalida = "0";
                        } else {
                            $notalida = $rowAVA['nota'];
                            if ($mostraAvaliacao == 1) {
                                echo $rowAVA['avaliacao'] . ": " . $rowAVA['nota'] . "<br />";
                            }
                        }
                        $formula = str_replace($matchesAVA[0], $notalida, $formula);
                    }

                    $formulaantes = $formula;
                    if ($debug) {
                        echo "*********** FÓRMULA ******************" . $formula . "]<br />";
                    }
                    if (str_contains(trim($formula), "if")) {
                        eval("$formula");
                    }
                    if ($debug) {
                        echo "*********** FÓRMULA ******************" . $formula . "]<br />";
                    }

                    if (strlen(trim($formula)) > 0) {
                        eval("\$nf=number_format(" . $formula . "," . $notasdecimais . " );");
                        if (( ($nf == 0) && ($rowAVAnota == "A")) || ($nf == "99,999.0")) {
                            echo "-";
                        } else if (($row1['dataate'] < date("Y-m-d")) || ($row1['dataate'] == '0000-00-00')) {
                            if ($mostraAvaliacao == 0) {
                                echo ($nf < $notavermelha) ? '<span style="color:#000;">' . $nf . '</span>' : $nf; //SÓ MOSTRA AS NOTAS DE PERÍODOS FECHADOS
                            }
                        }


                        if (($row1['datade'] != "0000-00-00") && ($row1['datade'] != "") && ($row1['dataate'] != "0000-00-00") && ($row1['dataate'] != "")) {
                            $pontosaprovacaodisciplina = $pontosaprovacaodisciplina + $nf;
                        }

                        if ($row1['recuperacao'] == 1) {
                            if (($nf == 0) && ($rowAVAnota == "A")) {
                                $notarec = -1;
                            } else {
                                $notarec = $nf;
                            }
                        }

                        if ($row1['provafinal'] == 1) {
                            if (($nf == 0) && ($rowAVAnota == "A")) {
                                $notapf = -1;
                            } else {
                                $notapf = $nf;
                            }
                        }

                        if ($row1['situacaofinalanual'] == 1) {
                            if (($nf < $mediaaprovacao) && ($notarec == -1)) {
                                $sitdisc = "RECUPERAÇÃO  ";
                                $linhaREC++;
                            } elseif ($nf < $mediaaprovacaopf && ($notapf == -1) && ($notarec < 7)) {
                                $sitdisc = "PROVA FINAL  ";
                                $linhaPF++;
                            } elseif ($nf >= $mediaaprovacao || ($notapf >= 5)) {
                                $sitdisc = "APROVADO  ";
                                $linha++;
                            } else {
                                $sitdisc = "REPROVADO ";
                                $linhaREP++;
                            }
                        }
                    }
                    if (($tipodefaltas == '1') && ($comfaltas == 1)) {
                        if (($row1['datade'] != "0000-00-00") && ($row1['datade'] != "") && ($row1['dataate'] != "0000-00-00") && ($row1['dataate'] != "")) {
                            echo " F(" . $faltaslida . ")";
                        }
                    }
                }
                echo "</td>";


                if (($pontosparaPF == 1) && ($row1['mediaanual'] == 1)) {
                    echo '<td>' . (100 - $nf) . '</td>';
                }
            }
            if ($databoletim <> "00") {
                echo "<td $classeCssTd>" . $sitdisc . "</td>";
            }
            ?>
        </tr>
        <?php
    }

    if ($comfaltas == 1) {
        $databoletim = ($databoletim == "00") || (trim($databoletim) == "") ? date("m") : $databoletim;
        $query1 = "SELECT * FROM periodos WHERE MONTH(datade)<='$databoletim' AND $indice_boletim ORDER BY colunaboletim ASC";

        echo "<tr><td style='border:1px solid #000;padding:5px;'>TOTAL FALTAS:</td>";
        $result1 = mysql_query($query1);
        if ($tipodefaltas == '1') { // POR DISCIPLINA
            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                echo "<td style='border:1px solid #000;padding:5px;'>" . $totalfaltasPeriodo[$row1['id']] . "</td>";
            }
            echo "</tr>";
        }

        if ($tipodefaltas == '0') {   // POR DIA
            while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                $datede = $row1['datade'];
                $dataate = $row1['dataate'];
                $query2 = "SELECT COUNT(id) as cnt FROM alunos_faltas_dia WHERE idaluno=$idaluno AND datafalta BETWEEN '" . $row1['datade'] . "' AND '" . $row1['dataate'] . "'";
                $result2 = mysql_query($query2);
                $row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
                echo "<td style='border:1px solid #000;padding:5px;'>" . $row2['cnt'] . "</td>";
            }
        }
    }
    ?>
    </tbody>
</table>
<?php if ($pontosparaPF == 0) { ?>
    <div style="text-align:right;width:90%;padding:5px;font-size:150%;"><b>Situação Final: </b><b>
    <?=situacaoFinal($linhaREP, $linhaREC, $linha, $linhaPF, $limite_de_recuperacoes, $possui_provafinal, $limite_dependencias, $limite_provasfinais);?>
    </div>
<?php } ?>
