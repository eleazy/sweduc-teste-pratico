<?php

function notasM($formula2, $idFormula, $alunos)
{

    $matches = [];
    $matches[0] = " ";
    $pattern = "/#M([0-9]*)@/";
    preg_match_all($pattern, $formula2, $matches);

    $formula2 = fomulaM($matches, $alunos, $formula2, $idFormula);




    return $formula2;
}


function fomulaM($matches, $alunos, $formula2, $idFormula)
{




    $matchesM = [];
    if ((is_countable($matches[0]) ? count($matches[0]) : 0) > 0) {
        for ($i = 0; $i < (is_countable($matches[0]) ? count($matches[0]) : 0); $i++) {
            $idMat = $matches[1][$i];

            $queryIN = "SELECT formula FROM medias WHERE id='" . $matches[1][$i] . "'";
            $resultIN = mysql_query($queryIN);
            $rowIN = mysql_fetch_array($resultIN, MYSQL_ASSOC);



            if ($rowIN['formula']) {
                $formulaIN = "(" . $rowIN['formula'] . " )"; //$rowIN['formula']; // FORMULA DA MÉDIA INTERNA     number_format($rowAVA['nota'],1 )
            } else {
                $formulaIN = 0;
            }


            $formula2 = str_replace($matches[0][$i], $formulaIN, $formula2);

            $formula2 = fomulaA($formula2, $idFormula, $alunos);

            $matchesM[0] = " ";
            $patternM = "/#M([0-9]*)@/";
            preg_match_all($patternM, $formula2, $matchesM);

            if ((is_countable($matchesM[0]) ? count($matchesM[0]) : 0) > 0) {
                $formula2 = fomulaM($matchesM, $alunos, $formula2);
            }
            eval($formula2);
        }
    }

    return $formula2;
}


function fomulaA($formula2, $idFormula, $alunos)
{

    $matchesAVA = [];
    $matchesAVA[0] = " ";
    $pattern = "/#A([0-9]*)@/";
    preg_match_all($pattern, $formula2, $matchesAVA);

    for ($i = 0; $i < (is_countable($matchesAVA[0]) ? count($matchesAVA[0]) : 0); $i++) {
        $queryAVA = "SELECT 
                                        (sum(nota)) nota, avaliacao, idmedia
                                    FROM
                                        alunos_notas an
                                            INNER JOIN
                                        avaliacoes a ON an.idavaliacao = a.id
                                    WHERE
                                        idavaliacao = " . $matchesAVA[1][$i] . " 
                                            AND idmedia = " . $idFormula . " 
                                                and an.idaluno in (" . $alunos . ") ";

        $resultAVA = mysql_query($queryAVA);
        $rowAVA = mysql_fetch_array($resultAVA, MYSQL_ASSOC);


        if (trim($rowAVA['nota']) == "") {
            $notalida = "0";
        } else {
            $notalida = $rowAVA['nota'];
        }

        $formula2 = str_replace($matchesAVA[0][$i], $notalida, $formula2);
    }


    return $formula2;
}
