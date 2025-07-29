<?php

use App\Academico\Model\Nota;

include '../headers.php';
include 'conectar.php';
include_once 'logs.php';
include '../function/helpers.php';
require '../helper_notas.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php';

$agora = date("Y-m-d H:i:s");

$debug = 0;
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

if ($debug) {
    echo $action . "<br>";
}


$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    foreach ($idgrade as $idg) {
        foreach ($idperiodo as $idp) {
            $query  = "SELECT COUNT(*) as cnt FROM medias WHERE idgrade=$idg AND idperiodo=$idp";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            if ($row['cnt'] > 0) {
                echo "red|Uma das grades já possui Cálculo de Média associado para um dos períodos|0|0";
                exit(1);
            }
        }
    }
    $msg = "";
    $erro = 0;
    $nomemedia = explode(",", $nomemedia);
    foreach ($idgrade as $idg) {
        $i = 0;
        foreach ($idperiodo as $idp) {
            $query1  = "INSERT INTO medias(idgrade, idperiodo, nome, formula) VALUES ($idg, $idp, '" . $nomemedia[$i] . "', '" . $quadro . "');";
            if ($debug) {
                echo $query1 . "<br>";
            }
            if ($result1 = mysql_query($query1)) {
                $idmedia = mysql_insert_id();

                salvaAvaliacoesFormulas($quadro, $idmedia, 0);

                $msg .= "Cálculo de Médias cadastrado.|" . $nomemedia[$i] . "|" . $idmedia . "|" . $quadro . " // ";
            } else {
                $erro++;
                $msg = "Erro ao cadastrar|" . $nomemedia[$i] . "|" . $quadro . " // ";
            }
            $i++;
        }
    }
    if ($erro == 0) {
        echo "blue|Cálculo de Médias cadastrado.|" . $nomemedia . "|" . $idmedia . "|" . $quadro;
    } else {
        echo "red|Erro ao cadastrar|0|" . $quadro;
    }

    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeMedias") {
    $query  = "SELECT * FROM medias WHERE idgrade IN (" . implode(",", $idgrade) . ") ORDER BY nome";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
   <input type="button" name="#M<?=$row['id']?>@" class="button insQuadro" value="  <?=$row['nome']?> => #M<?=$row['id']?>@ "  onClick="$('#quadro').val($('#quadro').val()+$(this).attr('name'));"/><br /><br />
        <?php
    }
} elseif ($action == "recebeMediasSubD") {
    $query = "SELECT * FROM `medias` WHERE idgrade IN ( SELECT id FROM grade WHERE idanoletivo=$idanoletivo AND iddisciplina IN ( SELECT id FROM `disciplinas` WHERE numordem= -( SELECT numordem FROM  `disciplinas` WHERE numordem>0 AND id = ( SELECT iddisciplina FROM  `grade` WHERE id =$idgrade AND idanoletivo=$idanoletivo ) ) ) ) ORDER BY nome";
    $result = mysql_query($query);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
   <input type="button" name="#M<?=$row['id']?>@" class="button insQuadro" value="  <?=$row['nome']?> => #M<?=$row['id']?>@ "  onClick="$('#quadro').val($('#quadro').val()+$(this).attr('name'));"/><br /><br />
        <?php
    }
  //echo $query;
} elseif ($action == "apaga") {
    $query  = "SELECT COUNT(*) as cnt FROM alunos_notas WHERE idmedia=" . $id;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($row['cnt'] > 0) {
        Nota::where('idmedia', $id)->delete();
    }

    $infoMedia = getMediaInfo($id);
    $query  = "DELETE FROM medias WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        limpaDM($infoMedia['idgrade'], $infoMedia['idperiodo']);

        echo "blue|Cálculo de Média removido.";
        $msg = "Cálculo de Média removido.";
    } else {
        echo "red|Erro 2 ao remover.";
        $msg = "Erro 2 ao remover.";
    }

    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "newformula") {
    $media = preg_replace('/#\w\d+@/', '(1)', $newvalue);
    try {
        eval("$media;");
    } catch (\Throwable $exception) {
        echo "red|Erro ao alterar.\n";
        echo $exception->getMessage();
        exit;
    }

    $query  = "UPDATE medias SET formula='" . $newvalue . "' WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        salvaAvaliacoesFormulas($newvalue, $id, 1);

        $msg = "Fórmula alterada.";
        echo "blue|$msg";
    } else {
        $msg = "Erro ao alterar.";
        echo "red|$msg";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
