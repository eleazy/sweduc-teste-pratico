<?php

declare(strict_types=1);

namespace App\Framework\Http;

use App\Framework\Http\UseLiquidRendererTrait;
use App\Framework\Http\UsePlatesRendererTrait;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use stdClass;

abstract class BaseController
{
    use UsePlatesRendererTrait;
    use UseLiquidRendererTrait;

    private ResponseFactoryInterface $responseFactory;
    protected ?ServerRequestInterface $request = null;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Parse a json request
     *
     * @return stdClass
     */
    public function jsonInput(ServerRequestInterface $request = null, $filter = true): stdClass
    {
        if (empty($request)) {
            $request = $this->request;
        }

        $data = json_decode($request->getBody()->getContents() ?: "{}", false, 512, JSON_THROW_ON_ERROR);
        $props = array_keys((array) $data);

        if ($filter) {
            // Filtra strings vazias
            foreach ($props as $prop) {
                if (is_string($data->{$prop}) && trim($data->{$prop}) === "") {
                    unset($data->{$prop});
                }
            }
        }

        return $data;
    }

    /**
     * Parse a form request
     *
     * @return stdClass
     */
    public function queryInput(ServerRequestInterface $request = null): stdClass
    {
        if (empty($request)) {
            $request = $this->request;
        }

        $data = (object) $request->getQueryParams();
        $props = array_keys((array) $data);

        // Transforma parametros sem formato chave valor em verdadeiros
        foreach ($props as $prop) {
            if (is_string($data->{$prop}) && trim($data->{$prop}) === "") {
                $data->{$prop} = true;
            }
        }

        return $data;
    }

    /**
     * Render a empty response
     *
     * @param integer $status
     * @return ResponseInterface
     */
    public function emptyResponse($status = 204): ResponseInterface
    {
        return $this->responseFactory->createResponse($status);
    }

    /**
     * Render a json response
     *
     * @param array $data
     * @param integer $status
     * @return ResponseInterface
     */
    public function jsonResponse($data = [], $status = 200): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse($status)
            ->withAddedHeader('Content-Type', 'application/json');

        if ($data) {
            $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));
        }

        return $response;
    }

    /**
     * Render a error json response
     *
     * @param array $data
     * @param integer $status
     * @return ResponseInterface
     */
    public function errorJsonResponse(string $msg, $status = 400): ResponseInterface
    {
        $data = [
            'message' => $msg
        ];

        return $this->jsonResponse($data, $status);
    }

    /**
     * Render plain text response
     *
     * @param array $data
     * @param integer $status
     * @return ResponseInterface
     */
    public function plainTextResponse(string $string, $status = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status);
        $response->getBody()->write($string);

        return $response;
    }

    /**
     * Redirect to url
     *
     * @param string $path
     * @return ResponseInterface
     */
    public function redirect(string $url, $status = 302, $headers = []): ResponseInterface
    {
        return new RedirectResponse($url, $status, $headers);
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    protected function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }

    protected function createStreamFromResource($resource): StreamInterface
    {
        $streamFactory = new StreamFactory();
        return $streamFactory->createStreamFromResource($resource);
    }
}
