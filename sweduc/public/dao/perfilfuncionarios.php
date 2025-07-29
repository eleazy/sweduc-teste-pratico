<?php

use App\Usuarios\PermissoesService;

include('../headers.php');
include('conectar.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}


include_once('logs.php');
$debug = 0;

$agora = date("Y-m-d H:i:s");

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "apaga") {
    $query = "SELECT COUNT(*) as cnt FROM usuarios WHERE idpermissao=" . $id;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];
    $queryParaPegarPerfil = "SELECT perfil FROM permissoes WHERE id=" . $id;
    $resultado = mysql_query($queryParaPegarPerfil);
    while ($linhaPerfil = mysql_fetch_array($resultado)) {
        $perfil = $linhaPerfil['perfil'];
    }
    $msg = "Perfil " . $perfil . " removido com sucesso.";

    if ($cnt == 0) {
        $query = "DELETE FROM permissoes WHERE id=$id";
        if ($result = mysql_query($query)) {
            echo "blue|Perfil removido com sucesso.";

            $status = 0;
        } else {
            echo "red|Erro ao remover perfil!";
            $msg = "Erro ao remover perfil!";
            $status = 1;
        }
        $parametroscsv = $id;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else {
        echo "red|Perfil em uso. Não pode ser removido!";
        $msg = "Perfil em uso. Não pode ser removido!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $cnt;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    $permissoes = new PermissoesService();
    $permissoes->importaPermissoesLegadas();
} elseif ($action == "paisalunos") {
    $query = "UPDATE cursos SET $campo='" . $paisalunos . "' WHERE id=" . $idcurso;
    if ($result = mysql_query($query)) {
        echo "blue|Perfil atualizado com sucesso. ";
        $msg = "Perfil: Pais de Aluno atualizado com sucesso. Pais de Aluno " . $paisalunos;
        $status = 0;
    } else {
        echo "red|Erro ao atualizar perfil!";
        $msg = "Erro ao atualizar perfil!";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
