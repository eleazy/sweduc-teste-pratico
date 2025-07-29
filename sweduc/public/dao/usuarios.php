<?php

require_once 'conectar.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php';
require '../function/ultilidades.func.php';
require '../function/validar.php';
require_once 'funcoes.php';
require_once 'trocasenha.php';
require '../permissoes.php';

$action = $_REQUEST['action'];

function usuarioDisponivel()
{
    $login = mysql_real_escape_string($_REQUEST['usuario']);
    $result = mysql_query("SELECT 1 FROM usuarios WHERE login='$login'");
    $usuario_existente = ! (mysql_num_rows($result) == 0);

    if ($usuario_existente) {
        http_response_code(422);
        echo 'Login de usuário já existente';
    }

    echo 'Login disponível';
}

function cadastrarUsuarioAluno($request, $usuario_permissoes)
{
    array_walk($request, 'mysql_real_escape_string');

    // Valida requisição
    _validar([
        'Campo pessoaId requirido' => !$request['pessoaId'],
        'Formato do campo pessoaId inválido' => !is_numeric($request['pessoaId']),
    ], 422);

    // Valida permissão do usuário
    _validar([
        'Usuário proibído' => !$usuario_permissoes['alunos']['cadastrar']
    ], 403);

    $idpessoa = $request['pessoaId'];
    $username = "a" . str_pad($idpessoa, 6, "0", STR_PAD_LEFT);
    $senhaaluno = gera_senha(6);
    $passhash = password_hash($senhaaluno, PASSWORD_DEFAULT);
    $apikey = generateApiKey();
    $sql = "INSERT INTO usuarios (idpessoa, tipo, idpermissao, login, senha, password_hash, api_key)
            VALUES ('$idpessoa', '0', '3', '$username', '$senhaaluno', '$passhash', '$apikey');";

    $result = mysql_query($sql);

    if ($result) {
        echo json_encode([
            'msg' => 'Usuário criado',
            'usuario' => $username,
            'senha' => $senhaaluno,
        ], JSON_THROW_ON_ERROR);

        http_response_code(200);
    } else {
        $error = mysql_error();
        echo "Houve um problema ao criar usuário";
        http_response_code(409);
    }
}

$action($_REQUEST, $usuario_permissoes);
