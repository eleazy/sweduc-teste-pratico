<?php

namespace App\Model;

use PDO;

/**
 * @deprecated
 */
class Grade
{
    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function buscargradeDisciplina($idanoletivo, $idturma, $minOrdem = 0, $maxOrdem = '', $basenacional = '')
    {

         $query = "SELECT
                        g.iddisciplina AS gid,
                        g.id AS grid,
                        disciplina,
                        abreviacao,
                        cargahoraria,
                        descricao
                    FROM
                        grade g
                        INNER JOIN
                        disciplinas d on g.iddisciplina = d.id ";

        $qwhere = '';
        $param = [];

        if ($idanoletivo != '') {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' g.idanoletivo = :idanoletivo ';
            $param[':idanoletivo'] =  $idanoletivo;
        }

        if ($idturma != '') {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' g.idturma = :idturma ';
            $param[':idturma'] =  $idturma;
        }

        if (isset($minOrdem)) {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' numordem > :minOrdem ';
            $param[':minOrdem'] =  $minOrdem;
        }

        if ($maxOrdem != '') {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' numordem < :maxOrdem ';
            $param[':maxOrdem'] =  $maxOrdem;
        }
        if ($basenacional != '') {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' basenacional = :basenacional ';
            $param[':basenacional'] =  $basenacional;
        }
        $query .= $qwhere;
        $query .= " GROUP BY g.iddisciplina
                    ORDER BY d.numordem ASC";

        $STH = $this->conn->prepare($query);
        $STH->execute($param);
        $results = $STH->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
