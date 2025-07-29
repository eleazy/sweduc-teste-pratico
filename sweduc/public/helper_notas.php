<?php

/**
 * @deprecated v6.x Retorna valor estático
 * @return 'alunos_notas'
 */
function bancoNotas($idanoletivo, $idgrade = null, $turmaaluno = false)
{
    return 'alunos_notas';
}

function getAnoLetivo($idanoletivo)
{
    $queryal = "SELECT anoletivo FROM anoletivo WHERE id=" . $idanoletivo;
    $resultal = mysql_query($queryal);
    $rowal = mysql_fetch_array($resultal, MYSQL_ASSOC);
    $anoletivo = trim($rowal['anoletivo']);

    return $anoletivo;
}

function getIdAnoLetivo($anoletivo)
{
    $queryal = "SELECT id,anoletivo FROM anoletivo WHERE anoletivo=" . $anoletivo;
    $resultal = mysql_query($queryal);
    $rowal = mysql_fetch_array($resultal, MYSQL_ASSOC);
    $anoletivo = trim($rowal['id']);

    return $anoletivo;
}

function date2br($dt)
{
    $d = explode('-', $dt);
    return $d[2] . '/' . $d[1] . '/' . $d[0];
}

function arredonda05($num)
{
    $inteiro = floor($num);
    $dec = number_format($num - $inteiro, 1);

    if ($dec < (0.3)) {
        return $inteiro;
    }
    if ($dec >= (0.3) && $dec < (0.8)) {
        return $inteiro += (0.5);
    }
    if ($dec >= (0.8)) {
        return $inteiro += 1;
    }

    return $inteiro;
}

function contarNaoNulos()
{
    $args = func_get_args();
    $args = is_array($args[0]) ? $args[0] : $args;
    $count = 0;
    foreach ($args as $i) {
        if ($i > 0) {
            $count++;
        }
    }

    return $count;
}

function mediaNaoNulos()
{
    $args = func_get_args();
    return (array_sum($args) / contarNaoNulos($args));
}

function getTipoPeriodo($idcurso)
{
    $queryal = "SELECT tipoperiodo FROM cursos WHERE id=" . $idcurso;
    $resultal = mysql_query($queryal);
    $rowal = mysql_fetch_array($resultal, MYSQL_ASSOC);
    return trim($rowal['tipoperiodo']);
}

function getTipoPeriodoPorTurma($idturma)
{
    $queryal = "SELECT cursos.tipoperiodo FROM grade inner join series on series.id=grade.idserie inner join cursos on cursos.id=series.idcurso where grade.idturma=" . $idturma . "  group by grade.idturma limit 1";
    $resultal = mysql_query($queryal);
    $rowal = mysql_fetch_array($resultal, MYSQL_ASSOC);
    return trim($rowal['tipoperiodo']);
}

function getTipoPeriodoPorGrade($idgrade)
{
    $queryal = "SELECT cursos.tipoperiodo FROM grade inner join series on series.id=grade.idserie inner join cursos on cursos.id=series.idcurso where grade.id IN (" . $idgrade . ")  group by grade.idturma limit 1";
    $resultal = mysql_query($queryal);
    $rowal = mysql_fetch_array($resultal, MYSQL_ASSOC);
    return trim($rowal['tipoperiodo']);
}

function getAnoMudanca($cliente)
{
    $alfa = strpos($cliente, "alfacem");

    return ($alfa === false) ? 2018 : 2017;
}

function situacaoFinal(
    $disciplinas_reprovado,
    $disciplinas_recuperação,
    $disciplinas_aprovado,
    $disciplinas_em_prova_final,
    $limite_de_recuperacoes,
    $possui_provafinal,
    $limite_dependencias,
    $limite_provasfinais
) {
    $status = "INDEFINIDO";
    $ultrapassa_limite_provas_finais = $limite_provasfinais < $disciplinas_em_prova_final;
    $ultrapassa_limite_recuperacoes  = $limite_de_recuperacoes < $disciplinas_recuperação;
    $ultrapassa_limite_dependencias  = $limite_dependencias < $disciplinas_reprovado + $disciplinas_recuperação;

    /**
     * Tem prova final se diferencia de possui prova final pois o primeiro se trata
     * de disciplinas em prova final e o segundo sobre ter a avaliação do tipo prova final
     */
    $tem_prova_final = $disciplinas_em_prova_final > 0;
    $tem_recuperacao = $disciplinas_recuperação > 0;
    $tem_reprovacao  = $disciplinas_reprovado > 0;
    $tem_aprovacao   = $disciplinas_aprovado > 0;

    if (
        $possui_provafinal && $tem_prova_final && !$ultrapassa_limite_provas_finais
    ) {
        $status = "PROVA FINAL";
    } elseif ($tem_recuperacao && !$ultrapassa_limite_recuperacoes) {
        $status = "RECUPERAÇÃO";
    } elseif (
        ($tem_reprovacao || $tem_recuperacao) && $ultrapassa_limite_dependencias
        || $ultrapassa_limite_provas_finais
    ) {
        $status = "REPROVADO";
    } elseif (
        ($tem_reprovacao || $tem_recuperacao)
        && $tem_aprovacao
        && !$ultrapassa_limite_dependencias
    ) {
        $status = "APROVADO COM DEPENDENCIAS";
    } elseif ($tem_aprovacao) {
        $status = "APROVADO";
    }

    return $status;
}


/**
 * Retorna array de mapeamento de notas diversificadas
 *
 * @return Array resultado da query de notas diversificadas
 */
function getNotasDiversificadas()
{
    $rows = [];

    $query1 = "SELECT * FROM boletim_diversificada";
    $result1 = mysql_query($query1);

    while ($row = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        $rows[] = $row;
    }

    return $rows;
}

/**
 * Retorna nota diversificada convertida
 *
 * @param nota Nota a ser convertida
 * @param notasDiversificadas Array de notas diversificadas resultantes do getNotasDiversificadas
 */
function notasDiversificada($nota, $notasDiversificadas = null)
{
    if (empty($notasDiversificadas)) {
        $query1 = "SELECT * FROM boletim_diversificada where nota = " . $nota;
        $result1 = mysql_query($query1);
        $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
    } else {
        $row1 = array_pop(
            array_filter($notasDiversificadas, fn($diversificada) => $diversificada['nota'] == $nota)
        );
    }

    return $row1['diversificada'];
}

if (!function_exists('getPeriodos')) {
    function getPeriodos($ano_letivo, $mudancaperiodo, $trimestre = false): array
    {
        if (empty($ano_letivo) || empty($mudancaperiodo)) {
            return null;
        }

        $indice_boletim = ($trimestre && $ano_letivo > $mudancaperiodo) ?
            " colunaboletim between 41 and 100 " :
            " colunaboletim between 1 and 40 ";

        global $_GLOBAL;

        if (!isset($_GLOBAL['query_periodos'][$indice_boletim])) {
            $query = "SELECT * FROM
                    periodos
                WHERE MONTH(datade)<='13'
                AND $indice_boletim
                AND (
                    periodo LIKE '1%' OR
                    periodo LIKE '2%' OR
                    periodo LIKE '3%' OR
                    periodo LIKE '4%' OR
                    provafinal = 1
                )
                ORDER BY colunaboletim ASC";
            $result = mysql_query($query);
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $_GLOBAL['query_periodos'][$indice_boletim][] = $row;
            }
        }

        // Sem registros
        if (empty($_GLOBAL['query_periodos'][$indice_boletim])) {
            return null;
        }

        return $_GLOBAL['query_periodos'][$indice_boletim];
    }
}

if (!function_exists('getFormula')) {
    function getFormula($anoLetivoId, $turmaId, $periodoId, $disciplinaId, $debug = false)
    {
        global $_GLOBAL;

        if (!isset($_GLOBAL['query_formula'][$anoLetivoId][$turmaId])) {
            $query2 = "SELECT
                formula,
                medias.id as mid,
                medias.id as idmedia,
                medias.idgrade as idgrade,
                idperiodo,
                iddisciplina
            FROM medias,grade
            WHERE medias.idgrade = grade.id
            AND grade.idanoletivo = $anoLetivoId
            AND grade.idturma = $turmaId";
            $result2 = mysql_query($query2);
            while ($row = mysql_fetch_array($result2, MYSQL_ASSOC)) {
                $_GLOBAL['query_formula'][$anoLetivoId][$turmaId][] = $row;
            }

            if ($debug) {
                echo $query2 . " ][ " . $result2 . "]<br />";
            }
        }

        $row2 = array_filter($_GLOBAL['query_formula'][$anoLetivoId][$turmaId], fn($item) => $item['idperiodo'] == $periodoId && $item['iddisciplina'] == $disciplinaId);

        return array_pop($row2);
    }
}

if (!function_exists('getAvaliacaoCompleta')) {
    function getAvaliacaoCompleta($avaliacaoId, $mediaId, $alunoId)
    {
        global $_GLOBAL;
        $alunosnotas = bancoNotas(null);

        if (!isset($_GLOBAL['query_avaliacao_completa'][$alunoId])) {
            $queryAVA = "SELECT
                nota, avaliacao, disciplina, idmedia, idavaliacao
                FROM medias, grade, disciplinas, $alunosnotas an, avaliacoes
                WHERE an.idmedia=medias.id
                AND medias.idgrade=grade.id
                AND grade.iddisciplina=disciplinas.id
                AND an.idavaliacao=avaliacoes.id
                AND idaluno='$alunoId'";

            $resultAVA = mysql_query($queryAVA);
            while ($rowAVA = mysql_fetch_array($resultAVA, MYSQL_ASSOC)) {
                $_GLOBAL['query_avaliacao_completa'][$alunoId][] = $rowAVA;
            }
        }

        // Sem registros
        if ($_GLOBAL['query_avaliacao_completa'][$alunoId] == null) {
            return null;
        }

        $rowAVA = array_filter($_GLOBAL['query_avaliacao_completa'][$alunoId], fn($item) => $item['idmedia'] == $mediaId && $item['idavaliacao'] == $avaliacaoId);

        return array_pop($rowAVA);
    }
}

if (!function_exists('getNotaAvaliacao')) {
    function getNotaAvaliacao($avaliacaoId, $mediaId, $alunoId)
    {
        if (!$avaliacaoId) {
            return null;
        }

        global $_GLOBAL;

        if (!isset($_GLOBAL['query_notas_avaliacoes'][$alunoId])) {
            $queryAVA = "SELECT
                    nota,
                    avaliacao,
                    idavaliacao,
                    idmedia
                FROM alunos_notas an
                JOIN avaliacoes ON an.idavaliacao=avaliacoes.id
                AND idaluno=$alunoId";

            $resultAVA = mysql_query($queryAVA);
            while ($rowAVA = mysql_fetch_array($resultAVA, MYSQL_ASSOC)) {
                $_GLOBAL['query_notas_avaliacoes'][$alunoId][] = $rowAVA;
            }
        }

        // Sem registros
        if (empty($_GLOBAL['query_notas_avaliacoes'][$alunoId])) {
            return null;
        }

        $rowAVA = array_filter($_GLOBAL['query_notas_avaliacoes'][$alunoId], fn($item) => $item['idmedia'] == $mediaId && $item['idavaliacao'] == $avaliacaoId);

        return array_pop($rowAVA);
    }
}
