<?php

declare(strict_types=1);

namespace App\Framework\ViewRenderer;

interface InlineRendererInterface
{
    /**
     * Renderiza template inline
     *
     * @param string Template
     * @param array Array ou objeto com dados a ser renderizados na página
     *
     * @return string template renderizado
     */
    public function renderInline(string $template, array $data = []): string;
}
