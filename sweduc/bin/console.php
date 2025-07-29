<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application as ConsoleApplication;
use App\Framework\Application;

$app = new Application();

$console = new ConsoleApplication();
$console->add(new App\Command\ProcessEmailCommand());
$console->add(new App\Opencart\Command\SyncStoreOrderAttributesCommand());
$console->add(new App\Command\ImportCSVAlunoCommand());
$console->add(new App\Command\ImportCSVEmpresaCommand());
$console->add(new App\Command\Oauth\GenerateKeysCommand());
$console->add(new App\Command\ExportCSVCommand());
$console->add(new App\Command\LimparCacheNotasCommand());
$console->add(new App\Command\ReconstruirTitulosCommand());
$console->add(new App\Importador\EscolarPlus\Command\ImportadorFinanceiroCommand());
$console->add(new App\Importador\WPensar\Command\ImportarCommand());
$console->add(new App\Command\ProcessaNotificacaoCobranca());
$console->add(new App\Command\AsaasUpdateDiscountCommand());
$console->add(new App\Command\AsaasProcessContasAPagarCommand()); // not used
$console->add(new App\Command\AsaasCorrigeValoresCommand()); // not used

$console->add(new App\Command\AsaasImportCobrancasCommand());

chdir(__DIR__ . '/../public');
$console->run();
