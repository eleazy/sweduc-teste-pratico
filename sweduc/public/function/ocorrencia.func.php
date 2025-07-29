<?php

function alunoOcorrenciaDisciplina($idAluno, $idDisciplina, $idperiodo, $datade, $dataate, $idanoletivo, $idOcorrencias = 0)
{
    $rowOcorrecias = null;
    if ($idOcorrencias == 0) {
         $sql = "";
    } else {
         $sql = " and    o.id in( " . $idOcorrencias . " )";
    }

    $sqlOcorrecias = "SELECT
                            COUNT(ao.id) quat
                        FROM
                            alunos_ocorrencias ao
                                INNER JOIN
                            periodos p ON DATE_FORMAT(ao.datahora, '%m-%d') BETWEEN DATE_FORMAT(p.datade, '%m-%d') AND DATE_FORMAT(p.dataate, '%m-%d')
                                INNER JOIN
                            ocorrencias o ON ao.idocorrencia = o.id
                        WHERE
                            ao.idaluno = " . $idAluno . " AND
                            ao.iddisciplina in(" . $idDisciplina . " )
                            AND p.id = " . $idperiodo . "
                        AND ao.datahora BETWEEN '" . $datade . " 00:00:00' AND '" . $dataate . " 23:59:59'
                           " . $sql . "
                            AND DATE_FORMAT(ao.datahora, '%Y') = (SELECT
                                                                anoletivo
                                                            FROM
                                anoletivo
                                                            WHERE
                                id =  " . $idanoletivo . ")
                        GROUP BY ao.iddisciplina";



    $resultOcorrecias = mysql_query($sqlOcorrecias);
    while ($row = mysql_fetch_array($resultOcorrecias, MYSQL_ASSOC)) {
        $rowOcorrecias = $row;
    }

    return $rowOcorrecias;
}

function turmaOcorrenciaDisciplina($idturma, $idDisciplina, $idperiodo, $datade, $dataate, $idanoletivo, $idOcorrencias = 0, $subDisciplina = 0)
{

    $rowOcorrecias = null;
    $sql = "";
    if ($idOcorrencias != 0) {
         $sql = "  and    o.id in( " . $idOcorrencias . ")  ";
    }

    if ($subDisciplina != 0) {
         $idDisciplina = $idDisciplina . ',' . $subDisciplina;
    }
    $sqlOcorrecias = "SELECT
                            COUNT(ao.id) quat
                        FROM
                            alunos_matriculas am
                                INNER JOIN
                            alunos a ON am.idaluno = a.id
                                INNER JOIN
                            alunos_ocorrencias ao ON a.id = ao.idaluno
                                INNER JOIN
                            periodos p ON DATE_FORMAT(ao.datahora, '%m-%d') BETWEEN DATE_FORMAT(p.datade, '%m-%d') AND DATE_FORMAT(p.dataate, '%m-%d')
                                INNER JOIN
                            ocorrencias o ON ao.idocorrencia = o.id
                        WHERE
                            am.turmamatricula = " . $idturma . " AND
                            ao.iddisciplina in ( " . $idDisciplina . " )
                            AND p.id = " . $idperiodo . "

                        AND ao.datahora BETWEEN '" . $datade . " 00:00:00' AND '" . $dataate . " 23:59:59'
                             " . $sql . "
                            AND DATE_FORMAT(ao.datahora, '%Y') = (SELECT
                                                                anoletivo
                                                            FROM
                                anoletivo
                                                            WHERE
                                id =  " . $idanoletivo . ")
                             and am.anoletivomatricula = " . $idanoletivo . "
                        GROUP BY am.turmamatricula";



    $resultOcorrecias = mysql_query($sqlOcorrecias);
    while ($row = mysql_fetch_array($resultOcorrecias, MYSQL_ASSOC)) {
        $rowOcorrecias = $row;
    }

    return $rowOcorrecias;
}

function alunoOcorrencia($idAluno, $idOcorrencias, $idperiodo, $idanoletivo, $datade, $dataate)
{
    $rowOcorrecias = null;
    $sqlOcorrecias = "SELECT
                        o.id idocorrencia, p.id idperiodo, COUNT(ao.id) quat
                    FROM
                        alunos_ocorrencias ao
                            INNER JOIN
                        periodos p ON DATE_FORMAT(ao.datahora, '%m-%d') BETWEEN DATE_FORMAT(p.datade, '%m-%d') AND DATE_FORMAT(p.dataate, '%m-%d')
                            INNER JOIN
                        ocorrencias o ON ao.idocorrencia = o.id
                    WHERE
                        ao.idaluno = " . $idAluno . " and
                        o.id = " . $idOcorrencias . " and
                        p.id = " . $idperiodo . " and
                        AND ao.datahora BETWEEN '" . $datade . " 00:00:00' AND '" . $dataate . " 23:59:59'
                        DATE_FORMAT(ao.datahora, '%Y') = (SELECT
                                                                anoletivo
                                                            FROM
                                anoletivo
                                                            WHERE
                                id =  " . $idanoletivo . ")
                    GROUP BY ao.idocorrencia";

    $resultOcorrecias = mysql_query($sqlOcorrecias);
    while ($row = mysql_fetch_array($resultOcorrecias, MYSQL_ASSOC)) {
        $rowOcorrecias = $row;
    }

    return $rowOcorrecias;
}

function turmaOcorrencia($idTurma, $idOcorrencias, $idperiodo, $idanoletivo, $datade, $dataate)
{
    $rowOcorrecias = null;
    $sqlOcorrecias = "SELECT
                            o.id idocorrencia, p.id idperiodo, COUNT(ao.id) quat
                        FROM
                            alunos_matriculas am
                                INNER JOIN
                            alunos a ON am.idaluno = a.id
                                INNER JOIN
                            alunos_ocorrencias ao ON a.id = ao.idaluno
                                INNER JOIN
                            periodos p ON DATE_FORMAT(ao.datahora, '%m-%d') BETWEEN DATE_FORMAT(p.datade, '%m-%d') AND DATE_FORMAT(p.dataate, '%m-%d')
                                INNER JOIN
                            ocorrencias o ON ao.idocorrencia = o.id
                        WHERE
                            am.turmamatricula = " . $idTurma . "
                            AND am.anoletivomatricula IN (" . $idanoletivo . ")
                            AND YEAR(ao.datahora) = (SELECT
                                anoletivo
                            FROM
                                anoletivo
                            WHERE
                                id = " . $idanoletivo . ") AND
                            o.id = " . $idOcorrencias . " and
                            p.id = " . $idperiodo . "
                            AND ao.datahora BETWEEN '" . $datade . " 00:00:00' AND '" . $dataate . " 23:59:59'

                        GROUP BY ao.idocorrencia";

    $resultOcorrecias = mysql_query($sqlOcorrecias);
    while ($row = mysql_fetch_array($resultOcorrecias, MYSQL_ASSOC)) {
        $rowOcorrecias = $row;
    }

    return $rowOcorrecias;
}


function buscarOcorreciaLista()
{
    $rowOcorrecias = [];
    $sqlOcorrecias = "SELECT
                            ol.id, ol.idtipolistagem, o.ocorrencia
                        FROM
                            ocorrencias_listagem ol
                                INNER JOIN
                            ocorrencias o ON ol.idocorrencia = o.id";

    $resultOcorrecias = mysql_query($sqlOcorrecias);
    while ($row = mysql_fetch_array($resultOcorrecias, MYSQL_ASSOC)) {
        $rowOcorrecias[] = $row;
    }

    return $rowOcorrecias;
}


function buscarOcorreciaDiario()
{
    $rowOcorrecias = [];
    $sqlOcorrecias = "SELECT
                            o.id, ol.idtipolistagem, o.ocorrencia
                        FROM
                            ocorrencias_listagem ol
                                INNER JOIN
                            ocorrencias o ON ol.idocorrencia = o.id
                        WHERE
                            ol.idtipolistagem = 3";

    $resultOcorrecias = mysql_query($sqlOcorrecias);
    while ($row = mysql_fetch_array($resultOcorrecias, MYSQL_ASSOC)) {
        $rowOcorrecias[] = $row;
    }

    return $rowOcorrecias;
}
