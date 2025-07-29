<?php

declare(strict_types=1);

namespace App\Framework\Http;

use App\Framework\ViewRenderer\PlatesRenderer;
use App\Framework\ViewRenderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;

use function App\Framework\resolve;

trait UsePlatesRendererTrait
{
    protected ?RendererInterface $platesRenderer = null;

    /**
     * Render Plates templates
     */
    public function platesView(string $path, array $data = []): ResponseInterface
    {
        if (isset($this->request)) {
            $data['csrfToken'] = $this->request->getAttribute('csrfToken');
        }

        $this->platesRenderer ??= $this->getPlates();

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write(
            $this->platesRenderer->render($path, $data)
        );

        return $response->withHeader('ASSETS_VERSION', resolve('ASSETS_VERSION'));
    }

    /**
     * Render Plates components
     */
    public function platesComponent(string $path, array $data = []): string
    {
        $this->platesRenderer ??= $this->getPlates();
        return $this->platesRenderer->render($path, $data);
    }

    /**
     * Plates instance constructor
     */
    protected function getPlates(): PlatesRenderer
    {
        return new PlatesRenderer();
    }
}
