<?php

declare(strict_types=1);

namespace App\Framework\ViewRenderer;

use Liquid\Cache\Local;
use Liquid\Template;

class LiquidRenderer implements RendererInterface, InlineRendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(string $path, $data = []): string
    {
        $template = new Template(__DIR__ . '/../../../View/');
        $template->parseFile($path);
        $template->setCache(new Local());
        return $template->render($data);
    }

    /**
     * @inheritDoc
     */
    public function renderInline(string $tpl, array $data = []): string
    {
        $template = new Template();
        $template->parse($tpl);
        return $template->render($data);
    }
}
