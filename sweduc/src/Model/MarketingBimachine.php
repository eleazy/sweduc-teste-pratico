<?php

namespace App\Model;

use App\Model\DbConnect;
use PDO;

/**
 * @deprecated
 */
class MarketingBimachine extends Model
{
    public static function getMarketing()
    {
        $db = new DbConnect();
        $conn = $db->connect();

         $query = "SELECT
         U.unidade,
         PF.responsavel_nome,
         PA.nome,
         C.curso,
         S.serie,
         PA.ano_interesse,
         IF(PF.ativo = 0, 'Inativo', 'Ativo') AS status,
         M.midia,
         PA.id AS idCount,
         IF(PC.data = '0000-00-00' OR PC.data < '0001-01-30' OR PC.data = null, '2000-01-01', PC.data) AS data,
         PF.responsavel_email,
         CONCAT(PF.responsavel_telefone, ', ', PF.responsavel_celular) AS telefones
         FROM prospeccao_alunos PA
         LEFT JOIN prospeccao_fichas PF ON (PA.id_prospeccao_ficha = PF.id)
         LEFT JOIN prospeccao_crm PC ON (PF.id = PC.id_prospeccao_ficha)
         LEFT JOIN cursos C ON (PA.id_curso = C.id)
         LEFT JOIN series S ON (PA.id_serie = S.id)
         LEFT JOIN midias M ON (PF.id_midia = M.id)
         LEFT JOIN unidades U ON (PA.id_unidade = U.id)
         GROUP BY PF.id
         ORDER BY PC.data DESC";

        $STH = $conn->prepare($query);
        $STH->execute();
        $results = $STH->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
