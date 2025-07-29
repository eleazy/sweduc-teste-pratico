<?php

function buscaAviso()
{
    $rowAviso = [];
    $sql = 'SELECT * FROM aviso';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowAviso[] = $row;
    }

    return $rowAviso;
}

function buscaAvisoId($id)
{
    $rowAviso = [];
    $sql = 'SELECT * FROM aviso where id = ' . $id;

    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowAviso[] = $row;
    }

    return $rowAviso;
}

function buscaFuncionarioAviso($id)
{
    $rowAviso = [];
    $sql = '   SELECT
                    *
                FROM
                    funcionarios f
                WHERE
                    f.id IN (SELECT
                                id_funcionarios
                            FROM
                                aviso_funcionarios
                            WHERE
                                id_aviso =' . $id . ') ';

    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowAviso[] = $row;
    }

    return $rowAviso;
}
function buscaDepartamentoAviso($id)
{
    $rowAviso = [];
    $sql = 'SELECT distinct
                GROUP_CONCAT(DISTINCT d.departamento ORDER BY d.departamento ASC SEPARATOR \', \') departamento
            FROM
                funcionarios f
                inner join
                departamentos d on f.iddepartamento = d.id
            WHERE
                f.id IN (SELECT
                        id_funcionarios
                    FROM
                        aviso_funcionarios
                    WHERE
                        id_aviso = ' . $id . ') ';

    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowAviso[] = $row['departamento'];
    }

    return $rowAviso;
}

function buscaAssunto()
{
    $rowAssunto = [];
    $sql = 'SELECT * FROM aviso_assunto';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowAssunto[] = $row;
    }

    return $rowAssunto;
}

function buscaAssuntoId($id)
{
    $rowAssunto = [];
    $sql = 'SELECT * FROM aviso_assunto where id = ' . $id;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowAssunto[] = $row;
    }

    return $rowAssunto;
}

function buscaAssuntoExistente($id)
{
    $rowAssunto = [];
    $sql = 'SELECT
                aa.assunto, aa.id
            FROM
                aviso_funcionarios af
                    INNER JOIN
                aviso a ON af.id_aviso = a.id
                    INNER JOIN
                aviso_assunto aa ON a.assunto = aa.id
            WHERE
                af.id_funcionarios = ' . $id;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowAssunto[] = $row;
    }

    return $rowAssunto;
}
