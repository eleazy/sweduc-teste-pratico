<?php

require_once "auth/injetaCredenciais.php";
$query = "SELECT * FROM permissoes WHERE id= '{$_SESSION['permissao']}'";
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$alunos = $row['alunos'];
$unidades = $row['unidades'];   //if (in_array($row['id'], $unidadesEdit)) echo " selected='selected' ";
$academico = $row['academico'];
$financeiro = $row['financeiro'];
$configuracoes = $row['configuracoes'];
$sistema = $row['sistema'];
$perfil = $row['perfil'];
$estoque = $row['estoque'];
$comunicado = $row['comunicado'];

$protocolo = $row['protocolo'];
$marketing = $row['marketing'];

$arraydo1 = ["1", "3", "5", "7"];
$arraydo2 = ["2", "3", "6", "7"];
$arraydo4 = ["4", "5", "6", "7"];
//echo $alunos."][".$academico."][".$financeiro."][".$configuracoes."][".$idpermissoes;

$_unidades = explode(',', $unidades);
$_idx = array_search('0', $_unidades);
if ($_idx !== false) {
    unset($_unidades[$_idx]);

    if (!in_array($_SESSION['id_unidade'], $_unidades)) {
        $_unidades[] = $_SESSION['id_unidade'];
    }
}

$_unidades = array_unique($_unidades);

$usuario_permissoes = [
    'alunos' => [
        'cadastrar' => isset($alunos[0]) && $alunos[0] & 2,
        'excluir' => isset($alunos[0]) && $alunos[0] & 4,
    ],

    'financeiro' => [
        'reabrir-baixar-cancelar-titulos' => isset($financeiro[3]) && $financeiro[3] & 4,
        'reabrir-titulo-no-dia' => isset($financeiro[14]) && $financeiro[14] & 2,
        'excluir-titulos' => isset($financeiro[2]) && $financeiro[2] & 4,
    ],

    'marketing' => [
        'visualizar-todas-prospeccoes' => isset($marketing[4]) && $marketing[4] & 4,
    ],

    'unidades' => $_unidades,
];
