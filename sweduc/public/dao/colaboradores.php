<?php

function getColaboradores($unidades)
{
    $colaboradores = [];
    $querycolab = "SELECT f.id,p.nome,d.departamento, unidade FROM funcionarios f
              INNER JOIN pessoas p ON f.idpessoa=p.id
              INNER JOIN departamentos d ON f.iddepartamento=d.id
              LEFT JOIN unidades ON f.idunidade = unidades.id " .
              (($unidades == '0') ? '' : "WHERE f.idunidade IN ($unidades) ")
              . "ORDER BY unidades.id, p.nome ASC";
    $resultcolab = mysql_query($querycolab);

    while ($rowcolab = mysql_fetch_array($resultcolab, MYSQL_ASSOC)) {
        $colaboradores[] = $rowcolab;
    }

    return $colaboradores;
}
