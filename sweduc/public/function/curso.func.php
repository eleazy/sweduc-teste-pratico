<?php

function buscarCursoHistorico()
{
    $rowHistorico = [];
    $sql = 'SELECT * FROM cursos where historico > 0 order by curso';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowHistorico[] = $row;
    }

    return $rowHistorico;
}
