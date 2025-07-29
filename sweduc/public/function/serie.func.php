<?php

function serieTurma($idserie, $idturma)
{

    $rowTurma = [];
    $sqlDisciplinas = "SELECT distinct
                            t.*
                        FROM
                            series s
                                INNER JOIN
                            turmas t ON s.id = t.idserie
                        WHERE
                            s.id = " . $idserie;

    if ($idturma != '') {
        $sqlDisciplinas .= " and t.id = " . $idturma;
    }

    $resultDisciplinas = mysql_query($sqlDisciplinas);
    while ($row = mysql_fetch_array($resultDisciplinas, MYSQL_ASSOC)) {
        $rowTurma[] = $row;
    }

    return $rowTurma;
}

function serieDisciplina($idserie, $idanoletivo)
{

    $rowDisciplinas = [];
    $sqlDisciplinas = "SELECT 
                        d.id, d.disciplina, g.idanoletivo, g.idturma, d.numordem, (SELECT 
                                        GROUP_CONCAT(d2.id)
                                    FROM
                                        disciplinas d2
                                    WHERE
                                        d2.numordem IN ((d.numordem * -1))) subDisciplina
                    FROM
                        grade g
                            INNER JOIN
                        disciplinas d ON g.iddisciplina = d.id
                         INNER JOIN
                        series s ON g.idserie = s.id
                    WHERE
                     numordem < 100 AND 
                      s.id = " . $idserie . "
                            AND g.idanoletivo IN (" . $idanoletivo . ")

                    group by d.id
                    order by d.numordem asc";

    $resultDisciplinas = mysql_query($sqlDisciplinas);
    while ($row = mysql_fetch_array($resultDisciplinas, MYSQL_ASSOC)) {
        $rowDisciplinas[] = $row;
    }

    return $rowDisciplinas;
}

function buscarSerieCurso($idCurso)
{
    $rowSeries = [];
    $sql = "SELECT 
                *
            FROM
                series
            WHERE
                idcurso = " . $idCurso;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowSeries[] = $row;
    }

    if (!$rowSeries) {
        $rowSeries = [];
    }

    return $rowSeries;
}
