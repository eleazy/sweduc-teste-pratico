<?php

declare(strict_types=1);

namespace App\Framework\Http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Psr\Http\Message\ResponseInterface;

class ConditionalStreamEmitter implements EmitterInterface
{
    public function emit(ResponseInterface $response): bool
    {
        if (!$response->hasHeader('Content-Disposition') && !$response->hasHeader('Content-Range')) {
            return false;
        }

        $emitter = new SapiStreamEmitter();
        return $emitter->emit($response);
    }
}
