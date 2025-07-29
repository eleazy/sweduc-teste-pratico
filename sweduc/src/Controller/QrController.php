<?php

declare(strict_types=1);

namespace App\Controller;

use Endroid\QrCode\QrCode;
use Psr\Http\Message\ServerRequestInterface;

class QrController extends Controller
{
    public function generate(ServerRequestInterface $request)
    {
        // use Endroid\QrCode\QrCode;
        $params = $request->getQueryParams();

        $qrCode = new QrCode($params['data'] ?? '');
        return $this->plainTextResponse($qrCode->writeString())
            ->withHeader('Content-Type', $qrCode->getContentType());
    }
}
