<?php

include_once('dao/conectar.php');
require_once('auth/injetaCredenciais.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

// if(isset($action) && $action == 'cacheboletim') {
//     $var = cacheBoletim($idanoletivo,$idunidade,$idgrade,$idperiodo,$idavaliacao, $idaluno);
//     echo $idaluno;
// }
// if( $_GET['acao'] == 'cache') {
if (isset($action) && $action == 'cacheboletim') {
    // $idanoletivo = 4;
    // $alunos = getAlunosAnoletivo($idanoletivo);
    $alunos = getAluno($idaluno, $idanoletivo);

    $boletimArray = [];

    $iii = 0;

    while ($rowa = mysql_fetch_array($alunos, MYSQL_ASSOC)) { // todos os alunos do ano letivo
        // echo $iii.' = alunoid: '.$rowa['idaluno'].'<br>';
        // $iii++;
        // pega as turmas do aluno
        $turmas = getTurmaAluno($rowa['idaluno'], $rowa['nummatricula'], $rowa['idunidade']);
        // while($rowt = mysql_fetch_array($turmas, MYSQL_ASSOC)) {

        $disciplinasaluno = getDisciplinasGrade($turmas['turmamatricula'], $idanoletivo);

        if (!$disciplinasaluno) {
            // echo '--------- nenhuma disciplina' . $turmas['turmamatricula'] . '<br>';
        } else {
            for ($j = 0; $j < (is_countable($disciplinasaluno) ? count($disciplinasaluno) : 0); $j++) {
                $salvaparada = salvar($rowa['idaluno'], $idanoletivo, $rowa['idunidade'], $turmas['turmamatricula'], $disciplinasaluno[$j]);

                //notasecho (!$salvaparada) ? 'deu ruim:' . $disciplinasaluno[$j] . '<br />' : 'ok<br />';
            }
        }
    }
    // $var = cacheBoletim($idanoletivo,$idunidade,$idgrade,$idperiodo,$idavaliacao, $idaluno);
    // echo $idaluno;
    fillGrade($idaluno);

    fillNotas($idaluno);
}

/*
 * ********************************************************************************************************
 */

function getAlunosAnoletivo($anoletivo)
{
    $stmt = "SELECT * FROM alunos_matriculas WHERE anoletivomatricula = $anoletivo ORDER BY idaluno ASC";
    $result = mysql_query($stmt);
    // $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $result;
}

/*
 * ********************************************************************************************************
 */

function getAluno($idaluno, $anoletivo)
{
    $stmt = "SELECT * FROM alunos_matriculas WHERE idaluno = $idaluno AND anoletivomatricula = $anoletivo LIMIT 1";
    $result = mysql_query($stmt);
    // $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $result;
}

/*
 * ********************************************************************************************************
 */

function getTurmaAluno($idaluno, $nummatricula, $idunidade)
{
    $stmt = "SELECT id,turmamatricula FROM alunos_matriculas WHERE idaluno= $idaluno AND nummatricula= $nummatricula AND idunidade= $idunidade";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $row;
}

/*
 * ********************************************************************************************************
 */

function getDisciplinasGrade($turma, $anoletivo)
{
    $stmt = "SELECT idturma,idserie, group_concat( distinct iddisciplina order by iddisciplina separator ',') as disciplinas FROM grade WHERE idturma=$turma /* AND idanoletivo=$anoletivo */ GROUP BY idturma";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $resposta = (!empty($row['disciplinas'])) ? explode(',', $row['disciplinas']) : false;
    return $resposta;
}

/*
 * ********************************************************************************************************
 */

function getPeriodos()
{
    $stmt = "SELECT * FROM periodos";
    $result = mysql_query($stmt);
    // $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $result;
}

/*
 * ********************************************************************************************************
 */

function getFormulas($idturma, $iddisciplina, $idperiodo, $idanoletivo)
{
    $stmt = "SELECT formula, medias.id as mid FROM medias INNER JOIN grade ON medias.idgrade=grade.id WHERE medias.idperiodo = $idperiodo AND grade.idanoletivo = $idanoletivo AND grade.idturma = $idturma AND grade.iddisciplina = $iddisciplina";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $row;
}

/*
 * ********************************************************************************************************
 */

function getFormula($idmedia)
{
    $stmt = "SELECT formula FROM medias WHERE id = $idmedia";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $row;
}

/*
 * ********************************************************************************************************
 */

function getDisciplina($iddisciplina)
{
    $stmt = "SELECT * FROM disciplinas WHERE id = $iddisciplina";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $row;
}

/*
 * ********************************************************************************************************
 */

function salvar($idaluno, $idanoletivo, $idunidade, $idturma, $iddisciplina, $notas = [])
{
    $checkExist = "SELECT * FROM cache_boletim WHERE idaluno = $idaluno AND idanoletivo = $idanoletivo AND idunidade = $idunidade AND idturma = $idturma AND iddisciplina = $iddisciplina";
    $resultExist = mysql_query($checkExist);
    $qtdeRows = mysql_num_rows($resultExist);
    if ($qtdeRows != 0) {
        // $rowExist = mysql_fetch_array($resultExist, MYSQL_ASSOC);
        // // verifica se o array de notas é igual
        // if( $rowExist['notas'] != serialize($notas) ) {
        //     $updt = "UPDATE cache_boletim SET notas = '".serialize($notas)."' WHERE id = ".$rowExist['id'];
        //     $updtRow = mysql_query($updt);
        //     return (!$updtRow)?false:true;
        // }
        return true;
    } else {
        $criar = "INSERT INTO cache_boletim (idaluno,idanoletivo,idunidade,idturma,iddisciplina) VALUES ($idaluno,$idanoletivo,$idunidade, $idturma, $iddisciplina)";

        $salvar = mysql_query($criar);
        return (!$salvar) ? false : true;
    }
}

/*
 * ********************************************************************************************************
 */

function getNotas($idaluno, $idavaliacao, $idmedia)
{
    $stmt = "SELECT nota, avaliacao, disciplina 
            FROM medias, grade, disciplinas, alunos_notas, avaliacoes 
            WHERE 
                alunos_notas.idmedia=medias.id AND 
                medias.idgrade=grade.id AND 
                grade.iddisciplina=disciplinas.id AND 
                alunos_notas.idavaliacao=avaliacoes.id AND 
                idavaliacao = $idavaliacao AND 
                idmedia = $idmedia AND 
                idaluno = $idaluno";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $row;
}

/*
 * ********************************************************************************************************
 */

function getNotasAval($idaluno, $idavaliacao, $idmedia)
{
    $stmt = "SELECT nota, avaliacao
            FROM alunos_notas, avaliacoes 
            WHERE 
                alunos_notas.idavaliacao=avaliacoes.id AND 
                idavaliacao = $idavaliacao AND 
                idmedia = $idmedia AND 
                idaluno = $idaluno";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    return $row;
}

/*
 * ********************************************************************************************************
 */

function getGrade($turma, $iddisciplina)
{
    $stmt = "SELECT * FROM grade WHERE idturma=$turma AND iddisciplina=$iddisciplina";
    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    return $row;
}

/*
 * ********************************************************************************************************
 */

function fillGrade($idaluno)
{
    $stmt = "SELECT * FROM cache_boletim WHERE idaluno = $idaluno ORDER BY id ASC";
    $result = mysql_query($stmt);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        //echo "<br>idaluno:" . $row['idaluno'];
        if (empty($row['idgrade'])) {
            $grade = getGrade($row['idturma'], $row['iddisciplina']);
            if (!empty($grade['id'])) {
                $updt = "UPDATE cache_boletim SET idgrade = " . $grade['id'] . " WHERE id=" . $row['id'];
                $atualiza = mysql_query($updt);
            }
        }
    }
    return true;
}

/*
 * ********************************************************************************************************
 */

function fillNotas($idaluno)
{
    $nf = null;
    $nota = null;
    $series = [];
    $notapf = null;
    $notarec = null;
    $stmt = "SELECT * FROM cache_boletim WHERE idaluno = $idaluno ORDER BY id ASC";
    $result = mysql_query($stmt);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        // echo "<br>notas:" . $row['idaluno'];
        $idaluno = $row['idaluno'];
        if (empty($row['notas'])) {
            $listanotas = [];
            $padrao = "/#M([0-9]*)@/";
            $padraoAva = "/#A([0-9]*)@/";

            $periodos = getPeriodos();
            $disciplin = getDisciplina($row['iddisciplina']);

            // foreach ($periodos as $periodo) {
            while ($periodo = mysql_fetch_array($periodos, MYSQL_ASSOC)) {
                $formula = getFormulas($row['idturma'], $row['iddisciplina'], $periodo['id'], $row['idanoletivo']);

                if (!empty($formula["formula"])) {
                    /*  DISCIPLINAS COM FÓRMULA MÉDIA (CONTÉM A LETRA M). EX.:  (#M18328@ + #M18344@) / 2   */
                    $formulaDisc = $formula["formula"];
                    $quant_matches = preg_match_all($padrao, $formulaDisc, $matches);

                    for ($j = 0; $j < $quant_matches; $j++) {
                        preg_match($padrao, $formulaDisc, $matches);

                        $formulaMatch = getFormula($matches[1]);

                        // echo '$formulaMatch: '.$formulaMatch["formula"].'<br>';

                        $quant_matchesIn = preg_match_all($padraoAva, $formulaMatch["formula"], $matchesAVA);

                        for ($k = 0; $k < $quant_matchesIn; $k++) {
                            preg_match($padraoAva, $formulaMatch["formula"], $matchesAVA);

                            $notas = getNotas($idaluno, $matchesAVA[1], $matches[1]);

                            $nota = 'A' . $notas['nota'];

                            if (empty($notas['nota'])) {
                                $notalida = 0;
                            } else {
                                $notalida = $notas['nota'];

                                $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['BoletimAvaliacoes'][] = $notas;
                            }

                            $formulaMatch["formula"] = str_replace($matchesAVA[0], $notalida, $formulaMatch["formula"]);
                        }

                        $formulaDisc = str_replace($matches[0], $formulaMatch["formula"], $formulaDisc);
                    }

                    /*  DISCIPLINAS COM FÓRMULA DIRETA. EX.: (#A114@+#A115@)/2   */

                    $quant_matchesAva = preg_match_all($padraoAva, $formulaDisc, $matchesAVA);

                    for ($l = 0; $l < $quant_matchesAva; $l++) {
                        preg_match($padraoAva, $formulaDisc, $matchesAVA);

                        $notas = getNotasAval($idaluno, $matchesAVA[1], $formula["mid"]);

                        $nota = 'A' . $notas['nota'];

                        if (empty($notas['nota'])) {
                            $notalida = 0;
                        } else {
                            $notalida = $notas['nota'];

                            $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['BoletimAvaliacoes'][] = $notas;
                        }

                        $formulaDisc = str_replace($matchesAVA[0], $notalida, $formulaDisc);
                    }

                    // tratar formula
                    if (str_contains(trim($formulaDisc), "if")) {
                        eval("$formulaDisc");
                    }

                    if (strlen(trim($formulaDisc)) > 0) {
                        eval("\$nf=number_format(" . $formulaDisc . ",1 );");
                    }

                    // Boletim Simples (fórmulas calculadas/notas consolidadas)
                    if (( ($nf == 0) && ($nota == "A")) || ($nf == "99,999.0")) {
                        $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['BoletimSimples'][] = ['periodo' => $periodo['periodo'], 'notafinal' => '-'];
                    } else if (($periodo['dataate'] < date("Y-m-d")) || ($periodo['dataate'] == '0000-00-00')) {
                        $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['BoletimSimples'][] = ['periodo' => $periodo['periodo'], 'notafinal' => $nf]; //SÓ MOSTRA AS NOTAS DE PERÍODOS FECHADOS
                    }




// recuperação
                    if ($periodo['recuperacao'] == 1) {
                        $notarec = ( ($nf == 0) && ($nota == "A")) ? -1 : $nf;
                    }
                    $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Recuperacao'] = $periodo['recuperacao'];

                    // prova final
                    if ($periodo['provafinal'] == 1) {
                        $notapf = ( ($nf == 0) && ($nota == "A")) ? -1 : $nf;
                    }
                    $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['ProvaFinal'] = $periodo['provafinal'];

                    // situacaofinalanual
                    if ($periodo['situacaofinalanual'] == 1) {
                        if (($nf < $series['mediaaprovacao']) && ($nf >= $series['mediarecuperacao'])) {
                            if ($notapf == -1) {
                                $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Situacao'] = "PROVA FINAL"; //."][".$mediaaprovacao."][".$mediarecuperacao;
                            } else {
                                if ($nf >= $series['mediaaprovacaopf']) { //$notapf
                                    $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Situacao'] = "APROVADO";
                                } else if ($nf < $series['mediaaprovacaopf']) { //$notapf
                                    //$sitdisc="REP 2 ".$nf."][".$mediaaprovacao."][".$mediarecuperacao;
                                    //$linhaREP++;
                                    if ($notarec == -1) {
                                        $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Situacao'] = "RECUPERAÇÃO"; //.$notapf." ][ ".$mediaaprovacaopf;
                                    } else {
                                        if ($nf >= $series['mediaaprovacaorec']) {//$notarec
                                            $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Situacao'] = "APROVADO";
                                        } else if ($nf < $series['mediaaprovacaorec']) {//$notarec
                                            $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Situacao'] = "REPROVADO";
                                        }
                                    }
                                }
                            }
                        } else if ($nf >= $series['mediaaprovacao']) {
                            $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Situacao'] = "APROVADO  ";
                        } else {
                            $listanotas['periodos'][ $periodo['id'] . '@' . $periodo['periodo'] ]['disciplinas'][$disciplin['id'] . '@' . $disciplin['disciplina']]['Situacao'] = "REPROVADO ";
                        }
                    }
                    // /situacaofinalanual
                }
            }
        }

        if (!empty($listanotas)) {
            $updt = "UPDATE cache_boletim SET notas = '" . serialize($listanotas) . "' WHERE id=" . $row['id'];
            $atualiza = mysql_query($updt);

            //echo (!$atualiza) ? 'deu ruim notas<br />' : 'ok -- notas<br />';
        }
    }
}

// movimentações - fechamento de caixa - verificar
function verificaFechamento($idfuncionario)
{
    // verifica se o usuario tem contabanco
    $qu = "SELECT cb.id FROM funcionarios f INNER JOIN contasbanco cb ON cb.idfuncionario=f.id WHERE f.id=$idfuncionario AND cb.tipo = 1;";
    $eu = mysql_query($qu);
    $quantu = mysql_num_rows($eu);

    if ($quantu < 1) {
        return 0;
    }

    $qconf  = "SELECT caixa_fechamentomanual FROM configuracoes LIMIT 1";
    $rconf = mysql_query($qconf);
    $rwconf = mysql_fetch_array($rconf, MYSQL_ASSOC);
    $caixa_fechamentomanual = $rwconf['caixa_fechamentomanual'];

    // verifica se já houve algum fechamento hoje
    $hojef = date('Y-m-d');
    $q_buscafechamento = "SELECT count(id) as movs from movimentacoes WHERE idfuncionariostatus = $idfuncionario AND datareferencia = '$hojef' AND motivo LIKE 'Fechamento%'";
    $e_buscafechamento = mysql_query($q_buscafechamento);
    $buscafechamento = mysql_fetch_array($e_buscafechamento, MYSQL_ASSOC);
    $caixafechado = $buscafechamento['movs'];

    if ($caixafechado > 0) {
        return [
            'situacao' => "fechado",
            'mensagem' => "Seu caixa já foi fechado por hoje. Você não poderá realizar recebimentos."
        ];
    }

    // se fechamento manual habilitado
    if ($rwconf['caixa_fechamentomanual'] == 1) {
        // fechamentos anteriores
        $q_anterior = "SELECT * from movimentacoes WHERE idfuncionariostatus = $idfuncionario AND datareferencia < '$hojef' ORDER BY id desc LIMIT 1";
        $e_anterior = mysql_query($q_anterior);
        $quant_resultados = mysql_num_rows($e_anterior);
        $buscaanterior = mysql_fetch_array($e_anterior, MYSQL_ASSOC);
        $motivo = $buscaanterior['motivo'];

        $fechamento = strpos($motivo, "Fechamento");

        if ($quant_resultados > 0 && ($fechamento === false)) {
            return [
                'situacao' => "aberto",
                'mensagem' => "Você possui movimentações em datas anteriores que estão pendentes de fechamento."
            ];
        }

        $dataref = '';
        if ($quant_resultados > 0 && ($fechamento !== false)) {
            $dataref .= " AND datarecebido > '{$buscaanterior['datareferencia']}' ";
        }

        // verifica se o funcionario fez algum recebimento
        $q_receb = "SELECT *
                    FROM alunos_fichasrecebidas af
                    JOIN contasbanco cb ON cb.id = af.idcontasbanco
                    WHERE af.idfuncionario = $idfuncionario
                    AND af.datarecebido < '$hojef' $dataref
                    AND cb.tipo = 1";
        $e_receb = mysql_query($q_receb);
        $quant_receb = mysql_num_rows($e_receb);

        if ($quant_receb > 0) {
            return [
                'situacao' => "aberto",
                'mensagem' => "Você possui recebimentos em datas anteriores que estão pendentes de fechamento."
            ];
        }
    }

    return 0;
}

function getUnidade($id)
{
    $q = "SELECT unidade FROM unidades WHERE id = " . $id;
    $e = mysql_query($q);
    $r = mysql_fetch_array($e, MYSQL_ASSOC);
    return $r['unidade'];
}
