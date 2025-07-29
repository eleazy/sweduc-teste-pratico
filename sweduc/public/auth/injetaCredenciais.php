<?php

require_once __DIR__ . '/../dao/conectar.php';
include_once __DIR__ . '/../dao/logs.php';

use App\Usuarios\AuthManager as Auth;

$auth = new Auth();
$sessao_valida = $auth->estaAutenticado();
$credenciaisIncorretas = false;

// Impede parametros de credenciais na requisição
if (isset($_POST['idpessoalogin']) && $_POST['idpessoalogin'] != $_SESSION['id_pessoa']) {
    salvaLog($_SESSION['id_pessoa'], basename(__FILE__), '', '', '', "Usuario {$_SESSION['id_pessoa']} usando id incorreto {$_POST['idpessoalogin']}");
    $credenciaisIncorretas = true;
}

if (isset($_POST['idpermissoes']) && $_POST['idpermissoes'] != $_SESSION['permissao']) {
    salvaLog($_SESSION['id_pessoa'], basename(__FILE__), '', '', '', "Usuario {$_SESSION['id_pessoa']} usando permissão incorreta {$_POST['idpermissoes']}");
    $credenciaisIncorretas = true;
}

if ($sessao_valida && !$credenciaisIncorretas) {
    $nomeusuario = $_SESSION['nome'];
    $idusu = $_SESSION['id_usuario'];
    $idpessoalogin = $_SESSION['id_pessoa'];
    $tipoUsuario = $_SESSION['tipoUsuario'];
    $idpermissoes = $_SESSION['permissao'];
} else {
    $auth->logout();
    header('Location:/');
    exit;
}
