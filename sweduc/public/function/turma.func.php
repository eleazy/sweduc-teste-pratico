<?php

function turmaDisciplina($turma, $idanoletivo)
{


    $rowDisciplinas = [];
    $sqlDisciplinas = "SELECT
                                d.id,
                                d.disciplina,
                                g.idanoletivo,
                                g.idturma,
                                d.numordem,
                                (SELECT
                                        GROUP_CONCAT(d2.id)
                                    FROM
                                        disciplinas d2
                                    WHERE
                                        d2.numordem IN ((d.numordem * -1))) subDisciplina,
                                        g.id idGrade

                            FROM
                                disciplinas d
                                    INNER JOIN
                                grade g ON g.iddisciplina = d.id
                                    INNER JOIN
                                turmas t ON g.idturma = t.id
                                    INNER JOIN
                                series s ON t.idserie = s.id
                                    INNER JOIN
                                cursos c ON s.idcurso = c.id
                                    INNER JOIN
                                unidades u ON c.idunidade = u.id
                                    INNER JOIN
                                anoletivo al ON g.idanoletivo = al.id
                            WHERE
                                al.id = " . $idanoletivo . " AND t.id = " . $turma . "
                            ORDER BY d.numordem ASC";

    $resultDisciplinas = mysql_query($sqlDisciplinas);
    while ($row = mysql_fetch_array($resultDisciplinas, MYSQL_ASSOC)) {
        $rowDisciplinas[] = $row;
    }


    return $rowDisciplinas;
}

function buscarTurmaId($idturma)
{
    $rowTurma = [];
    $temp = '';
    foreach ($idturma as $d) {
        $temp .= $d . ', ';
    }
    $temp = substr($temp, 0, -2);

    $sql = "SELECT
                            *
                        FROM
                            turmas t
                        WHERE
                            id IN (" . $temp . ")";

    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowTurma[] = $row;
    }

    return $rowTurma;
}
