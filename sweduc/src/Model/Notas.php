<?php

namespace App\Model;

use PDO;

/**
 * @deprecated
 */
class Notas
{
    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
    public function buscarNotasAluno($id, $tabelanotas = null)
    {
        $STH = $this->conn->prepare("SELECT * FROM alunos_notas WHERE id=:id");
        $STH->bindParam(':id', $id);
        $STH->execute();
        $results = $STH->fetch(PDO::FETCH_ASSOC);
        return $results;
    }
    public function buscarNotasAvaliacao($idavaliacao, $idmedia, $idaluno, $tabelanotas = null)
    {

        $query = "SELECT
                        nota, avaliacao, disciplina
                    FROM
                        medias m
                            INNER JOIN
                        grade g ON m.idgrade = g.id
                            INNER JOIN
                        disciplinas d ON g.iddisciplina = d.id
                            INNER JOIN
                        alunos_notas an ON an.idmedia = m.id
                            INNER JOIN
                        avaliacoes a ON an.idavaliacao = a.id
                    WHERE
                        idavaliacao = :idavaliacao AND idmedia = :idmedia
                            AND idaluno = :idaluno";

        $STH = $this->conn->prepare($query);
        $STH->bindParam(':idavaliacao', $idavaliacao);
        $STH->bindParam(':idmedia', $idmedia);
        $STH->bindParam(':idaluno', $idaluno);
        $STH->execute();
        $results = $STH->fetch(PDO::FETCH_ASSOC);
        return $results;
    }
}
