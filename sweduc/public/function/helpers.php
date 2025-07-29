<?php

// include ('dao/conectar.php');

function salvaAvaliacoesFormulas($formula, $idmedia = 0, $update = 0)
{

    $idgrade = null;
    $idperiodo = null;
    $matchesAVA = [];
    $opts = [];
    if ($idmedia > 0) {
        $qm = "SELECT id,idgrade,idperiodo,formula FROM medias WHERE id=$idmedia";
        $rm = mysql_query($qm);
        $rowm = mysql_fetch_array($rm, MYSQL_ASSOC);
        $idgrade = $rowm['idgrade'];
        $idperiodo = $rowm['idperiodo'];
    }

    if ($update > 0) {
        limpaDM($idgrade, $idperiodo);
    }


    // comentado abaixop para não pegar avaliações a partir de médias medias
    // $pattern = "/#M([0-9]*)@/";
    //    $matches[0] = " ";
    //    while ($matches[0] != null) {
    //        preg_match($pattern, $formula, $matches);
    //        $queryIN = "SELECT formula FROM medias WHERE id=" . $matches[1];
    //        $resultIN = mysql_query($queryIN);
    //        $rowIN = mysql_fetch_array($resultIN, MYSQL_ASSOC);

    //        $formulaIN = $rowIN['formula']; // FORMULA DA MÉDIA INTERNA

    //        $patternAVA = "/#A([0-9]*)@/";
    //        $matchesAVA[0] = " ";
    //        while ($matchesAVA[0] != null) {
    //            preg_match($patternAVA, $formulaIN, $matchesAVA);
    //            $queryAVA = "SELECT nota FROM alunos_notas WHERE idavaliacao=" . $matchesAVA[1] . " AND idmedia=" . $matches[1];
    //            $resultAVA = mysql_query($queryAVA);
    //            $rowAVA = mysql_fetch_array($resultAVA, MYSQL_ASSOC);


    //            insereDM($idgrade,$idperiodo,$matchesAVA[1]);


    //            $formulaIN = str_replace($matchesAVA[0], $rowAVA['nota'], $formulaIN);
    //        }

    //        $formula = str_replace($matches[0], $formulaIN, $formula);
    //    }

    $patternAVA = "/#A([0-9]*)@/";
    $matchesAVA[0] = " ";
    $opts[] = "";
    while ($matchesAVA[0] != null) {
        preg_match($patternAVA, $formula, $matchesAVA);
        $queryAVA = "SELECT avaliacao FROM avaliacoes WHERE id=" . $matchesAVA[1];
        if (empty($matchesAVA[1])) {
            break;
        }
        $resultAVA = mysql_query($queryAVA);
        $rowAVA = mysql_fetch_array($resultAVA, MYSQL_ASSOC);
        $formula = str_replace($matchesAVA[0], $rowAVA['avaliacao'], $formula);


        insereDM($idgrade, $idperiodo, $matchesAVA[1]);

        // if (trim($rowAVA['avaliacao'])) {
        //     $opts[$matchesAVA[1]] = '<option value="' . $matchesAVA[1] . '">' . $rowAVA['avaliacao'] . '</option>';
        //     $ids[] = $matchesAVA[1];
        // }
    }
}

function checkDM($grade, $periodo, $avaliacao)
{

    $sql = "SELECT id FROM disciplinas_avaliacoes WHERE idgrade = $grade AND idperiodo = $periodo AND idavaliacao = $avaliacao";
    $resultado = mysql_query($sql);
    $row = mysql_fetch_array($resultado, MYSQL_ASSOC);
    $did = $row['id'];

    return (!empty($did)) ? $did : false;
}

function limpaDM($grade, $periodo)
{
    $sql = "DELETE FROM disciplinas_avaliacoes WHERE idgrade = $grade AND idperiodo = $periodo";
    $resultado = mysql_query($sql);
    return true;
}

function getMediaInfo($idmedia)
{
    if ($idmedia > 0) {
        $qm = "SELECT id,idgrade,idperiodo,formula FROM medias WHERE id=$idmedia";
        $rm = mysql_query($qm);
        $rowm = mysql_fetch_array($rm, MYSQL_ASSOC);
        $idgrade = $rowm['idgrade'];
        $idperiodo = $rowm['idperiodo'];

        return ["idgrade" => $idgrade, "idperiodo" => $idperiodo];
    }
}

function insereDM($grade, $periodo, $avaliacao)
{

    $existe = checkDM($grade, $periodo, $avaliacao);

    if (!$existe) {
        $ins = "INSERT INTO disciplinas_avaliacoes (idgrade,idperiodo,idavaliacao) VALUES ($grade,$periodo,$avaliacao)";
        $insert = mysql_query($ins);

        return true;
    } else {
    }
}

function atualizaDM($grade, $periodo, $avaliacao)
{
    // $existe = checkDM($grade,$periodo,$avaliacao);
    $limpa = limpaDM($grade, $periodo);
    $ins = "INSERT INTO disciplinas_avaliacoes (idgrade,idperiodo,idavaliacao) VALUES ($grade,$periodo,$avaliacao)";
    $insert = mysql_query($ins);
    return true;
}
