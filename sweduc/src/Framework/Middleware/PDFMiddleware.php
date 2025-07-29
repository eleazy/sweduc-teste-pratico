<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use Mpdf\Mpdf;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PDFMiddleware implements MiddlewareInterface
{
    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'tempDir' => '/tmp/boletins'
        ]);

        $mpdf->WriteHTML($response->getBody());

        $response = $this->responseFactory
            ->createResponse(200)
            ->withHeader('Content-Disposition', 'inline;filename="boleto.pdf"')
            ->withHeader('Content-type', 'application/pdf')
            ->withHeader('Cache-Control', 'public');

        $response->getBody()->write($mpdf->Output('documento', 'S'));

        return $response;
    }
}
