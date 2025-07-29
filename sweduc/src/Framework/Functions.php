<?php

declare(strict_types=1);

namespace App\Framework;

use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use App\Framework\Http\ConditionalStreamEmitter;
use Psr\Container\ContainerInterface;
use Monolog\Utils;
use App\Framework\Middleware\MonologConsoleLoggerMiddleware;
use App\Framework\Middleware\ParseJsonMiddleware;
use App\Framework\Middleware\SessionMiddleware;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

use function Sentry\captureException;

if (!function_exists('setupApp')) {
    function setupApp(\Closure $before): void
    {
        $app = new Application(autoBoot: false);
        $before($app);
        $app->boot();
    }
}

if (!function_exists('app')) {
    function app(): Application
    {
        return Application::globalInstance();
    }
}


if (!function_exists('logger')) {
    function logger(): LoggerInterface
    {
        return app()->get(LoggerInterface::class);
    }
}

if (!function_exists('normalizeRequest')) {
    function normalizeRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        // Remove barra / final nas rotas
        $trimmedSlashUri = $uri->withPath(rtrim($path, '/') ?: '/');

        return $request->withUri($trimmedSlashUri);
    }
}

if (!function_exists('hasAcceptJsonHeader')) {
    /**
     * Verifica se a requisição aceita respostas json
     *
     * @return boolean
     */
    function hasAcceptJsonHeader(ServerRequestInterface $request)
    {
        return !empty(array_filter(
            $request->getHeader('Accept'),
            fn($header) => str_contains($header, 'application/json')
        ));
    }
}

if (!function_exists('resolve')) {
    /**
     * Retorna elemento definido na injeção de dependencias
     *
     * @return mixed
     */
    function resolve(mixed $item)
    {
        return app()->get($item);
    }
}

if (!function_exists('isProductionEnv')) {
    function isProductionEnv()
    {
        return app()->isProductionEnv();
    }
}

if (!function_exists('emit')) {
    function emit(ResponseInterface $response): void
    {
        $emitter = resolve(EmitterStack::class);
        $emitter->push(resolve(SapiEmitter::class));
        $emitter->push(resolve(ConditionalStreamEmitter::class));

        // Retorna a resposta para o navegador
        $emitter->emit($response);
    }
}

if (!function_exists('routeRequest')) {
    function routeRequest(ServerRequestInterface $request): ResponseInterface
    {
        /**
         * @var Router
         */
        $router = resolve(Router::class);
        $router->lazyMiddleware(ParseJsonMiddleware::class);

        if (hasAcceptJsonHeader($request)) {
            $strategy = resolve(JsonStrategy::class);
            /**
             * Inibe exibição de erros em chamadas JSON
             * A resposta em HTML atrapalha a comunicação
             */
            ini_set('display_errors', 'false');
        } else {
            $strategy = resolve(ApplicationStrategy::class);
            $router->middleware(resolve(MonologConsoleLoggerMiddleware::class));
            $router->middleware(resolve(SessionMiddleware::class));
        }

        $strategy->setContainer(resolve(ContainerInterface::class));
        $router->setStrategy($strategy);

        require __DIR__ . '/../Routes/web.php';
        require __DIR__ . '/../Routes/api.php';
        require __DIR__ . '/../Routes/legacy.php';

        try {
            return $router->dispatch($request);
        } catch (\Throwable $exception) {
            resolve(LoggerInterface::class)->error(
                sprintf(
                    'Uncaught Exception %s: "%s" at %s line %s',
                    Utils::getClass($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                ),
                ['exception' => $exception]
            );

            captureException($exception);

            /**
             * @var ErrorRendererInterface
             */
            $errorRenderer = app()->get(\App\Framework\Http\ErrorRendererInterface::class);
            return $errorRenderer->handleThrowable($exception, hasAcceptJsonHeader($request));
        }
    }
}
