<?php

use App\Academico\Model\Media;
use App\Academico\CalculoMediaService;

require __DIR__ . '/../helper_notas.php';

function mediaAluno($idperiodo, $idanoletivo, $idturma, $iddisciplina, $idaluno)
{
    $rowMedia = [];
    global $notasdecimais;
    $q = "SELECT periodo FROM periodos where id = " . $idperiodo;
    $result = mysql_query($q);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowperiodo = $row;
    }

    $q = "SELECT
                *
            FROM
                cache_boletim
            where
                idturma = " . $idturma . "  and
                idaluno = " . $idaluno[0]['id'] . " and
                iddisciplina = " . $iddisciplina;

    $result = mysql_query($q);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowMedia = $row;
    }

    $rowMedia = unserialize($rowMedia['notas']);
    $mediaFinal = $rowMedia[$idperiodo][$iddisciplina]['BoletimSimples'][0]['notafinal'];

    return number_format($mediaFinal, $notasdecimais);
}

function mediaTurma($idperiodo, $idanoletivo, $idturma, $iddisciplina)
{
    $alunosQuant = null;
    global $notasdecimais;
    $medias = [];

    $sql = "SELECT
                group_concat(idaluno) alunos, count(idaluno) quant
            FROM
                alunos_matriculas am
                    INNER JOIN
                alunos a ON am.idaluno = a.id
                    INNER JOIN
                pessoas p ON a.idpessoa = p.id
            WHERE
                am.turmamatricula = " . $idturma .
            " AND am.anoletivomatricula IN (" . $idanoletivo . ") and
                am.status = 1
            group by am.turmamatricula";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $alunosQuant = $row['quant'];
        $alunos = $row['alunos'];
    }

    $sqlFormula = "SELECT
                    m.id,
                    m.formula,
                    idcurso
                FROM
                    medias m
                        INNER JOIN
                    grade g ON m.idgrade = g.id
                        JOIN
                    series ON idserie=series.id
                WHERE
                    m.idperiodo = " . $idperiodo . " AND
                    g.idanoletivo = " . $idanoletivo . " AND
                    g.idturma = " . $idturma . " AND
                    g.iddisciplina = " . $iddisciplina;
    $resulFormula = mysql_query($sqlFormula);
    $rowFormula = mysql_fetch_array($resulFormula, MYSQL_ASSOC);
    $mediaId = $rowFormula['id'];

    $calculaMedia = new CalculoMediaService($notasdecimais);
    foreach (explode(',', $alunos) as $idaluno) {
        $medias[] = (float) $calculaMedia->calcularMedia((int) $idaluno, Media::find($mediaId));
    }

    $media = number_format(array_sum($medias) / $alunosQuant, $notasdecimais);
    return $media;
}

function tabelaNotas($turmaaluno, $anoletivo)
{
    return 'alunos_notas';
}
