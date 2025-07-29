<?php

declare(strict_types=1);

namespace App\Controller;

use League\Route\Http\Exception\UnprocessableEntityException;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Psr\Http\Message\ServerRequestInterface;

class BarcodeController extends Controller
{
    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();

        if (empty($cod = $params['cod'])) {
            throw new UnprocessableEntityException("Cod missing");
        }

        $barcode = (new BarcodeGeneratorSVG())->getBarcode($cod, BarcodeGeneratorSVG::TYPE_EAN_13);
        return $this->plainTextResponse($barcode)
            ->withHeader('Content-Type', 'image/svg+xml');
    }
}
