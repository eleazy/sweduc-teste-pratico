<?php

declare(strict_types=1);

namespace Tests\Traits;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

use function App\Framework\resolve;
use function App\Framework\routeRequest;

trait UsesRouter
{
    public function get(UriInterface|string $uri, $queryParameters = [])
    {
        $request = $this
            ->request('GET', $uri)
            ->withQueryParams($queryParameters);

        return $this->routeRequest($request);
    }

    public function post(UriInterface|string $uri, $data = [])
    {
        resolve(StreamFactoryInterface::class);

        $request = $this
            ->request('POST', $uri)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withParsedBody($data);

        return $this->routeRequest($request);
    }

    public function postJson(UriInterface|string $uri, $data = [])
    {
        resolve(StreamFactoryInterface::class);

        $request = $this
            ->request('POST', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->jsonStream($data));

        return $this->routeRequest($request);
    }

    public function putJson(UriInterface|string $uri, $data = [])
    {
        $request = $this->request('PUT', $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->jsonStream($data));

        return $this->routeRequest($request);
    }

    public function delete(UriInterface|string $uri)
    {
        return $this->request('DELETE', $uri);
    }

    public function request(
        string $method,
        UriInterface|string $uri,
        array $serverParams = []
    ): ServerRequestInterface {
        /**
         * @var ServerRequestFactoryInterface
         */
        $requestFactory = resolve(ServerRequestFactoryInterface::class);
        assert($requestFactory instanceof ServerRequestFactoryInterface);

        return $requestFactory->createServerRequest(
            $method,
            $uri,
            $serverParams,
        );
    }

    private function jsonStream(array $content): StreamInterface
    {
        /**
         * @var StreamFactoryInterface
         */
        $streamFactory = resolve(StreamFactoryInterface::class);
        $encodedContent = json_encode($content, JSON_THROW_ON_ERROR);
        return $streamFactory->createStream($encodedContent);
    }

    private function routeRequest(ServerRequestInterface $request)
    {
        global $_COOKIE, $_GET, $_POST, $_REQUEST;

        $_COOKIE = $request->getCookieParams() ?? [];
        $_GET = $request->getQueryParams() ?? [];
        $_POST = $request->getParsedBody() ?? [];
        $_REQUEST = array_merge($_GET, $_POST);

        return routeRequest($request);
    }
}
