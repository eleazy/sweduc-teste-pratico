<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParseJsonMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Skip if request has body parsed alread
        if (!empty($request->getParsedBody())) {
            return $handler->handle($request);
        }

        // Skip if content type is not json
        if (!str_contains($request->getHeaderLine('Content-Type'), 'application/json')) {
            return $handler->handle($request);
        }

        // Fill parsed body with json properties
        $parsedRequest = $request->withParsedBody(
            json_decode((string) $request->getBody(), null, 512, JSON_THROW_ON_ERROR)
        );

        return $handler->handle($parsedRequest);
    }
}
