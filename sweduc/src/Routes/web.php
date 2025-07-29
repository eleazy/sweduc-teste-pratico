<?php

namespace App\Routes;

use League\Route\Router;
use App\Financeiro\Controller\Relatorios\ContatosController;
use App\Financeiro\Controller\Relatorios\SerasaController;
use App\Financeiro\Controller\Relatorios\BalanceteController;
use App\Financeiro\Controller\Relatorios\RetornoRelatorioController;
/**
 * Uses league/routes package
 *
 * https://route.thephpleague.com/4.x/usage/
 *
 * @var Router $router
 */

use App\Academico\Controller\AlunoController;
use App\Academico\Controller\AtaController;
use App\Academico\Controller\BoletimController;
use App\Academico\Controller\MensalidadeController;
use App\Academico\Controller\DisciplinaController;
use App\Academico\Controller\MapaoController;
use App\Academico\Controller\NotaController;
use App\Academico\Controller\PreMatriculaController;
use App\Academico\Controller\TurmaController;
use App\Controller\AcademicoFinanceiro\AlunoController as AcademicoFinanceiroAlunoController;
use App\Academico\APIController\ArquivoController;
use App\Academico\APIController\Rematricula\ContratoRematriculaController;
use App\Academico\APIController\Rematricula\DocumentosEnviadosController;
use App\Controller\BarcodeController;
use App\Academico\Controller\CursoController;
use App\Academico\Controller\OperacoesDeGradeController;
use App\Academico\Controller\ResponsavelController;
use App\Academico\Controller\SerieController;
use App\Academico\Controller\TrabalhoDeCasaController;
use App\Academico\Controller\SolicitacoesController;
use App\Academico\Controller\TransferenciaDeNotasController;
use App\Academico\Controller\MensagemController;
use App\Academico\Controller\PlanoHorarioController;
use App\Configuracoes\Controller\Core\PoliticaConfigController;
use App\Configuracoes\Controller\DocumentoController;
use App\Configuracoes\Controller\Financeiro\CartaoConfigController;
use App\Configuracoes\Controller\OcorrenciaConfigController;
use App\Configuracoes\Controller\OpsGradeMediaController;
use App\Configuracoes\Controller\PerfilFuncionarioController;
use App\Configuracoes\Controller\PeriodoLetivoConfigController;
use App\Configuracoes\Controller\PoliticaFuncionarioController;
use App\Configuracoes\Controller\LogoController;
use App\Configuracoes\Controller\DepartamentoController;
use App\Financeiro\Controller\BoletosController;
use App\Financeiro\Controller\ContasAPagarController;
use App\Financeiro\Controller\ContasAReceberController;
use App\Financeiro\Controller\PixController;
use App\Financeiro\Controller\ReceberCartaoController;
use App\Financeiro\Controller\RecorrenciaController;
use App\Financeiro\Controller\NotificacaoCobrancaController;
use App\Financeiro\Controller\ContasBancoController;
use App\Controller\HomeController;
use App\Controller\LoginController;
use App\Controller\Newsletter\NewsletterController;
use App\Controller\Dashboard\DashboardController;
use App\Controller\Dashboard\DashboardFinanceiroController;
use App\Controller\Dashboard\DashboardConfigsController;
use App\Controller\CalendarioEventosController;
use App\Controller\QrController;
use App\Controller\Publico\MatriculaController;
use App\Controller\RelatorioAcademicoController;
use App\Controller\Sistema\EmailController;
use App\Controller\StaffLoginController;
use App\Estoque\Controller\EstoqueController;
use App\Financeiro\Controller\ImportadorExcelController;
use App\Framework\Middleware\AuthMiddleware;
use App\Framework\Middleware\VerifyCsrfTokenMiddleware;
use App\Framework\Middleware\WebhookMiddleware;
use App\Marketing\Controller\ProspeccaoController;
use App\Opencart\Controller\OpencartConfigController;
use App\AgendaEdu\Controller\AgendaEduConfigController;
use App\Opencart\Controller\RelatorioOpencartController;
use App\Asaas\Controller\AsaasController;
use App\Relatorio\Financeiro\RelatorioDescontoController;
use App\Relatorio\Financeiro\RelatorioDescontoPorBoletoController;
use App\Relatorio\Financeiro\RelatorioContasReceberController;
use App\Relatorio\Marketing\ExportacaoController;
use App\Relatorio\Marketing\ExportacaoRelatoriosController;
use App\Academico\Controller\HistoricoController;
use App\Nlu\Controller\NluController;
use App\Google\Controller\GoogleApiController;
use League\Route\RouteGroup;

// Rotas públicas
$router->get('/pre-matricula', [PreMatriculaController::class, 'show']);
$router->post('/pre-matricula', [PreMatriculaController::class, 'store']);
$router->get('/pre-matricula/finalizado', [PreMatriculaController::class, 'finalizado']);

// Logo e rota de compatibilidade
$router->get('/logo', [HomeController::class, 'logo']);
$router->get("/clientes/{$_ENV['CLIENTE']}/logo.png", [HomeController::class, 'logo']);

$router->get('/qrcode', [QrController::class, 'generate']);
$router->get('/barcode', BarcodeController::class);
$router->get('/login', [LoginController::class, 'showLogin']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/staff-login', [StaffLoginController::class, 'login']);
$router->get('/staff-logout', [StaffLoginController::class, 'logout']);
$router->get('/impersonate', [LoginController::class, 'impersonate'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/impersonate', [LoginController::class, 'impersonate'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/trocar-senha', [LoginController::class, 'showTrocarSenha'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/trocar-senha', [LoginController::class, 'trocarSenha'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/logout', [LoginController::class, 'logout'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/logout', [LoginController::class, 'logout'])->lazyMiddleware(AuthMiddleware::class);

// Calendário de eventos
$router->get('/calendarioEventos/getTurmas', [CalendarioEventosController::class, 'getTurmas'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/calendarioEventos/getEventos', [CalendarioEventosController::class, 'getEventos'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/calendarioEventos/getCalendarPermissions', [CalendarioEventosController::class, 'getPermissions'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/calendarioEventos/saveEvento', [CalendarioEventosController::class, 'saveEvento'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/calendarioEventos/deleteEvento', [CalendarioEventosController::class, 'deleteEvento'])->lazyMiddleware(AuthMiddleware::class);

/**
 * Rotas privadas
 */
$router->get('/', [HomeController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);

/**
 * Acesso publico: Alunos e Responsaveis ======================================
 */
$router->group('/responsaveis', function (RouteGroup $router) {
    $router->get('/matricula', [MatriculaController::class, 'listarComoResponsavel']);
    $router->get('/matricula/{id:number}/titulos', [MatriculaController::class, 'mostrarTitulos']);
    $router->get('/matricula/atualizarDadosCadastrais/{idAluno:number}', [MatriculaController::class, 'atualizarDadosCadastrais']);
    $router->post('/matricula/saveUpdateDadosCadastrais', [MatriculaController::class, 'saveUpdateDadosCadastrais']);
})->lazyMiddleware(AuthMiddleware::class);

$router->get('/matricula/{id:number}/boletim', [MatriculaController::class, 'boletimImpresso'])
    ->lazyMiddleware(AuthMiddleware::class);

$router->get('alunos_alunos_lista.php', [MatriculaController::class, 'listarComoAluno'])
    ->lazyMiddleware(AuthMiddleware::class);

$router->get('/pagar/{id:number}', [ReceberCartaoController::class, 'index'])
    ->lazyMiddleware(AuthMiddleware::class);

$router->post('/pagar/{id:number}', [ReceberCartaoController::class, 'store'])
    ->lazyMiddleware(AuthMiddleware::class)
    ->lazyMiddleware(VerifyCsrfTokenMiddleware::class);

// Endereço da rota não pode ser modificado pois os assets dependem dele por enquanto
$router->get('/lib/boletos/boleto', [BoletosController::class, 'index'])
    ->lazyMiddleware(AuthMiddleware::class);

$router->get('/boleto.pdf', [BoletosController::class, 'pdf'])
    ->lazyMiddleware(AuthMiddleware::class);

$router->get('/boleto/copiarCodigo', [BoletosController::class, 'copiaCodigo'])
    ->lazyMiddleware(AuthMiddleware::class);

/* Roteia arquivos para configurar PWA para o acesso responsável */
$router->get('/service-worker.js', function () {
    $filePath = '../public/js/acessoResp_PWAConfig/service-worker.js';

    if (file_exists($filePath)) {
        header('Content-Type: application/javascript');
        readfile($filePath);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo 'File not found.';
    }
    exit;
});
$router->get('/manifest.json', function () {
    $filePath = '../public/js/acessoResp_PWAConfig/manifest.json';

    if (file_exists($filePath)) {
        header('Content-Type: application/json');
        readfile($filePath);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo 'File not found.';
    }
    exit;
});

// Boletins
$router->get('/alunos_boletim_geral.php', [BoletimController::class, 'boletimGeral'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_geral.php', [BoletimController::class, 'boletimGeral'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_evolucao_geral.php', [BoletimController::class, 'boletimGeralEvolucao'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_evolucao_geral.php', [BoletimController::class, 'boletimGeralEvolucao'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_vermelho.php', [BoletimController::class, 'boletimVermelho'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_vermelho.php', [BoletimController::class, 'boletimVermelho'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_novo_aluno.php', [BoletimController::class, 'boletim'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_novo_aluno.php', [BoletimController::class, 'boletim'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_faltas.php', [BoletimController::class, 'boletimFaltas'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_faltas.php', [BoletimController::class, 'boletimFaltas'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_faltas_x.php', [BoletimController::class, 'boletimFaltasX'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_faltas_x.php', [BoletimController::class, 'boletimFaltasX'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_modelo2.php', [BoletimController::class, 'boletimModelo2'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_modelo2.php', [BoletimController::class, 'boletimModelo2'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_cets.php', [BoletimController::class, 'boletimCets'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_cets.php', [BoletimController::class, 'boletimCets'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_cets_mes.php', [BoletimController::class, 'boletimCetsMes'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_cets_mes.php', [BoletimController::class, 'boletimCetsMes'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_cets_faltas.php', [BoletimController::class, 'boletimCetsFaltas'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_cets_faltas.php', [BoletimController::class, 'boletimCetsFaltas'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_boletim_cets_fundamental.php', [BoletimController::class, 'boletimCetsFundamental'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_boletim_cets_fundamental.php', [BoletimController::class, 'boletimCetsFundamental'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/mensalidades-mes-ano', [MensalidadeController::class, 'mensalidadesMesAno'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/mensalidades-mes-ano', [MensalidadeController::class, 'mensalidadesMesAno'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/bolsas-mes-ano', [MensalidadeController::class, 'bolsasMesAno'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/bolsas-mes-ano', [MensalidadeController::class, 'bolsasMesAno'])->lazyMiddleware(AuthMiddleware::class);

$router->get('/arquivos/{arquivo}', [ArquivoController::class, 'serve'])->lazyMiddleware(AuthMiddleware::class);

$router->post('/evento-mes-ano', [MensalidadeController::class, 'eventoMesAno'])->lazyMiddleware(AuthMiddleware::class);

/**
 * Grupo Configurações
 */
$router->group('/config', function (RouteGroup $route) {
    $route->get('/academico/ocorrencias', [OcorrenciaConfigController::class, 'index']);

    $route->get('/financeiro/cartao', [CartaoConfigController::class, 'index']);
    $route->get('/financeiro/cartao/{id:number}', [CartaoConfigController::class, 'show']);
    $route->post('/financeiro/cartao/{id:number}', [CartaoConfigController::class, 'store']);

    $route->get('/financeiro/notificacaoDeCobranca', [NotificacaoCobrancaController::class, 'index']);
    $route->get('/financeiro/notificacaoDeCobranca/pendentes', [NotificacaoCobrancaController::class, 'pendentes']);
    $route->get('/financeiro/notificacaoDeCobranca/enviados', [NotificacaoCobrancaController::class, 'enviados']);
    $route->get('/financeiro/notificacaoDeCobranca/falhas', [NotificacaoCobrancaController::class, 'falhas']);
    $route->get('/financeiro/notificacaoDeCobranca/logs', [NotificacaoCobrancaController::class, 'logs']);
    $route->get('/financeiro/notificacaoDeCobranca/getNotificacao/{id:number}', [NotificacaoCobrancaController::class, 'getNotificacao']);
    $route->post('/financeiro/notificacaoDeCobranca/storeUpdate', [NotificacaoCobrancaController::class, 'setNotificacao']);
    $route->post('/financeiro/notificacaoDeCobranca/setNotificacaoAtiva', [NotificacaoCobrancaController::class, 'setNotificacaoAtiva']);
    $route->post('/financeiro/notificacaoDeCobranca/getTitulos', [NotificacaoCobrancaController::class, 'getTitulos']);
    $route->post('/financeiro/notificacaoDeCobranca/delete/{id:number}', [NotificacaoCobrancaController::class, 'cancelarEnvio']);

    // DELETAR
    $route->get('/financeiro/notificacaoDeCobranca/agendaEmails', [NotificacaoCobrancaController::class, 'agendaEmails']);

    $route->get('/politicas/funcionarios', [PoliticaFuncionarioController::class, 'index']);
    $route->get('/politicas/funcionarios/{id:number}', [PoliticaFuncionarioController::class, 'edit']);
    $route->post('/politicas/funcionarios', [PoliticaFuncionarioController::class, 'save']);
    $route->post('/politicas/funcionarios/{id:number}', [PoliticaFuncionarioController::class, 'save']);
    $route->post('/politicas/alunos', [PoliticaConfigController::class, 'listarPoliticasAlunos']);
    $route->get('/politicas/responsaveis', [PoliticaConfigController::class, 'listarPoliticasResponsaveis']);

    $route->get('/opencart', [OpencartConfigController::class, 'index']);
    $route->get('/opencart/criar', [OpencartConfigController::class, 'createOrEdit']);
    $route->get('/opencart/{id:number}', [OpencartConfigController::class, 'createOrEdit']);
    $route->post('/opencart', [OpencartConfigController::class, 'store'])->lazyMiddleware(VerifyCsrfTokenMiddleware::class);
    $route->post('/opencart/{id:number}', [OpencartConfigController::class, 'store'])->lazyMiddleware(VerifyCsrfTokenMiddleware::class);

    // AgendaEdu API
    $route->get('/agendaedu/configs', [AgendaEduConfigController::class, 'configs']);
    $route->get('/agendaedu/index', [AgendaEduConfigController::class, 'index']);
    $route->post('/agendaedu/export', [AgendaEduConfigController::class, 'export']);
    $route->post('/agendaedu/update', [AgendaEduConfigController::class, 'update']);
    $route->post('/agendaedu/saveSegmentos', [AgendaEduConfigController::class, 'saveSegmentos']);
    $route->post('/agendaedu/saveTurnos', [AgendaEduConfigController::class, 'saveTurnos']);
    $route->post('/agendaedu/saveCargos', [AgendaEduConfigController::class, 'saveCargos']);

    // Asaas API
    $route->get('/asaas/configs', [AsaasController::class, 'configs']);
    $route->post('/asaas/saveConfigs', [AsaasController::class, 'saveConfigs']);

    // Asaas Notifcações
    $route->get('/asaas/notificacoes', [AsaasController::class, 'notificacoes']);

    // Asaas contas a pagar
    $route->get('/asaas/contas-a-pagar', [AsaasController::class, 'contasAPagar']);
    $route->post('/asaas/contas-a-pagar/saveRelacaoEventos', [AsaasController::class, 'saveRelacaoEventos']);
    $route->post('/asaas/contas-a-pagar/saveTransferencias', [AsaasController::class, 'saveTransferencias']);
    $route->post('/asaas/contas-a-pagar/saveFornecedor', [AsaasController::class, 'saveFornecedor']);
    $route->get('/asaas/contas-a-pagar/searchFornecedores', [AsaasController::class, 'searchFornecedores']);
    $route->post('/asaas/contas-a-pagar/getRelacoesAnteriores', [AsaasController::class, 'getRelacoesAnteriores']);

    //Google Api
    $route->get('/googleapi', [GoogleApiController::class, 'configs']);
    $route->post('/googleapi/saveConfigs', [GoogleApiController::class, 'saveConfigs']);
    $route->get('/googleapi/daily-limit-reached', [GoogleApiController::class, 'checkQuota']);
    $route->get('/googleapi/segundos-gravacao', [GoogleApiController::class, 'getSegundosDeGravacao']);
    $route->get('/googleapi/increment-usage', [GoogleApiController::class, 'incrementUsage']);

    // NLU voice navigator
    $route->get('/smartnavigator', [NluController::class, 'configs']);
    $route->post('/smartnavigator/saveConfigs', [NluController::class, 'saveConfigs']);
    $route->get('/smartnavigator/routes', [NluController::class, 'configsRoutes']);
    $route->post('/smartnavigator/saveRoutes', [NluController::class, 'saveRoutes']);
    $route->get('/smartnavigator/usa-padrao-microfone', [NluController::class, 'padraoMicrofone']);

    // Academico
    $route->get('/periodo-letivo', [PeriodoLetivoConfigController::class, 'index']);
    $route->post('/periodo-letivo/{id:number}', [PeriodoLetivoConfigController::class, 'store']);

    $route->get('/editor-de-documentos', [DocumentoController::class, 'index']);
    $route->get('/editor-de-documentos/{id:number}', [DocumentoController::class, 'edit']);

    $route->get('/ops-grade-medias', [OpsGradeMediaController::class, 'index']);

    // Logo
    $route->get('/logo', [LogoController::class, 'index']);
    $route->post('/logo', [LogoController::class, 'store']);

    // Dashboard
    $route->get('/dashboardPedagogico', [DashboardController::class, 'indexPedagogico'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/dashboard/changePedagogicoConfig', [DashboardController::class, 'changePedagogicoConfig'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/dashboard/deletePedagogicoConfig', [DashboardController::class, 'deletePedagogicoConfig'])->lazyMiddleware(AuthMiddleware::class);
    $route->get('/dashboard/getMediasPorSerie', [DashboardController::class, 'getMediasPorSerie'])->lazyMiddleware(AuthMiddleware::class);
    $route->get('/dashboard/getMatriculasData', [DashboardController::class, 'getMatriculasData'])->lazyMiddleware(AuthMiddleware::class);
    $route->get('/dashboard/getPermissions', [DashboardController::class, 'getPermissions'])->lazyMiddleware(AuthMiddleware::class);

    $route->post('/dashboard/changeFinanceiroConfigs', [DashboardFinanceiroController::class, 'changeFinanceiroConfigs'])->lazyMiddleware(AuthMiddleware::class);
    $route->get('/dashboardFinanceiro', [DashboardFinanceiroController::class, 'indexFinanceiro'])->lazyMiddleware(AuthMiddleware::class);
    $route->get('/dashboard/getFinancialData', [DashboardFinanceiroController::class, 'getFinancialData'])->lazyMiddleware(AuthMiddleware::class);

    $route->get('/dashboardConfigs', [DashboardConfigsController::class, 'configs'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/dashboard/saveConfigs', [DashboardConfigsController::class, 'saveConfigs'])->lazyMiddleware(AuthMiddleware::class);

    // Departamentos
    $route->get('/departamento/index', [DepartamentoController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/departamento/storeOrUpdate', [DepartamentoController::class, 'storeOrUpdate'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/departamento/delete', [DepartamentoController::class, 'delete'])->lazyMiddleware(AuthMiddleware::class);
})->lazyMiddleware(AuthMiddleware::class);

/**
 * Grupo Financeiro
 */
$router->group('/financeiro', function (RouteGroup $route) {
    $route->get('/contasareceber/{id:number}/cartao', [ReceberCartaoController::class, 'index']);
    $route->post('/contasareceber/{id:number}/cartao', [ReceberCartaoController::class, 'store'])
        ->lazyMiddleware(VerifyCsrfTokenMiddleware::class);
    $route->get('/contasareceber/{id:number}/cartao/sucesso', [ReceberCartaoController::class, 'sucesso']);
    $route->get('/contasareceber/{id:number}/cartao/erro', [ReceberCartaoController::class, 'erro']);

    $route->get('/contas-a-receber/lancamento', [ContasAReceberController::class, 'mostrarFormLancamento']);
    $route->get('/contas-a-receber/buscar', [ContasAReceberController::class, 'buscar']);
    $route->get('/contas-a-receber/listar', [ContasAReceberController::class, 'listar']);
    $route->post('/contas-a-receber/listar', [ContasAReceberController::class, 'listar']);

    $route->get('/pix/buscar', [PixController::class, 'buscar']);
    $route->get('/pix/listar', [PixController::class, 'listar']);
    $route->post('/pix/listar', [PixController::class, 'listar']);


    $route->post(
        '/contas-a-receber/relatorios/contatos',
        ContatosController::class
    );

    $route->post(
        '/contas-a-receber/relatorios/serasa',
        SerasaController::class
    );

    $route->get('/contas-a-pagar/buscar', [ContasAPagarController::class, 'buscar']);
    $route->get('/contas-a-pagar/cadastrar', [ContasAPagarController::class, 'cadastrar']);
    $route->get('/contas-a-pagar/listar', [ContasAPagarController::class, 'listar']);
    $route->get('/contas-a-pagar/relatorio', [ContasAPagarController::class, 'relatorio']);
    $route->get('/contas-a-pagar/relatorio-xls', [ContasAPagarController::class, 'relatorioXLS']);
    $route->post('/contas-a-pagar/recibo', [ContasAPagarController::class, 'recibo']);

    $route->get('/recorrencia/recebimentos', [RecorrenciaController::class, 'mostrarRecebimentos']);
    $route->get('/recorrencia/planos', [RecorrenciaController::class, 'mostrarPlanos']);
    $route->get('/recorrencia/assinaturas', [RecorrenciaController::class, 'mostrarAssinaturas']);

    $route->get('/contasapagar/options-autorizar', [ContasAPagarController::class, 'getUsuariosAutorizados']);

    $route->get('/importar-excel/importar', [ImportadorExcelController::class, 'index']);
    $route->post('/importar-excel/importar', [ImportadorExcelController::class, 'store']);
    $route->post('/importar-excel/insert', [ImportadorExcelController::class, 'insert']);

    $route->post('/relatorios/balanceteGrafico', [BalanceteController::class, 'getBalanceteGrafData']);
    $route->get('/contasbanco/listaContasDaUnidade/{unidadeId}', [ContasBancoController::class, 'listaContasDaUnidade']);

    $route->get('/retorno/relatorio', [RetornoRelatorioController::class, 'relatorio']);
})->lazyMiddleware(AuthMiddleware::class);

/**
 * Grupo WebHooks Publicos
 */
$router->group('/webhook', function (RouteGroup $route) {
    $route->post('/santander/boletos', [PixController::class, 'webhookSantander']);
    $route->get('/santander/pix', [PixController::class, 'webhookPixSantander']);
    $route->post('/santander/pix', [PixController::class, 'webhookPixSantander']);
});

/**
 * Grupo Acadêmico
 */
$router->group('/academico', function (RouteGroup $route) {
    $route->get('/aluno/{id:number}', [AlunoController::class, 'mostrarCadastro']);
    $route->get('/responsaveis-autocomplete', [ResponsavelController::class, 'autocomplete']);
    $route->get('/responsaveis-asaas-pendente', [ResponsavelController::class, 'asaasPendente']);
    $route->get('/responsaveis-asaas-apagar-pendentes', [ResponsavelController::class, 'apagaAsaasPendente']);

    // Cursos
    $route->get('/cursos', [CursoController::class, 'listar']);
    $route->post('/cursos', [CursoController::class, 'listar']);

    // Series
    $route->get('/series/cadastrar', [SerieController::class, 'cadastrar']);
    $route->post('/series/cadastrar', [SerieController::class, 'cadastrar']);
    $route->get('/series', [SerieController::class, 'listar']);
    $route->post('/series', [SerieController::class, 'listar']);
    $route->post('/series/cadastrarAtualizarSerie', [SerieController::class, 'cadastrarAtualizarSerie']);

    // Turmas
    $route->get('/turmas', [TurmaController::class, 'index']);
    $route->post('/turmas', [TurmaController::class, 'index']);
    $route->get('/turmas/listar', [TurmaController::class, 'listar']);
    $route->post('/turmas/listar', [TurmaController::class, 'listar']);
    $route->post('/turmas/encerramento', [TurmaController::class, 'encerramento']);

    $route->get('/turmas/options-diario', [TurmaController::class, 'selectOptionsDiarioOnline']);
    $route->get('/disciplinas/options-diario', [DisciplinaController::class, 'selectOptionsDiarioOnline']);

    // Ferramenta clonagem de turma
    $route->post('/grades/clonar-por-ano', [OperacoesDeGradeController::class, 'copiaGradeMedias']);
    $route->post('/grades/clonar-por-turma', [OperacoesDeGradeController::class, 'copiaGradeMediasTurmas']);
    $route->post('/grades/apagar', [OperacoesDeGradeController::class, 'apagaGradeMedias']);

    // Notas
    $route->get('/notas/listar', [NotaController::class, 'listar']);
    $route->post('/notas/listar', [NotaController::class, 'listar']);

    // Mapões
    $route->get('/mapao/notas', [MapaoController::class, 'notas']);
    $route->post('/mapao/notas', [MapaoController::class, 'notas']);
    $route->get('/mapao/notas-2', [MapaoController::class, 'notasV2']);
    $route->post('/mapao/notas-2', [MapaoController::class, 'notasV2']);
    $route->get('/mapao/por-periodo', [MapaoController::class, 'notasPorPeriodo']);
    $route->post('/mapao/por-periodo', [MapaoController::class, 'notasPorPeriodo']);
    $route->get('/mapao/por-disciplina', [MapaoController::class, 'notasPorDisciplina']);
    $route->post('/mapao/por-disciplina', [MapaoController::class, 'notasPorDisciplina']);
    $route->get('/mapao/por-disciplina-2', [MapaoController::class, 'notasPorDisciplinaV2']);
    $route->post('/mapao/por-disciplina-2', [MapaoController::class, 'notasPorDisciplinaV2']);
    $route->get('/mapa-situacao', [MapaoController::class, 'situacao']);
    $route->post('/mapa-situacao', [MapaoController::class, 'situacao']);
    $route->post('/trabalho-de-casa/cadastrar', [TrabalhoDeCasaController::class, 'cadastro']);
    $route->post('/trabalho-de-casa/listar', [TrabalhoDeCasaController::class, 'lista']);
    $route->post('/trabalho-de-casa/listar_quantitativo', [TrabalhoDeCasaController::class, 'listarQuantitativo']);
    $route->post('/trabalho-de-casa/relatorio', [TrabalhoDeCasaController::class, 'relatorio']);
    $route->post('/trabalho-de-casa/relatorio_quantitativo', [TrabalhoDeCasaController::class, 'relatorioQuantitativo']);
    $route->get('/trabalho-de-casa', [TrabalhoDeCasaController::class, 'index']);
    $route->post('/trabalho-de-casa', [TrabalhoDeCasaController::class, 'index']);
    $route->post('/transferencia-de-notas', TransferenciaDeNotasController::class);
    $route->get('/mensagens-institucionais', [MensagemController::class, 'index']);

    // Plano de Horários
    $route->post('/plano-de-horarios/mudar_habilitado', [PlanoHorarioController::class, 'mudarHabilitado']);

    // Relatório de média por turma
    $route->get('/relatorios/notas-por-faixa', [RelatorioAcademicoController::class, 'notasPorTurmaIndex'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getTurmasDisciplinas', [RelatorioAcademicoController::class, 'getTurmasDisciplinas'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getTipoPeriodo', [RelatorioAcademicoController::class, 'getTipoPeriodo'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getAvalicoes', [RelatorioAcademicoController::class, 'getAvaliacoes'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getRelatorio', [RelatorioAcademicoController::class, 'notasPorTurma'])->lazyMiddleware(AuthMiddleware::class);

    // Relatório de alunos por turma
    $route->get('/relatorios/alunos-por-turma', [RelatorioAcademicoController::class, 'alunosPorTurmaIndex'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getAlunosPorTurma', [RelatorioAcademicoController::class, 'getAlunosPorTurmaDados'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getAlunosPorTurmaXls', [RelatorioAcademicoController::class, 'getAlunosPorTurmaXls'])->lazyMiddleware(AuthMiddleware::class);

    // Relatório com listagem de alunos por turma
    $route->get('/relatorios/listagem-alunos-por-turma', [RelatorioAcademicoController::class, 'listagemAlunosPorTurmaIndex'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getListagemAlunosPorTurma', [RelatorioAcademicoController::class, 'getListagemAlunosPorTurmaDados'])->lazyMiddleware(AuthMiddleware::class);

    //Relatório de entrevistas
    $route->post('/relatorios/exportRelatorioEntrevistas', [RelatorioAcademicoController::class, 'exportEntrevistasXls'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/relatorioEntrevistas', [RelatorioAcademicoController::class, 'relatorioEntrevistasHtml'])->lazyMiddleware(AuthMiddleware::class);

    //Relatório de renovação de matriculas
    $route->get('/relatorios/relatorioRenovacaoIndex', [RelatorioAcademicoController::class, 'relatorioRenovacaoIndex'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/relatorios/getRelatorioRenovacao', [RelatorioAcademicoController::class, 'getRelatorioRenovacao'])->lazyMiddleware(AuthMiddleware::class);

    // Solicitações
    $route->post('/solicitacoes/toggleHabilitado', [SolicitacoesController::class, 'toggleHabilitado'])->lazyMiddleware(AuthMiddleware::class);
})->lazyMiddleware(AuthMiddleware::class);

/**
 * Grupo Marketing
 */
$router->group('/marketing', function (RouteGroup $route) {
    $route->get('/buscar', [ProspeccaoController::class, 'buscar']);
    $route->post('/listar', [ProspeccaoController::class, 'listar']);
    $route->get('/cadastrar', [ProspeccaoController::class, 'cadastrar']);
    $route->post('/cadastrar', [ProspeccaoController::class, 'cadastrar']);
})->lazyMiddleware(AuthMiddleware::class);

/**
 * Grupo Estoque
 */
$router->group('/estoque', function (RouteGroup $route) {
    $route->get('/autocomplete-descricoes', [EstoqueController::class, 'autocomplete']);
})->lazyMiddleware(AuthMiddleware::class);

$router->get('/alunos_busca.php', [AlunoController::class, 'busca'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_busca.php', [AlunoController::class, 'busca'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_lista.php', [AlunoController::class, 'listar'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_lista.php', [AlunoController::class, 'listar'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_cadastra.php', [AlunoController::class, 'cadastro'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_cadastra.php', [AlunoController::class, 'cadastro'])->lazyMiddleware(AuthMiddleware::class);

// Atas
$router->get('/academico_turmas_ata.php', [AtaController::class, 'padrao'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/academico_turmas_ata.php', [AtaController::class, 'padrao'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/academico_turmas_ata_reduzida.php', [AtaController::class, 'reduzida'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/academico_turmas_ata_reduzida.php', [AtaController::class, 'reduzida'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/academico_turmas_ata_reduzida_CETS.php', [AtaController::class, 'reduzida_CETS'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/academico_turmas_ata_reduzida_CETS.php', [AtaController::class, 'reduzida_CETS'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/academico_turmas_ata_evolucao.php', [AtaController::class, 'evolucao'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/academico_turmas_ata_evolucao.php', [AtaController::class, 'evolucao'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/academico_turmas_ata_objetivo.php', [AtaController::class, 'objetivo'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/academico_turmas_ata_objetivo.php', [AtaController::class, 'objetivo'])->lazyMiddleware(AuthMiddleware::class);

// Historico
$router->post('/aluno_historico_cadastra', [HistoricoController::class, 'cadastrar'])->lazyMiddleware(AuthMiddleware::class);

/**
 * Grupo Integração Acadêmico/Financeiro
 */
$router->group('/academico-financeiro', function (RouteGroup $route) {
    $route->get(
        '/aluno-ficha-financeira/{alunoId:number}',
        [AcademicoFinanceiroAlunoController::class, 'mostrarFichaFinanceira']
    );
})->lazyMiddleware(AuthMiddleware::class);

/**
 * Grupo Sistema
 */
$router->group('/sistema', function (RouteGroup $route) {
    $route->get('/emails', [EmailController::class, 'pendentes']);
    $route->post('/emails/enviar', [EmailController::class, 'enviarEmail']);
    $route->get('/emails/pendentes', [EmailController::class, 'pendentes']);
    $route->get('/emails/enviados', [EmailController::class, 'enviados']);
    $route->get('/emails/falhas', [EmailController::class, 'falhas']);
    $route->get('/emails/logs', [EmailController::class, 'logs']);

    $route->get('/emails/configs/index', [EmailController::class, 'index']);
    $route->post('/emails/configs', [EmailController::class, 'setEmailConfigs']);
})->lazyMiddleware(AuthMiddleware::class);

/**
 * Newsletter
 */
$router->group('/atualizacoes', function (RouteGroup $route) {
    $route->get('/index', [NewsletterController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
    $route->get('/novas', [NewsletterController::class, 'novas'])->lazyMiddleware(AuthMiddleware::class);
    $route->post('/marcarvisto', [NewsletterController::class, 'marcarvisto'])->lazyMiddleware(AuthMiddleware::class);
})->lazyMiddleware(AuthMiddleware::class);

$router->get('/relatorios/vendas-loja', [RelatorioOpencartController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/documento-academico', [DocumentoController::class, 'documentoAcademico'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/documento-academico-buscar', [DocumentoController::class, 'buscarDocumentoAcademico'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/relatorios/academico/quantidade-alunos', [RelatorioAcademicoController::class, 'quantidadeAlunos'])->lazyMiddleware(AuthMiddleware::class);

$router->get('/visualizar-contrato/{contratoId}', [ContratoRematriculaController::class, 'visualizar'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/rematriculas-documentos-enviados/{id}', [DocumentosEnviadosController::class, 'serve'])->lazyMiddleware(AuthMiddleware::class);

$router->get('/config/perfil-funcionario', [PerfilFuncionarioController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/config/perfil-funcionario', [PerfilFuncionarioController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);

$router->get('/relatorios/marketing/exportacao', [ExportacaoController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/relatorios/marketing/exportacao.xlsx', [ExportacaoController::class, 'xlsx'])->lazyMiddleware(AuthMiddleware::class);

$router->get('/relatorios/marketing/exportacao-relatorios', [ExportacaoRelatoriosController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/relatorios/marketing/exportacao-relatorios.xlsx', [ExportacaoRelatoriosController::class, 'xlsx'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/relatorios/marketing/exportacao-relatorios-anuidades.xlsx', [ExportacaoRelatoriosController::class, 'anuidadeXlsx'])->lazyMiddleware(AuthMiddleware::class);

$router->get(
    '/relatorios/financeiro/descontos',
    [RelatorioDescontoController::class, 'index']
)->lazyMiddleware(AuthMiddleware::class);

$router->post(
    '/relatorios/financeiro/descontos/listar',
    [RelatorioDescontoController::class, 'listar']
)->lazyMiddleware(AuthMiddleware::class);

$router->get(
    '/relatorios/financeiro/descontos-por-boleto',
    [RelatorioDescontoPorBoletoController::class, 'index']
)->lazyMiddleware(AuthMiddleware::class);

$router->post(
    '/relatorios/financeiro/descontos/listar-por-boleto',
    [RelatorioDescontoPorBoletoController::class, 'listar']
)->lazyMiddleware(AuthMiddleware::class);

$router->post('/relatorios/financeiro/contas-a-receber.xls', [RelatorioContasReceberController::class, 'relatorioContasReceberXls'])->lazyMiddleware(AuthMiddleware::class);


// Smart Navigator
$router->post('/gemini/nlu', [NluController::class, 'getMatchesWithAPI'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/local/nlu', [NluController::class, 'getMatchesWithLocal'])->lazyMiddleware(AuthMiddleware::class);
