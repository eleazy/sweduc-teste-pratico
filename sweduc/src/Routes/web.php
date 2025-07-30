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

/**
 * Rotas privadas
 */
$router->get('/', [HomeController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);


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

/**
 * Grupo Configurações
 */
$router->group('/config', function (RouteGroup $route) {

    $route->get('/politicas/funcionarios', [PoliticaFuncionarioController::class, 'index']);
    $route->get('/politicas/funcionarios/{id:number}', [PoliticaFuncionarioController::class, 'edit']);
    $route->post('/politicas/funcionarios', [PoliticaFuncionarioController::class, 'save']);
    $route->post('/politicas/funcionarios/{id:number}', [PoliticaFuncionarioController::class, 'save']);
    $route->post('/politicas/alunos', [PoliticaConfigController::class, 'listarPoliticasAlunos']);
    $route->get('/politicas/responsaveis', [PoliticaConfigController::class, 'listarPoliticasResponsaveis']);


    $route->get('/editor-de-documentos', [DocumentoController::class, 'index']);
    $route->get('/editor-de-documentos/{id:number}', [DocumentoController::class, 'edit']);

    $route->get('/ops-grade-medias', [OpsGradeMediaController::class, 'index']);

    // Logo
    $route->get('/logo', [LogoController::class, 'index']);
    $route->post('/logo', [LogoController::class, 'store']);
})->lazyMiddleware(AuthMiddleware::class);


/**
 * Grupo Acadêmico
 */
$router->group('/academico', function (RouteGroup $route) {
    $route->get('/aluno/{id:number}', [AlunoController::class, 'mostrarCadastro']);

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

})->lazyMiddleware(AuthMiddleware::class);





$router->get('/alunos_busca.php', [AlunoController::class, 'busca'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_busca.php', [AlunoController::class, 'busca'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_lista.php', [AlunoController::class, 'listar'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_lista.php', [AlunoController::class, 'listar'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_cadastra.php', [AlunoController::class, 'cadastro'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_cadastra.php', [AlunoController::class, 'cadastro'])->lazyMiddleware(AuthMiddleware::class);




$router->post('/documento-academico', [DocumentoController::class, 'documentoAcademico'])->lazyMiddleware(AuthMiddleware::class);
$router->get('/documento-academico-buscar', [DocumentoController::class, 'buscarDocumentoAcademico'])->lazyMiddleware(AuthMiddleware::class);


$router->get('/config/perfil-funcionario', [PerfilFuncionarioController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/config/perfil-funcionario', [PerfilFuncionarioController::class, 'index'])->lazyMiddleware(AuthMiddleware::class);
