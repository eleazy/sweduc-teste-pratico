<?php

function buscarDepartamento()
{
    $rowDepartamento = [];
    $sql = 'SELECT * FROM departamentos d ORDER BY d.departamento ASC';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowDepartamento[] = $row;
    }

    return $rowDepartamento;
}
