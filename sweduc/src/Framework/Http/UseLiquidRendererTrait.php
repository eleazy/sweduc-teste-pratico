<?php

declare(strict_types=1);

namespace App\Framework\Http;

use App\Framework\ViewRenderer\LiquidRenderer;
use Psr\Http\Message\ResponseInterface;

trait UseLiquidRendererTrait
{
    protected ?LiquidRenderer $liquidRenderer = null;

    /**
     * Render Liquid templates
     */
    public function liquidView(string $path, array $data = []): ResponseInterface
    {
        $this->liquidRenderer ??= new LiquidRenderer();

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write(
            $this->liquidRenderer->render($path, $data)
        );

        return $response;
    }

    /**
     * Render Liquid templates from string data
     *
     * @param string $path
     */
    public function liquidInlineView(string $template, array $data = []): ResponseInterface
    {
        $this->liquidRenderer ??= new LiquidRenderer();

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write(
            $this->liquidRenderer->renderInline($template, $data)
        );

        return $response;
    }
}
