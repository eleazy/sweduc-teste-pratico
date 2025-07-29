<?php

function trabalhoCasaQuantDisciplina($disciplina, $idturma, $idanoletivo, $idperiodo)
{
    $subDisciplina = null;
    $rowT = null;
    $idDisciplina = $disciplina['id'];
    if ($subDisciplina == 0) {
         $sql = "";
    } else {
         $idDisciplina = $idDisciplina . ',' . $subDisciplina;
    }


    $sql = "SELECT
                COUNT(pp.id) quant
            FROM
                planejamentopedagogico pp
                    INNER JOIN
                disciplinas d ON pp.iddisciplina = d.id
                    INNER JOIN
                periodos p ON DATE_FORMAT(pp.periodode, '%m-%d') BETWEEN DATE_FORMAT(p.datade, '%m-%d') AND DATE_FORMAT(p.dataate, '%m-%d')
            WHERE
                d.id in (" . $idDisciplina . ") AND
                idturma = " . $idturma . " AND
                 DATE_FORMAT(pp.periodode, '%Y') = (SELECT
                                                        anoletivo
                                                    FROM
                                                        anoletivo
                                                    WHERE
                                                        id =  " . $idanoletivo . " ) AND
                p.id = " . $idperiodo . "
                and pp.trabalhocasa <> ''
            GROUP BY d.id
            ORDER BY d.numordem ASC";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowT = $row;
    }


    return $rowT;
}
function trabalhoAulaQuantDisciplina($disciplina, $idturma, $idanoletivo, $idperiodo)
{
    $subDisciplina = null;
    $rowT = null;
    $idDisciplina = $disciplina['id'];
    if ($subDisciplina == 0) {
         $sql = "";
    } else {
         $idDisciplina = $idDisciplina . ',' . $subDisciplina;
    }


    $sql = "SELECT
                COUNT(pp.id) quant
            FROM
                planejamentopedagogico pp
                    INNER JOIN
                disciplinas d ON pp.iddisciplina = d.id
                    INNER JOIN
                periodos p ON DATE_FORMAT(pp.periodode, '%m-%d') BETWEEN DATE_FORMAT(p.datade, '%m-%d') AND DATE_FORMAT(p.dataate, '%m-%d')
            WHERE
                d.id in (" . $idDisciplina . ") AND
                idturma = " . $idturma . " AND
                 DATE_FORMAT(pp.periodode, '%Y') = (SELECT
                                                        anoletivo
                                                    FROM
                                                        anoletivo
                                                    WHERE
                                                        id =  " . $idanoletivo . " ) AND
                p.id = " . $idperiodo . "
                and pp.trabalhoaula <> ''
            GROUP BY d.id
            ORDER BY d.numordem ASC";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowT = $row;
    }


    return $rowT;
}
function trabalhoCasaQuantDisciplina2($disciplina, $idturma, $idanoletivo, $idperiodo)
{
    $subDisciplina = null;
    $rowT = null;
    $idDisciplina = $disciplina;
    if ($subDisciplina == 0) {
         $sql = "";
    } else {
         $idDisciplina = $idDisciplina . ',' . $subDisciplina;
    }


    $sql = "SELECT
                COUNT(pp.id) quant
            FROM
                planejamentopedagogico pp
                    INNER JOIN
                disciplinas d ON pp.iddisciplina = d.id
                    INNER JOIN
                periodos p ON DATE_FORMAT(pp.periodode, '%m-%d') BETWEEN DATE_FORMAT(p.datade, '%m-%d') AND DATE_FORMAT(p.dataate, '%m-%d')
            WHERE
                d.id in (" . $idDisciplina . ") AND
                idturma = " . $idturma . " AND
                 DATE_FORMAT(pp.periodode, '%Y') = (SELECT
                                                        anoletivo
                                                    FROM
                                                        anoletivo
                                                    WHERE
                                                        id =  " . $idanoletivo . " ) AND
                p.id = " . $idperiodo . "
                and pp.trabalhocasa <> ''
            GROUP BY d.id
            ORDER BY d.numordem ASC";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowT = $row;
    }


    return $rowT['quant'];
}
