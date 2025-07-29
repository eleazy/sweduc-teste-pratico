<?php

declare(strict_types=1);

namespace App\Framework\Http;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ErrorRendererInterface
{
    public function handleThrowable(Throwable $exception, $hasAcceptJsonHeader): ResponseInterface;
}
