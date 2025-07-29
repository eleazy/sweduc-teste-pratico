<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use App\Usuarios\SessionManager;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyCsrfTokenMiddleware implements MiddlewareInterface
{
    public function __construct(protected SessionManager $session)
    {
    }

    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->getToken($request);

        if ($this->session->validateCsrfToken($token)) {
            return $handler->handle($request);
        }

        $referer = $request->getHeaderLine('Referer');
        return new RedirectResponse($referer);
    }

    protected function getToken($request)
    {
        $isJson = !empty(array_filter($request->getHeader('content-type'), fn($x) => str_contains($x, 'application/json')));
        return $isJson
            ? $this->getJsonToken($request)
            : $request->getParsedBody()['csrf-token'];
    }

    protected function getJsonToken($request)
    {
        $body = $request->getBody()->getContents();
        $parsedRequest = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        return $parsedRequest['csrf-token'] ?? null;
    }
}
