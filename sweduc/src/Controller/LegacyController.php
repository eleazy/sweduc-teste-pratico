<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Core\Usuario;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LegacyController extends Controller
{
  /**
     * Render Plates templates
     *
     * @param string $path
     * @param array $data
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $phpExt = stripos($request->getUri()->getPath(), '.php');
        $path = 'Legacy/' . substr($request->getUri()->getPath(), 0, $phpExt ?: null);
        $data = [
            'usuario' => $request->getAttribute(Usuario::class)
        ];
        return $this->platesView($path, $data);
    }
}
