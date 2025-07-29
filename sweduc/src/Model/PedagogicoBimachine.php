<?php

namespace App\Model;

use App\Model\DbConnect;
use PDO;

/**
 * @deprecated
 */
class PedagogicoBimachine extends Model
{
    public static function getPedagogico()
    {
        $results = [];
        $db = new DbConnect();
        $conn = $db->connect();

         $query = "SELECT
         U.unidade,
         C.curso,
         S.serie,
         T.turma,
         A.numeroaluno,
         ANO.anoletivo,
         AM.nummatricula,
         E.email,
         AM.status,
         D.abreviacao,
         AV.avaliacao,
         PE.periodo,
         AN.nota,
         AN.id AS idCount,
         IF(AN.datahora = '0000-00-00' OR AN.datahora < '0001-01-30' OR AN.datahora = null, '2000-01-01', AN.datahora) AS dataLancamento,
	     (CASE
         WHEN (PE.periodo LIKE '1º Trimestre') THEN CONCAT(ANO.anoletivo, '-', LPAD(FLOOR(3 + (RAND() * 2)), 2, 0), '-', LPAD(FLOOR(1 + (RAND() * 27)), 2, 0))
		 WHEN (PE.periodo LIKE '2º Trimestre') THEN CONCAT(ANO.anoletivo, '-', LPAD(FLOOR(6 + (RAND() * 2)), 2, 0), '-', LPAD(FLOOR(1 + (RAND() * 29)), 2, 0))
		 WHEN (PE.periodo LIKE '3º Trimestre') THEN CONCAT(ANO.anoletivo, '-', LPAD(FLOOR(10 + (RAND() * 2)), 2, 0), '-', LPAD(FLOOR(1 + (RAND() * 29)), 2, 0))
		 ELSE AN.datahora
		 END) AS data
		 FROM alunos A
         CROSS JOIN alunos_notas AN ON (A.id = AN.idaluno)
         INNER JOIN medias M ON (AN.idmedia = M.id)
         INNER JOIN periodos PE ON (M.idperiodo = PE.id)
         INNER JOIN grade G ON (M.idgrade = G.id)
         INNER JOIN anoletivo ANO ON (G.idanoletivo = ANO.id)
         INNER JOIN turmas T ON (G.idturma = T.id)
         INNER JOIN series S ON (G.idserie = S.id)
         INNER JOIN disciplinas D ON (G.iddisciplina = D.id)
         CROSS JOIN avaliacoes AV ON (AN.idavaliacao = AV.id)
         LEFT JOIN cursos AS C ON (S.idcurso = C.id)
         LEFT JOIN alunos_matriculas AM ON (A.id = AM.idaluno)
         LEFT JOIN pessoas P ON (A.idpessoa = P.id)
         LEFT JOIN unidades U ON (U.id = AM.idunidade)
         LEFT JOIN emails E ON (P.id = E.idpessoa)
         WHERE AN.datahora > '2022-01-01'
         AND AN.datahora < '2022-08-01'
         AND CONCAT('', AN.nota * 1) = AN.nota
         GROUP BY AN.id
         ORDER BY AN.created_at DESC";

        $STH = $conn->prepare($query);
        $STH->execute();
        while ($STH->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $STH->fetch(PDO::FETCH_ASSOC);
        }
        return $results;
    }
}
