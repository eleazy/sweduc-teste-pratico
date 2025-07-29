<?php

function buscarDisciplinaGrade($idGrade)
{
    $rowDisciplina = null;
    $sql = "SELECT DISTINCT
                d.*
            FROM
                grade g
                    INNER JOIN
                disciplinas d ON g.iddisciplina = d.id
            WHERE
                g.id IN ($idGrade)";
    $resul = mysql_query($sql);
    while ($row = mysql_fetch_array($resul, MYSQL_ASSOC)) {
        $rowDisciplina = $row;
    }

    return $rowDisciplina;
}

function buscarDisciplina($idDisciplina)
{

    $rowDisciplina = [];
    $temp = '';
    foreach ($idDisciplina as $d) {
        $temp .= $d . ', ';
    }
    $temp = substr($temp, 0, -2);


    $sql = "SELECT DISTINCT
                d.*
            FROM
               disciplinas d 
            WHERE
                d.id IN (" . $temp . ")";
    $resul = mysql_query($sql);

    while ($row = mysql_fetch_array($resul, MYSQL_ASSOC)) {
        $rowDisciplina[] = $row;
    }


    return $rowDisciplina;
}
