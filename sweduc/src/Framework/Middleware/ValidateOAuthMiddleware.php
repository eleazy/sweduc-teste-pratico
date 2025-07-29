<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use App\Model\Core\Usuario;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateOAuthMiddleware implements MiddlewareInterface
{
    public function __construct(protected ResourceServerMiddleware $middleware, protected ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middleware = $this->middleware;

        return $middleware($request, $this->responseFactory->createResponse(), function ($request) use ($handler) {
            $usuarioId = $request->getAttribute('oauth_user_id');

            $usuario = Usuario::findOrFail($usuarioId);

            return $handler->handle(
                $request->withAttribute(Usuario::class, $usuario)
            );
        });
    }
}
