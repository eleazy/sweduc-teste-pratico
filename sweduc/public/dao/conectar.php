<?php

use App\Model\Core\Configuracao;

use function App\Framework\app;

require_once __DIR__ . '/../../vendor/autoload.php';

app();

require_once __DIR__ . '/../../src/bootstrap.php';

require_once "funcoes.php";

$agora = date("Y-m-d H:i:s");
$hoje = date("d/m/Y");
$cliente = $_SERVER['CLIENTE'];

extract(Configuracao::completa());

/**
 * Identifica se o cliente está bloqueado
 */
if ($bloqueio !== 0 && $_SERVER['REQUEST_URI'] !== '/bloqueio.php') {
    header("Location: bloqueio.php");
    exit();
}
