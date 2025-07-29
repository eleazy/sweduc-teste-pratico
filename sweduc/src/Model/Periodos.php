<?php

namespace App\Model;

use PDO;

/**
 * @deprecated
 */
class Periodos
{
    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function buscarPeriodosMes($mes = 13, $coluna_boletim = false)
    {
        $colunas = (!$coluna_boletim) ? " colunaboletim>0 " : $coluna_boletim;
        $STH = $this->conn->prepare("SELECT * FROM periodos WHERE MONTH(datade)<=:mes AND " . $colunas . " ORDER BY colunaboletim ASC");
        $STH->bindParam(':mes', $mes);
        $STH->execute();
        $results = $STH->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function buscarPeriodosPorCurso($curso, $coluna_boletim = false)
    {
        $colunas = (!$coluna_boletim) ? " colunaboletim>0 " : $coluna_boletim;
        $STH = $this->conn->prepare(
            "SELECT * FROM (
                SELECT * FROM periodos WHERE NOT EXISTS (SELECT 1 FROM cursos_periodos WHERE curso_id=:curso_id)
                    UNION
                SELECT
                    periodos.id,
                    cp.ordenacao as colunaboletim,
                    cp.situacaofinalanual,
                    cp.recuperacao,
                    cp.provafinal,
                    cp.mediaanual,
                    cp.conta_faltas,
                    periodos.periodo,
                    DATE_FORMAT(inicio_em, '0000-%m-%d') as datade,
                    DATE_FORMAT(termino_em, '0000-%m-%d') as dataate
                FROM cursos_periodos cp JOIN periodos ON cp.periodo_id = periodos.id
                WHERE curso_id=:curso_id
            ) as t WHERE $colunas ORDER BY colunaboletim ASC
        "
        );
        $STH->bindParam(':curso_id', $curso);
        $STH->execute();
        $results = $STH->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
