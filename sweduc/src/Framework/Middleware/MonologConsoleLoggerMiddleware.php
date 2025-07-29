<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MonologConsoleLoggerMiddleware implements MiddlewareInterface
{
    public function __construct(private Logger $logger)
    {
    }

    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $shouldDisplayErrors = filter_var($_ENV['DISPLAY_ERRORS'], FILTER_VALIDATE_BOOLEAN);
        $isHtml = $response->getHeader('Content-type') == 'text/html';

        if ($shouldDisplayErrors && $isHtml) {
            $this->logger->pushHandler(new BrowserConsoleHandler());
        }

        return $response;
    }
}
