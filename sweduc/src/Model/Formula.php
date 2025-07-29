<?php

namespace App\Model;

use PDO;

/**
 * @deprecated
 */
class Formula
{
    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function buscarFormulaDisciplina($idperiodo, $idanoletivo, $idturma, $iddisciplina)
    {
        $query = "SELECT
                        formula, m.id AS mid
                    FROM
                        medias m
                            INNER JOIN
                        grade g ON m.idgrade = g.id
                    WHERE
                        m.idperiodo = :idperiodo
                            AND g.idanoletivo = :idanoletivo
                            AND g.idturma = :idturma
                            AND g.iddisciplina = :iddisciplina";


        $STH = $this->conn->prepare($query);
        $STH->bindParam(':idperiodo', $idperiodo);
        $STH->bindParam(':idanoletivo', $idanoletivo);
        $STH->bindParam(':idturma', $idturma);
        $STH->bindParam(':iddisciplina', $iddisciplina);
        $STH->execute();
        $results = $STH->fetch(PDO::FETCH_ASSOC);
        return $results;
    }

    public function buscarFormula($media)
    {
        $query = "SELECT formula FROM medias WHERE id=:media";

        $STH = $this->conn->prepare($query);
        $STH->bindParam(':media', $media);
        $STH->execute();
        $results = $STH->fetch(PDO::FETCH_ASSOC);
        return $results;
    }
}
