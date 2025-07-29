<?php

function buscarDisciplina($idCurso, $idaluno)
{
    $rowDisciplina = [];

    $sql = 'SELECT DISTINCT
                   d.abreviacao disciplina,  d.numordem numordem
                FROM
                    grade g
                        INNER JOIN
                    disciplinas d ON g.iddisciplina = d.id
                        INNER JOIN
                    series s ON g.idserie = s.id
                        INNER JOIN
                    cursos c ON s.idcurso = c.id
                WHERE
                    c.id = ' . $idCurso . ' AND d.numordem > 0
                        AND d.numordem < 100

                group by d.abreviacao
                ORDER BY d.numordem';

    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowDisciplina[] = $row['disciplina'];
    }

    $sql = 'SELECT
                    h.disciplina
                FROM
                    alunos_historico h
                WHERE
                    h.idaluno = ' . $idaluno . '
                        AND h.serie IN (SELECT
                            s.serie
                        FROM
                            cursos c
                                INNER JOIN
                            series s ON s.idcurso = c.id
                        WHERE
                            c.id = ' . $idCurso . ')
            ORDER BY h.ordem';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        if (!in_array($row['disciplina'], $rowDisciplina)) {
            $rowDisciplina[] = $row['disciplina'];
        }
    }

    return $rowDisciplina;
}

function buscarHistoricoNotaCh($idaluno, $disciplina, $serie)
{
    $rowHistorico = [];
    $sql = 'SELECT
                media,cargahoraria
            FROM
                alunos_historico
            WHERE
                idaluno = ' . $idaluno . '
                    AND disciplina   = "' . $disciplina . '"
                    AND serie = "' . $serie . '"' .
        'AND media IS NOT NULL';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowHistorico[] = $row;
    }

    return $rowHistorico;
}

function buscarHistoricoDadosEscola($idaluno, $serie)
{
    $rowHistorico = [];

    $sql = 'SELECT DISTINCT
                ano,
                situacao,
                escola,
                local,
                carga_horaria_total,
                frequencia
            FROM
                alunos_historico
            WHERE
                idaluno = ' . $idaluno . ' AND serie = "' . $serie . '"';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowHistorico[] = $row;
    }

    return $rowHistorico;
}

function buscarHistoricoSerie($serie, $idaluno)
{
    $rowHistorico = [];

    $sql = 'SELECT
                    GROUP_CONCAT(id) id
                FROM
                    alunos_historico
                where
                    serie="' . $serie . '"
                    and idaluno = ' . $idaluno;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowHistorico[] = $row;
    }

    return $rowHistorico;
}
