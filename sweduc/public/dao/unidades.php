<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
include('../permissoes.php');
$agora = date("Y-m-d H:i:s");
$debug = 0;
$keys = array_keys($_REQUEST);
foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}

if ($action == "cadastra") {
    if ($id == 0) { //CADASTRO
        $query  = "SELECT COUNT(*) as cnt FROM unidades WHERE unidade='$unidade'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        $cnt = $row['cnt'];
        if ($cidades == "") {
            $cidades = "0";
        }
        if ($cnt == 0) {
            $query  = "INSERT INTO unidades (unidade, telefone, email, endereco, numero, complemento, bairro, cidade, uf, cep, anoletivo_prospeccao ) VALUES ('$unidade','$telefone', '$email', '$endereco','$numero', '$complemento', '$bairro', '$cidades', $uf, '$cep', '$anoletivo_prospeccao');";
            if ($result = mysql_query($query)) {
                $idunidade = mysql_insert_id();
                foreach ($idempresa as $k) {
                    $query  = "INSERT INTO unidades_empresas (idunidade, idempresa) VALUES ( $idunidade, $k );";
                    $result = mysql_query($query);
                }
                echo "blue|Unidade " . $unidade . " cadastrada.";
                $msg = "Unidade " . $unidade . " foi cadastrada com sucesso";
            } else {
                echo "red|Erro ao inserir nova unidade.";
            }
        } else {
            echo "red|Unidade $unidade j� existe!.";
        }
        $parametroscsv = $query;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else { //UPDATE
        $query  = "UPDATE unidades  SET unidade='$unidade', telefone='$telefone', email='$email', endereco='$endereco', numero='$numero', complemento='$complemento', bairro='$bairro', cidade='$cidades', uf='$uf', cep='$cep', anoletivo_prospeccao='$anoletivo_prospeccao' WHERE id=$id";
        if ($debug) {
            echo $query . "<br />";
        }
        if ($result = mysql_query($query)) {
            $query  = "DELETE FROM unidades_empresas WHERE idunidade=" . $id;
            if ($debug) {
                echo $query . "<br />";
            }
            $result = mysql_query($query);
            foreach ($idempresa as $k) {
                $query = "INSERT INTO unidades_empresas (idunidade, idempresa) VALUES ( $id, $k );";
                if ($debug) {
                    echo $query . "<br />";
                }
                $result = mysql_query($query);
            }
            echo "blue|Unidade $unidade atualizada.";
            $msg = "Unidade $unidade foi atualizada.";
        } else {
            echo "red|Erro ao atualizar a unidade.";
            $msg = "Erro ao atualizar a unidade.";
        }
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $queryParaPegarNome =  "SELECT unidade FROM unidades WHERE id=" . $id;
    $resultado = mysql_query($queryParaPegarNome);
    while ($row = mysql_fetch_assoc($resultado)) {
        $unidade = $row['unidade'];
    }
    $msg1 = "Unidade " . $unidade . " foi removida.";
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg1);
    $query  = "DELETE FROM unidades WHERE id=$id";
    if ($result = mysql_query($query)) {
        $query  = "DELETE FROM unidades_empresas WHERE idunidade=$id";
        if ($result = mysql_query($query)) {
            $msg = "Unidade removida.";
            echo "blue|Unidade removida.";
        } else {
            $msg = "Erro 1 ao remover Unidade.";
            echo "red|Erro 1 ao remover Unidade.";
        }
    } else {
        $msg = "Erro 2 ao remover Unidade.";
        echo "red|Erro 2 ao remover Unidade.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeUnidades") {
    $query  = "SELECT * FROM unidades";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['unidade'] . '</option>';
    }
} elseif ($action == "recebeUnidadesFuncionario") {
    $idunidade = $_SESSION['id_unidade'];
    if ($idpermissoes == "1") {
        $query  = "SELECT * FROM unidades GROUP BY unidade ORDER BY unidade ASC";
    } elseif ($unidades == "0") {
        $query  = "SELECT * FROM unidades WHERE id = '$idunidade' GROUP BY unidade ORDER BY unidade ASC";
    } else {
        $query  = "SELECT * FROM unidades WHERE id IN ($unidades) GROUP BY unidade ORDER BY unidade ASC";
    }

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['unidade'] . '</option>';
    }
} elseif ($action == "recebeUnidadesFuncionario2") {
    if ($idunidade != 0) {
        $sqlUnidade = ' where  f.idunidade = ' . $idunidade;
    } else {
        $sqlUnidade = '';
    }
    $query = 'SELECT
                    p.nome, d.departamento, u.unidade, f.id idfuncionarios
                FROM
                    departamentos d
                        INNER JOIN
                    funcionarios f ON d.id = f.iddepartamento
                        INNER JOIN
                    pessoas p ON f.idpessoa = p.id
                        INNER JOIN
                    unidades u ON u.id = f.idunidade
               ' . $sqlUnidade . ' order by p.nome';

    $result = mysql_query($query);
    $i = 0;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $d['data'][$i]['nome'] = $row['nome'];
        $d['data'][$i]['departamento'] = $row['departamento'];
        $d['data'][$i]['unidade'] = $row['unidade'];
        $d['data'][$i]['idfuncionarios'] = '<input type = "checkbox" class = "group1" id = "ids' . $row['idfuncionarios'] . '" name = "idfuncionarios[]" value = "' . $row['idfuncionarios'] . '"/>';
        $d['data'][$i]['idfuncionarios'] .=  '<label for = "ids' . $row['idfuncionarios'] . '"><span></span></label>';
        $i++;
    }
    echo json_encode($d, JSON_THROW_ON_ERROR);
}
