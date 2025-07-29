<?php

declare(strict_types=1);

namespace App\Framework\ViewRenderer;

interface RendererInterface
{
    /**
     * Renderiza template
     *
     * @param string Caminho do template a partir da pasta View
     * @param array Array ou objeto com dados a ser renderizados na página
     *
     * @return string template renderizado
     */
    public function render(string $templatePath, array $data = []): string;
}
