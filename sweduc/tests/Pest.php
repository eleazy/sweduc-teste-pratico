<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/
use Tests\TestCase;
use App\Usuarios\AuthManager;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use App\Model\Core\Pessoa;
use App\Model\Core\Usuario;
use App\Framework\Model;
use GuzzleHttp\Client;

uses(TestCase::class)->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

use function App\Framework\resolve;
use function Pest\Faker\faker;
use function PHPUnit\Framework\assertStringContainsStringIgnoringCase;

expect()->extend('toBeOne', fn() => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function loggedAs(Usuario $usuario): void
{
    /**
     * @var AuthManager
     */
    $authManager = resolve(AuthManager::class);
    $authManager->login($usuario);
}

function get(UriInterface|string $uri, array $queryParameters = []): ResponseInterface
{
    return test()->get($uri, $queryParameters);
}

function post(UriInterface|string $uri, array $data = []): ResponseInterface
{
    return test()->post($uri, $data);
}

function postJson(UriInterface|string $uri, array $data = []): ResponseInterface
{
    return test()->postJson($uri, $data);
}

function putJson(UriInterface|string $uri, array $data = []): ResponseInterface
{
    return test()->putJson($uri, $data);
}

/**
 * Captura output do stdout como echo e dispatcher http e compara com resultado esperado
 *
 * @param $expected
 * @param Closure $actual
 * @return void
 */
function assertOutput($expected, callable $actual, string $message = '')
{
    ob_start();

    try {
        call_user_func($actual);
        $content = ob_get_contents();
        ob_end_clean();
    } catch (\Throwable $th) {
        ob_end_clean();
        throw $th;
    }

    return assertStringContainsStringIgnoringCase($expected, $content, $message);
}

function criaUsuario($login = null, $senha = null)
{
    $dadosLogin = array_filter(compact('login', 'senha'));
    return Usuario::factory($dadosLogin)->funcionario()->create();
}

function logadoComoAdmin(): Client
{
    criaUsuario('admin', 'admin');

    $http = new Client([
        'base_uri' => 'http://localhost',
        'cookies' => true,
    ]);

    $http->post('/login', [
        'form_params' => [
            'login' => 'admin',
            'senha' => 'admin',
        ],
    ]);

    return $http;
}
