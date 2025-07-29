<?php

declare(strict_types=1);

namespace App\Usuarios;

use App\Model\Core\Usuario;
use Psr\Http\Message\RequestInterface;

class AuthManager
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * Identifica usuário logado no sistema
     *
     * @return Usuario instância do usuário logado no sistema
     */
    public function usuario(RequestInterface $request = null)
    {
        return Usuario::find($_SESSION['id_usuario']);
    }

    /**
     * Realiza login por meio das credenciais de acesso
     *
     * @return bool verdadeiro se login foi realizado
     */
    public function autenticar(string $username, string $password): bool
    {
        if (empty($username) || empty($password)) {
            return false;
        }

        $usuario = Usuario::where('login', $username)->first();

        if (!$usuario) {
            return false;
        }

        if ($this->exFuncionario($usuario)) {
            return false;
        }

        if (!$usuario->verificaSenha($password, true)) {
            return false;
        }

        $this->login($usuario);
        return true;
    }

    /**
     * Verifica se o usuário está personificando outro
     */
    public function estaPersonificado(): bool
    {
        return isset($_SESSION['personificador']);
    }

    /**
     * Verifica se existe usuário logado
     */
    public function estaAutenticado(): bool
    {
        return isset(
            $_SESSION['login'],
            $_SESSION['id_usuario'],
            $_SESSION['id_pessoa'],
            $_SESSION['tipoUsuario'],
            $_SESSION['permissao']
        );
    }

    /**
     * Realiza login adminstrativo em conta de usuário
     * sobreponto a sessão de login do admin pela do usuário
     * ao finalizar o uso o logout poderá reverter o login
     * para a conta do admin
     */
    public function personificar(Usuario $usuario): void
    {
        if ($_SESSION['personificador']) {
            $this->logout();
        }

        $_SESSION['personificador'] = $_SESSION;
        $this->login($usuario);
    }

    /**
     * Realiza login por sessão
     */
    public function login(Usuario $usuario): void
    {
        $_SESSION['id_pessoa'] = $usuario->pessoa->id;
        $_SESSION['id_usuario'] = $usuario->id;
        $_SESSION['login'] = $usuario->login;
        $_SESSION['tipoUsuario'] = $usuario->tipo;
        $_SESSION['permissao'] = $usuario->idpermissao;
        $_SESSION['nome'] = $usuario->pessoa->nome;

        // Informações específicas de funcionário
        if ($usuario->funcionario) {
            $_SESSION['id_funcionario'] = $usuario->funcionario->id;
            $_SESSION['id_unidade'] = $usuario->funcionario->idunidade;
            $_SESSION['nome_unidade'] = $usuario->funcionario->unidade->unidade;
        } else {
            $_SESSION['id_funcionario'] = null;
            $_SESSION['id_unidade'] = null;
            $_SESSION['nome_unidade'] = null;
        }
    }

    /**
     * Realiza logout da conta personificada se houver
     * ou da conta normal
     */
    public function logout(): void
    {
        if (isset($_SESSION['personificador'])) {
            $_SESSION = $_SESSION['personificador'] ?? $_SESSION;
            return;
        }

        unset(
            $_SESSION['login'],
            $_SESSION['nome'],
            $_SESSION['senha'],
            $_SESSION['id_usuario'],
            $_SESSION['id_pessoa'],
            $_SESSION['tipoUsuario'],
            $_SESSION['permissao'],
            $_SESSION['id_funcionario'],
            $_SESSION['id_funcionariounidade'],
            $_SESSION['personificador']
        );
    }

    private function exFuncionario(Usuario $usuario): bool
    {
        if ($usuario->idpermissao == 13) {
            $usuario->removerSenha(true);
            return true;
        }

        return false;
    }
}
