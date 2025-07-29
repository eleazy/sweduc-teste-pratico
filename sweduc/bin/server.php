<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use App\Exception\ClienteNaoConfiguradoException;

use function App\Framework\emit;
use function App\Framework\normalizeRequest;
use function App\Framework\resolve;
use function App\Framework\routeRequest;

$request ??= resolve(ServerRequestInterface::class);
$request = normalizeRequest($request);

try {
    $response = routeRequest($request);
    emit($response);
} catch (ClienteNaoConfiguradoException $exception) {
    echo "<h1>Erro de configuração!</h1>";
}
