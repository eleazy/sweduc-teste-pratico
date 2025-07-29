<?php

namespace App\Model;

use PDO;

/**
 * @deprecated
 */
class Serie
{
    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function buscarSerieParam($idgrade)
    {

         $query = "SELECT
                        dependencias,
                        mediaaprovacao,
                        mediaaprovacaorec,
                        mediaaprovacaopf,
                        pontosaprovacao,
                        mediarecuperacao
                    FROM
                        grade g
                            INNER JOIN
                        series s ON g.idserie = s.id
                    WHERE
                        g.id = :idgrade";


        $STH = $this->conn->prepare($query);
        $STH->bindParam(':idgrade', $idgrade);
        $STH->execute();
        $results = $STH->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
