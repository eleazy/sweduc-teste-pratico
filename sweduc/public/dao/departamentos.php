<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $query = "SELECT COUNT(*) as cnt FROM departamentos WHERE departamento='$departamento'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];

    if ($cnt == 0) {
        $query = "INSERT INTO departamentos (departamento) VALUES ('$departamento');";
        $id = 0;
        if ($result = mysql_query($query)) {
            $id = mysql_insert_id();
            echo $id . "blue|Departamento $departamento cadastrado com sucesso.|" . $departamento;
            $msg = "Departamento $departamento cadastrado com sucesso.";
            $status = 0;
        } else {
            echo "0|Erro ao cadastrar departamento $departamento.|" . $departamento;
            $msg = "Erro ao cadastrar departamento $departamento.";
            $status = 1;
        }
        $parametroscsv = $id . ',' . $departamento;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else {
        echo "0|Departamento $departamento já existe.|" . $departamento;
        $msg = "Departamento $departamento já existe.";
        $status = 1;
        $parametroscsv = $cnt . ',' . $id . ',' . $departamento;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "update") {
    $query = "UPDATE departamentos SET departamento='$departamento' WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Departamento $departamento atualizado com sucesso.";
        $msg = "Departamento $departamento atualizado com sucesso.";
        $status = 0;
    } else {
        echo "0|Erro ao atualizar departamento $departamento.";
        $msg = "Erro ao atualizar departamento $departamento.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $departamento;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $queryParaPegarNome = "SELECT departamento FROM departamentos WHERE id=$id";
    $resultadoDaQueryParaPegarNome = mysql_query($queryParaPegarNome);
    while ($resultadoDaQuery = mysql_fetch_array($resultadoDaQueryParaPegarNome)) {
        $nomeDoDepartamentoDeletado = $resultadoDaQuery['departamento'];
    }

    $msg = "Departamento " . $nomeDoDepartamentoDeletado . " removido com sucesso.";
    $query = "DELETE FROM departamentos WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Departamento removido com sucesso.";

        $status = 0;
    } else {
        echo "0|Erro ao remover departamento.";
        $msg = "Erro ao remover departamento.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeDepartamentos") {
    $query1 = "SELECT * FROM departamentos ORDER BY departamento ASC";
    $result1 = mysql_query($query1);
    $tem = 0;
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo '<option value="' . $row1['id'] . '">' . $row1['departamento'] . '</option>';
    }
} elseif ($action == "departamentosPessoa") {
    $query1 = 'SELECT
                    p.*
                FROM
                    departamentos d
                        INNER JOIN
                    funcionarios f ON d.id = f.iddepartamento
                        INNER JOIN
                    pessoas p ON f.idpessoa = p.id
                WHERE
                    d.id = ' . $id;
    $result1 = mysql_query($query1);
    $tem = 0;
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo '<option value="' . $row1['id'] . '">' . $row1['nome'] . '</option>';
    }
} elseif ($action == "departamentosPessoa2") {
    $idunidade;
    if ($idunidade != 0) {
        $sqlUnidade = ' and f.idunidade = ' . $idunidade;
    } else {
        $sqlUnidade = '';
    }
    $query1 = 'SELECT
                    p.*, f.id idfuncionarios
                FROM
                    departamentos d
                        INNER JOIN
                    funcionarios f ON d.id = f.iddepartamento
                        INNER JOIN
                    pessoas p ON f.idpessoa = p.id
                WHERE
                    d.id = ' . $id . $sqlUnidade;
    $result1 = mysql_query($query1);
    $tem = 0;
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        if ($tem == 0) {
            echo "<tr><td>";
            echo '<input type = "checkbox" class = "group1" id = "ids' . $row1['idfuncionarios'] . '" name = "idfuncionarios[]" value = "' . $row1['idfuncionarios'] . '"/>';
            echo '<label for = "ids' . $row1['idfuncionarios'] . '"><span></span>' . $row1['nome'] . '</label>';
            echo "</td>";
        } elseif ($tem == 1) {
            echo "<td>";
            echo '<input type = "checkbox" class = "group1" id = "ids' . $row1['idfuncionarios'] . '" name = "idfuncionarios[]" value = "' . $row1['idfuncionarios'] . '"/>';
            echo '<label for = "ids' . $row1['idfuncionarios'] . '"><span></span>' . $row1['nome'] . '</label>';
            echo "</td>";
        } else {
            echo "<td>";
            echo '<input type = "checkbox" class = "group1" id = "ids' . $row1['idfuncionarios'] . '" name = "idfuncionarios[]" value = "' . $row1['idfuncionarios'] . '"/>';
            echo '<label for = "ids' . $row1['idfuncionarios'] . '"><span></span>' . $row1['nome'] . '</label>';
            echo "</td></tr>";
            $tem = -1;
        }
        $tem++;
    }
} elseif ($action == "departamentosPessoa3") {
    $idunidade;
    if ($idunidade != 0) {
        $sqlUnidade = ' and f.idunidade = ' . $idunidade;
    } else {
        $sqlUnidade = '';
    }
    $query1 = 'SELECT
                    p.*, f.id idfuncionarios
                FROM
                    departamentos d
                        INNER JOIN
                    funcionarios f ON d.id = f.iddepartamento
                        INNER JOIN
                    pessoas p ON f.idpessoa = p.id
                WHERE
                    d.id = ' . $id . $sqlUnidade;

    $result1 = mysql_query($query1);
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo '<option value="' . $row1['idfuncionarios'] . '">' . $row1['nome'] . '</option>';
    }
}
