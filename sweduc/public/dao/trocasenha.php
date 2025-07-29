<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_REQUEST);
foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

function checkFieldExists()
{
    $result = mysql_query("SHOW COLUMNS FROM usuarios LIKE 'password_hash'");
    $exists = (mysql_num_rows($result)) ? true : false;
    return $exists;
}

function generateApiKey()
{
    return md5(uniqid(random_int(0, mt_getrandmax()), true));
}

// this will be used to generate a hash
function hashpass($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function validaSenha($senha)
{
  // Verifica se a senha possui 6 caracteres ou mais
    $tamanhoMinimo = strlen($senha) > 5;

  // Valida força da senha
    $letras = preg_match('@[^0-9]@', $senha);
    $numeros = preg_match('@[0-9]@', $senha);

    return ($tamanhoMinimo && $letras && $numeros);
}

function trocaSenha($idpessoa, $senhaatual, $novasenha)
{
    $trocado = null;
    $idfuncionario = null;
    $action = null;
    $senhaatual = mysql_real_escape_string($senhaatual);
    $novasenha = mysql_real_escape_string($novasenha);
    $passhash = password_hash($novasenha, PASSWORD_DEFAULT);
    $apikey = generateApiKey();

    if (validaSenha($novasenha)) {
        $query = "UPDATE usuarios SET senha='" . $novasenha . "', password_hash='" . $passhash . "', api_key='" . $apikey . "' WHERE idpessoa=" . $idpessoa . " AND senha='" . $senhaatual . "'";

        $result = mysql_query($query);
        $trocado = mysql_affected_rows() > 0;
    }

    if ($trocado) {
        echo "blue|Senha atualizada com sucesso.";
        $msg = "Senha atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualiza a senha. Confira os dados digitados e tente novamente." . mysql_affected_rows();
        $msg = "Erro ao atualiza a senha.";
        $status = 1;
    }

    $parametroscsv = mysql_affected_rows() . ',' . $idpessoa . ',' . $senhaatual . ',' . $novasenha;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}

if ($action == "trocasenha") {
    trocaSenha($idpessoa, $senhaatual, $novasenha);
}
