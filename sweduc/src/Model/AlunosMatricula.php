<?php

namespace App\Model;

use PDO;

/**
 * @deprecated
 */
class AlunosMatricula
{
    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function buscarAlunoMatricula($idaluno = '', $nummatricula = '', $idunidade = '')
    {
        $query = "SELECT * FROM alunos_matriculas";
        $qwhere = '';
        $param = [];


        if ($idaluno != '') {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' idaluno= :idaluno';
            $param[':idaluno'] =  $idaluno;
        }

        if ($nummatricula != '') {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' nummatricula= :nummatricula';
            $param[':nummatricula'] =  $nummatricula;
        }

        if ($idunidade != '') {
            $qwhere .= $qwhere != '' ? ' and ' : ' where ';
            $qwhere .= ' idunidade= :idunidade';
            $param[':idunidade'] =  $idunidade;
        }

        $STH = $this->conn->prepare($query . $qwhere);
        $STH->execute($param);
        $results = $STH->fetch(PDO::FETCH_ASSOC);
        return $results;
    }
}
