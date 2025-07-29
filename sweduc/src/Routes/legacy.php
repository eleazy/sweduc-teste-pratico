<?php
// phpcs:ignoreFile
namespace App\Routes;

use App\Framework\Middleware\AuthMiddleware;
use App\Controller\LegacyController;

use League\Route\Router;
/**
 * Uses league/routes package
 *
 * https://route.thephpleague.com/4.x/usage/
 *
 * @var Router $router
 */


/**
 * Acadêmico/Alunos
 */
$router->get('/alunos_fichafin.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_fichafin.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_lancamentocoletivo.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_lancamentocoletivo.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/aluno_ficha_anamnese.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/aluno_ficha_anamnese.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/ficha_anamnese_aluno.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/ficha_anamnese_aluno.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);

/**
 * Acadêmico
 */
$router->get('/academico_cursos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/academico_cursos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/academico_faltas.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/academico_faltas.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_entrevistas.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_entrevistas.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_entrevistas_busca.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_entrevistas_busca.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);


/**
 * Financeiro
 */
$router->get('/financeiro_relatoriofluxo2.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_relatoriofluxo2.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/financeiro_relatoriofluxo.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_relatoriofluxo.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/financeiro_contasareceber_lista.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_contasareceber_lista.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/alunos_fichafin_caixarapido.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_fichafin_caixarapido.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/financeiro_retorno.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_retorno.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/financeiro_retorno_arquivos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_retorno_arquivos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/financeiro_lotesRPS.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_lotesRPS.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/financeiro_loteRPS_ver.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_loteRPS_ver.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/financeiro_criaLoteRPS.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/financeiro_criaLoteRPS.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);

/**
 * Estoque
 */
$router->get('/estoque_consulta.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/estoque_consulta.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/estoque_consulta_balanco.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/estoque_consulta_balanco.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);

/**
 * Configurações
 */
$router->get('/config_fin_formaspagamento.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_fin_formaspagamento.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/config_contasbanco_cadastrar.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_contasbanco_cadastrar.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/config_periodos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_periodos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/config_periodos_cursos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_periodos_cursos.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/config_fin_descontocomercial.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_fin_descontocomercial.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/config_smtp.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_smtp.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/config_funcionarios_listar.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_funcionarios_listar.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/config_notas.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/config_notas.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);

/**
 * Responsaveis
 */
$router->get('/alunos_fichafin_resp.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/alunos_fichafin_resp.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/resp_aluno_ficha_anamnese.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/resp_aluno_ficha_anamnese.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);

/**
 * Sistema
 */
$router->get('/documentos_email_segunda_via.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/documentos_email_segunda_via.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/documentos_email.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/documentos_email.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->get('/documentos_mensagens_ler.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
$router->post('/documentos_mensagens_ler.php', LegacyController::class)->lazyMiddleware(AuthMiddleware::class);
