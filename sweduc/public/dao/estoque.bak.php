<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');



/**
 * Send debug code to the Javascript console
 */
function debug_to_console($data)
{
    if (is_array($data) || is_object($data)) {
        echo("<script>console.log('PHP: " . json_encode($data, JSON_THROW_ON_ERROR) . "');</script>");
    } else {
        echo("<script>console.log('PHP: " . $data . "');</script>");
    }
}




$hoje = date("Y-m-d");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id, idunidade FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];
$idunidadefuncionario = $row1['idunidade'];

$valorvenda = str_replace(",", ".", str_replace(".", "", $valorvenda));

if ($action == "cadastraGrupo") {
    $query  = "INSERT INTO estoque_grupos(idunidade, idfuncionario, ideventofinanceiro, datacadastro, grupo) VALUES  ($idunidadefuncionario, $idfuncionario, $ideventofinanceiro, '$hoje', '$grupo');";
    if ($result = mysql_query($query)) {
        echo "blue|Grupo cadastrado.|" . mysql_insert_id();
        $msg = "Grupo $grupo cadastrado.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar grupo.|0";
        $msg = "Erro ao cadastrar grupo $grupo.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $grupo;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaGrupo") {
    $query  = "SELECT COUNT(*) as cnt FROM estoque WHERE idgrupo IN ($id)";
    $result = mysql_query($query);
    if ($row['cnt'] == 0) {
        $query  = "DELETE FROM estoque_grupos WHERE id IN ($id)";
        $result = mysql_query($query);
        echo "blue|Grupo removido.";
        $msg = "Grupo removido.";
        $status = 0;
    } else {
        echo "red|Erro ao remover grupo. Algum Grupo pode ter material em estoque associado.";
        $msg = "Erro ao remover grupo. Algum Grupo pode ter material em estoque associado.";
        $status = 1;
    }

    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cadastraKit") {
    $query  = "INSERT INTO estoque_kits(idunidade, idfuncionario, datacadastro, kit) VALUES  ($idunidadefuncionario, $idfuncionario, '$hoje', '$kit');";
    if ($result = mysql_query($query)) {
        echo "blue|Kit cadastrado.|" . mysql_insert_id();
        $msg = "Kit $kit cadastrado.";
        $status = 0;
    } else {
        $erro = "";
        if (mysql_errno() == 1062) {
            $erro = "Kit já existe!";
        }
        echo "red|Erro ao cadastrar kit. $erro|0";
        $msg = "Erro ao cadastrar kit $kit.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaKit") {
    $query  = "DELETE FROM estoque_kits WHERE id IN ($id)";
    if ($result = mysql_query($query)) {
        $query  = "DELETE FROM estoque_kit_mat WHERE idkit IN ($id)";
        $result = mysql_query($query);
        echo "blue|Kit removido.";
        $msg = "Kit removido.";
        $status = 0;
    } else {
        echo "red|Erro ao remover kit.";
        $msg = "Erro ao remover kit.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "addKit") {
    $query  = "INSERT INTO estoque_kit_mat(idkit, idestoque, quantidade) VALUES($addidkit, $id, $qtdeprokit);";
    if ($result = mysql_query($query)) {
        echo "blue|Material adicionado ao kit.|";
        $msg = "Material adicionado ao kit.";
        $status = 0;
    } else {
        echo "red|Erro ao adicionar ao kit.|0";
        $msg = "Erro ao adicionar ao kit.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "excluidoKit") {
    $query  = "DELETE FROM estoque_kit_mat WHERE idestoque=$id;";
    if ($result = mysql_query($query)) {
        echo "blue|Material removido ao kit.|";
        $msg = "Material removido ao kit.";
        $status = 0;
    } else {
        echo "red|Erro ao remover ao kit.$query|0";
        $msg = "Erro ao remover ao kit.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cadastraUnidadesContagem") {
    $query  = "INSERT INTO estoque_unidadescontagem (idunidade, idfuncionario, datacadastro, unidadescontagem, sigla) VALUES  ($idunidadefuncionario, $idfuncionario, '$hoje', '$unidadescontagem', '$sigla');";
    if ($result = mysql_query($query)) {
        echo "blue|Unidade de Contagem cadastrada.|" . mysql_insert_id();
        $msg = "Unidade de Contagem $unidadescontagem ($sigla) cadastrada.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar Unidade de Contagem.|0";
        $msg = "Erro ao cadastrar Unidade de Contagem $unidadescontagem ($sigla).";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaUnidadesContagem") {
    $query  = "DELETE FROM estoque_unidadescontagem WHERE id IN ($id)";
    if ($result = mysql_query($query)) {
        echo "blue|Unidade de Contagem removida.";
        $msg = "Unidade de Contagem removida.";
        $status = 0;
    } else {
        echo "red|Erro ao remover unidade de contagem.";
        $msg = "Erro ao remover unidade de contagem.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cadastraMaterial") {
    $query  = "INSERT INTO estoque(idunidadescontagem, idunidade, idfuncionario, idgrupo, datacadastro, codigo, descricao, estoqueseguranca, consumodiario, loteecocompra, valorvenda, valorcompra) VALUES  ($idunidadescontagem, $idunidadefuncionario, $idfuncionario, $idgrupo, '$hoje', '$codigo', '$descricao', '$estoqueseguranca', '$consumodiario', '$loteecocompra', '$valorvenda', '$valorcompra');";
    if ($result = mysql_query($query)) {
        $idestoque = mysql_insert_id();
        echo "blue|Material cadastrado.|" . $idestoque;
        $msg = "Material $codigo / $descricao cadastrado.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar material. " . mysql_error() . "|0";
        $msg = "Erro ao cadastrar material $codigo / $descricao .";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "atualizaMaterial") {
    $query  = "UPDATE estoque SET idunidade=$idunidadefuncionario, idfuncionario=$idfuncionario, idgrupo=$idgrupo, datacadastro='$hoje', codigo='$codigo', descricao='$descricao', estoqueseguranca='$estoqueseguranca', consumodiario='$consumodiario', loteecocompra='$loteecocompra', valorvenda='$valorvenda', valorcompra='$valorcompra' WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        echo "blue|Material atualizado.|" . $id;
        $msg = "Material $codigo / $descricao atualizado.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar material $query.|0";
        $msg = "Erro ao atualizar material $codigo / $descricao .";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagaMaterial") {
    $query  = "DELETE FROM estoque WHERE id IN ($id)";
    if ($result = mysql_query($query)) {
        echo "blue|Material removido.";
        $msg = "Material removido.";
        $status = 0;
    } else {
        echo "red|Erro ao remover material.";
        $msg = "Erro ao remover material.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "movimentacao") {
    if ($tipo == 0) {
      // debug_to_console($estoque_unidade);
      // debug_to_console(json_decode($estoque_unidade));
      // debug_to_console(json_decode($estoque_unidade, true));

        $erroexec = [];
        $k = 0;
        $dtmovimentacao = explode("/", $datamovimentacao);
        $datamovimentacao = $dtmovimentacao[2] . "-" . $dtmovimentacao[1] . "-" . $dtmovimentacao[0];

        foreach ($estoque_unidade as $eu) {
            debug_to_console($eu);

            $valormovim = str_replace(",", ".", str_replace(".", "", $valormovimentacao[$k]));
          // $dtmovimentacao = explode ("/",$datamovimentacao);
          // $datamovimentacao = $dtmovimentacao[2]."-".$dtmovimentacao[1]."-".$dtmovimentacao[0];
            $quant = $quantidade[$k];
            $tempodeentrega = $tempoentrega[$k];

            $query = "INSERT INTO estoque_movimentacao(idestoque, idunidadedestino, idterceiro, quantidade, dataoperacao, idfuncionario, datamovimentacao, valormovimentacao, tempoentrega, motivo, estoque_unidade)
                    VALUES ('$idestoque','$idunidadedestino','$idterceiro', $quant, '$hoje', '$idfuncionario', '$datamovimentacao', '$valormovim', '$tempodeentrega', '$motivo','$eu');";

          // $result = mysql_query($query);

            debug_to_console($query);

            if ($result = mysql_query($query)) {
                $erroexec[] = 0;
            } else {
                $erroexec = 1;
            }

            $k++;
        }

        if (!in_array(1, $erroexec)) {
            echo "blue|Movimentação realizada com sucesso $query.|" . $idestoque;
            $msg = "Movimentação realizada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao movimentar material. $query|0";
            $msg = "Erro ao movimentar material.";
            $status = 1;
        }
    } else {
        $valormovimentacao = str_replace(",", ".", str_replace(".", "", $valormovimentacao));
        $dtmovimentacao = explode("/", $datamovimentacao);
        $datamovimentacao = $dtmovimentacao[2] . "-" . $dtmovimentacao[1] . "-" . $dtmovimentacao[0];

      /*
      if($tipo==0) $idunidadedestino=0;
      else if($tipo==1) {
        $idunidadedestino=$idunidadefuncionario;
        $tempoentrega=0;
      }
      */

        if (($idunidadedestino == -1) && ($idterceiro == -1)) {
            $oque = "Baixa";
            $oque1 = "baixar";
        } else {
            $oque = "Movimentação";
            $oque1 = "movimentar";
        }

      // print '>> estoque_unidade:'.$estoque_unidade.'<br>';
      // print_r($estoque_unidade);

        $query = "INSERT INTO estoque_movimentacao(idestoque, idunidadedestino, idterceiro, quantidade, dataoperacao, idfuncionario, datamovimentacao, valormovimentacao, tempoentrega, motivo) VALUES ('$idestoque','$idunidadedestino','$idterceiro', $quantidade, '$hoje', '$idfuncionario', '$datamovimentacao', '$valormovimentacao', '$tempoentrega', '$motivo');";
        if ($result = mysql_query($query)) {
            echo "blue|" . $oque . " realizada com sucesso $query.|" . $idestoque;
            $msg = "" . $oque . " realizada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao " . $oque1 . " material. $query|0";
            $msg = "Erro ao " . $oque1 . " material.";
            $status = 1;
        }
        $parametroscsv = $id . ',' . $novovalor;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
}
