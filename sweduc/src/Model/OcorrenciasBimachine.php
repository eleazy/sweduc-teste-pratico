<?php

namespace App\Model;

use App\Model\DbConnect;
use PDO;

/**
 * @deprecated
 */
class OcorrenciasBimachine extends Model
{
    public static function getOcorrencias()
    {
        $db = new DbConnect();
        $conn = $db->connect();

         $query = "SELECT
         U.unidade,
         A.numeroaluno,
         AM.nummatricula,
         P.nome,
         ANO.anoletivo,
         C.curso,
         S.serie,
         T.turma,
         D.abreviacao,
         US.login,
         AO.assunto,
         AO.id AS idCount,
         IF(AO.datahora = '0000-00-00' OR AO.datahora < '0001-01-30' OR AO.datahora > '2100-01-01' OR AO.datahora = null, '2000-01-01', AO.datahora) AS datahora
         FROM alunos A
         LEFT JOIN alunos_matriculas AM ON (A.id = AM.idaluno)
         LEFT JOIN pessoas P ON (A.idpessoa = P.id)
         LEFT JOIN anoletivo ANO ON (AM.anoletivomatricula = ANO.id)
         LEFT JOIN turmas T ON (AM.turmamatricula = T.id)
         LEFT JOIN unidades U ON (U.id = AM.idunidade)
         LEFT JOIN series S ON (T.idserie = S.id)
         LEFT JOIN cursos C ON (S.idcurso = C.id)
         RIGHT JOIN alunos_ocorrencias AO ON (AM.idaluno = AO.idaluno)
         LEFT JOIN disciplinas D ON (AO.iddisciplina = D.id)
         LEFT JOIN usuarios US ON (A.idpessoa = US.idpessoa)
         WHERE AO.datahora > '2022-01-01'
         GROUP BY AO.id
         ORDER BY AO.datahora DESC";

        $STH = $conn->prepare($query);
        $STH->execute();
        $results = $STH->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
