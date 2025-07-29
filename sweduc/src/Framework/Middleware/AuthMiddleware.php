<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use App\Model\Core\Usuario;
use App\Usuarios\AuthManager;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(protected AuthManager $auth)
    {
    }

    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        if (!empty($queryParams['token'])) {
            $usuario = Usuario::firstWhere('api_key', $queryParams['token']);

            if ($usuario) {
                $this->auth->login($usuario);
                return $handler->handle(
                    $request->withAttribute(Usuario::class, $usuario)
                );
            }
        } elseif ($this->auth->estaAutenticado()) {
            if ($usuario = $this->auth->usuario($request)) {
                return $handler->handle(
                    $request->withAttribute(Usuario::class, $usuario)
                );
            } else {
                $this->auth->logout();
            }
        }

        $redirect = urlencode((string) $request->getUri());
        return new RedirectResponse('/login?redirect=' . $redirect);
    }
}
