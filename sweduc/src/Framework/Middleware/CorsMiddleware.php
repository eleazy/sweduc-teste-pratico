<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private array $origins;

    public function __construct(array $origins = [])
    {
        if (empty($origin)) {
            $this->origins = ['*'];
        }

        $this->origins ??= $origins;
    }

    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return array_reduce($this->origins, [$this, 'applyHeader'], $handler->handle($request))
            ->withHeader('allow', 'GET, POST, PUT, DELETE')
            ->withHeader('access-control-allow-methods', 'GET, POST, PUT, DELETE')
            ->withHeader('access-control-allow-headers', '*');
    }

    private function applyHeader(ResponseInterface $response, string $origin)
    {
        return $response->withAddedHeader('access-control-allow-origin', $origin);
    }
}
