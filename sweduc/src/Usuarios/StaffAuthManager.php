<?php

declare(strict_types=1);

namespace App\Usuarios;

use App\Model\Core\Usuario;
use App\Usuarios\AuthManager as Auth;
use Auth0\SDK\Auth0;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;

class StaffAuthManager
{
    protected Auth0 $auth0;
    protected string $callbackUrl;

    public function __construct(
        protected Auth $auth
    ) {
        $this->callbackUrl = $_SERVER['AUTH0_BASE_URL'] . '/staff-login';

        $this->auth0 = new Auth0([
            'domain' => $_SERVER['AUTH0_DOMAIN'],
            'clientId' => $_SERVER['AUTH0_CLIENT_ID'],
            'clientSecret' => $_SERVER['AUTH0_CLIENT_SECRET'],
            'cookieSecret' => $_SERVER['AUTH0_COOKIE_SECRET']
        ]);
    }

    public function redirectToLogin(): ResponseInterface
    {
        $this->auth0->clear();
        return new RedirectResponse($this->auth0->login($this->callbackUrl));
    }

    public function callback()
    {
        $this->auth0->exchange($this->callbackUrl);
        $auth0user = $this->auth0->getUser();
        $usuario = $this->gerarUsuario($auth0user);

        $this->auth->login($usuario);
    }

    public function redirectToLogout(): ResponseInterface
    {
        $uri = $this->auth0->logout($_SERVER['AUTH0_BASE_URL']);
        return new RedirectResponse($uri);
    }

    protected function gerarUsuario(array $auth0): Usuario
    {
        $suporte = Usuario::firstWhere('login', 'suporte');

        $usuario = Usuario::firstWhere([
            'provider_iss' => $auth0['iss'],
            'provider_sub' => $auth0['sub'],
        ]) ?? $suporte->replicate([
            'idpessoa',
            'provider_iss',
            'provider_sub',
            'login',
        ]);

        $pessoa = $usuario->pessoa ?? $suporte->pessoa->replicate(['nome']);
        $pessoa->fill([
            'nome' => $auth0['name']
        ]);
        $pessoa->saveOrFail();

        $usuario->fill([
            'idpessoa' => $pessoa->id,
            'provider_iss' => $auth0['iss'],
            'provider_sub' => $auth0['sub'],
            'login' => $auth0['nickname'],
        ]);
        $usuario->saveOrFail();

        $funcionario = $usuario->funcionario ?? $suporte->funcionario->replicate(['idpessoa']);
        $funcionario->fill([
            'idpessoa' => $pessoa->id,
        ]);
        $funcionario->saveOrFail();

        return $usuario;
    }
}
