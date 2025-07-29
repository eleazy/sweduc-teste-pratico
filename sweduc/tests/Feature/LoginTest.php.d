<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;

use function PHPUnit\Framework\assertEquals;
use function Pest\Faker\faker;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertStringContainsStringIgnoringCase;
use function PHPUnit\Framework\assertStringNotContainsStringIgnoringCase;

test('login comum', function () {
    $http = new Client([
        'base_uri' => 'http://localhost',
        'cookies' => true,
    ]);

    $user = criaUsuario();

    $redirectedTo = '';

    $http->get('/', [
        'on_stats' => function (TransferStats $stats) use (&$redirectedTo) {
            $redirectedTo = $stats->getEffectiveUri();
        }
    ]);

    $http->post($redirectedTo, [
        'form_params' => [
            'login' => $user->login,
            'senha' => $user->senha,
            'logar' => '',
            'idescola' => 1,
        ]
    ]);

    $homeRequest = $http->get('/');
    assertStringNotContainsStringIgnoringCase('name="formlogin"', $homeRequest->getBody()->getContents());
    assertEquals(200, $homeRequest->getStatusCode());
});

test('login com senha errada deve ser impedido', function () {
    $http = new Client([
        'base_uri' => 'http://localhost',
        'cookies' => true,
    ]);

    $user = criaUsuario();

    /** @var ResponseInterface */
    $loginRequest = $http->post('/login', [
        'form_params' => [
            'login' => $user->login,
            'senha' => faker()->password,
        ]
    ]);

    $loginHtmlResponse = $loginRequest->getBody()->getContents();

    assertStringContainsString('Usuário ou senha incorreta.', $loginHtmlResponse);
    assertStringContainsStringIgnoringCase('name="formlogin"', $loginHtmlResponse);

    $homeRequest = $http->get('/', ['allow_redirects' => false]);
    assertEquals(302, $homeRequest->getStatusCode());
});

test('login de ex funcionário deve ser impedido', function () {
    $http = new Client([
        'base_uri' => 'http://localhost',
        'cookies' => true,
    ]);

    $user = criaUsuario();
    $user->idpermissao = 13;
    $user->save();

    /** @var ResponseInterface */
    $loginRequest = $http->post('/login', [
        'form_params' => [
            'login' => $user->login,
            'senha' => $user->senha,
        ]
    ]);

    $loginHtmlResponse = $loginRequest->getBody()->getContents();

    assertStringContainsString('Usuário ou senha incorreta.', $loginHtmlResponse);
    assertStringContainsStringIgnoringCase('name="formlogin"', $loginHtmlResponse);

    $homeRequest = $http->get('/', ['allow_redirects' => false]);
    assertEquals(302, $homeRequest->getStatusCode());
});
