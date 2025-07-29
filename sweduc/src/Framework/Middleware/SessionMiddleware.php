<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use App\Usuarios\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public function __construct(protected SessionManager $session)
    {
    }

    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->session->start();

        return $handler->handle(
            $request
                ->withAttribute('session', $_SESSION)
                ->withAttribute('csrfToken', $_SESSION['csrfToken'])
        );
    }
}
