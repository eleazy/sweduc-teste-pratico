<?php

namespace App\Routes;

/**
 * Uses league/routes package
 *
 * https://route.thephpleague.com/4.x/usage/
 *
 * @var \League\Route\Router $router
 */

use App\Academico\APIController\AcademicoPublicAPIController;
use App\Academico\APIController\Aluno\AnamneseAlunoController;
use App\Academico\APIController\BiAlunoController;
use App\Academico\APIController\BiOcorrenciaController;
use App\Academico\APIController\BiResponsavelController;
use App\Academico\APIController\BiMarketingController;
use App\Academico\APIController\BiCrmController;
use App\Academico\APIController\BiContasreceberController;
use App\Academico\APIController\BiContaspagarController;
use App\Academico\APIController\BiPedagogicoController;
use App\Academico\APIController\AnoLetivoController;
use App\Academico\APIController\ArquivoController;
use App\Academico\APIController\BoletimController;
use App\Academico\APIController\CarteirinhaAppController;
use App\Academico\APIController\CursoController;
use App\Academico\APIController\FeedController;
use App\Academico\APIController\MatriculaAPIController;
use App\Academico\APIController\NotasController;
use App\Academico\APIController\OcorrenciaController;
use App\Academico\APIController\BoletimPeriodoController;
use App\Academico\APIController\PlanejamentoPedagogicoController;
use App\Academico\APIController\Rematricula\ContratoRematriculaController;
use App\Academico\APIController\Rematricula\DocumentosEnviadosController;
use App\Academico\APIController\Rematricula\GerarRematriculaController;
use App\Academico\APIController\Rematricula\LancamentoRematriculaController;
use App\Academico\APIController\Rematricula\RematriculaController;
use App\Academico\APIController\Rematricula\HistoricoDeRematriculaController;
use App\Academico\APIController\Rematricula\RestartRematriculaController;
use App\Academico\APIController\Rematricula\TituloRematriculaController;
use App\Academico\APIController\SerieController;
use App\Academico\APIController\TituloController;
use App\Academico\APIController\TrabalhoDeAulaController;
use App\Academico\APIController\TrabalhoDeCasaController;
use App\Academico\APIController\TurmaController;
use App\Academico\APIController\UnidadeController;
use App\Academico\Controller\DaoMensagensInstitucionaisController;
use App\Academico\Controller\HistoricoController;
use App\Academico\Controller\MensagemInstitucionalController;
use App\Configuracoes\Controller\DocumentosRematriculaController;
use App\Configuracoes\Controller\SignatarioController;
use App\Configuracoes\Controller\LogoController;
use App\APIController\Core\LocalidadesController;
use App\APIController\Core\PessoaController;
use App\APIController\DocumentoController;
use App\Financeiro\Controller\CondicoesDeParcelamentoAPIController;
use App\Financeiro\Controller\ContasAPIController;
use App\Financeiro\Controller\ContasAReceberAPIController;
use App\Financeiro\Controller\PixController;
use App\Financeiro\Controller\FinanceiroSerieAPIController;
use App\APIController\PerfilController;
use App\Configuracoes\Controller\OcorrenciaConfigController;
use App\Controller\Login\OauthServerController;
use App\Financeiro\Controller\EventoFinanceiroController;
use App\Framework\Middleware\AuthMiddleware;
use App\Framework\Middleware\GuardaMatriculaMiddleware;
use App\Framework\Middleware\ValidateOAuthMiddleware;
use App\Framework\Middleware\VerifyCsrfTokenMiddleware;
use App\Marketing\Controller\ProspeccaoAPIController;
use App\Notificacoes\Controller\FirebaseTokenController;
use App\Opencart\Controller\CheckoutOpencartController;
use App\Opencart\Controller\LoginOpencartController;
use App\Opencart\Controller\OpencartConfigController;
use App\Asaas\Controller\AsaasWebhookController;
use League\Route\RouteGroup;

// OAUTH2
$router->get('/oauth/v2/authorize', [OauthServerController::class, 'authorize'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/oauth/v2/token', [OauthServerController::class, 'accessToken']);

// Público
$router
    ->group('/api/public/v1', function (RouteGroup $route) {
        $route->get('/academico/unidades', [AcademicoPublicAPIController::class, 'unidades']);
        $route->get('/academico/cursos', [AcademicoPublicAPIController::class, 'cursos']);
        $route->get('/academico/cursosTodos', [AcademicoPublicAPIController::class, 'cursosTodos']);
        $route->get('/academico/series', [AcademicoPublicAPIController::class, 'series']);
        $route->get('/academico/turmas', [AcademicoPublicAPIController::class, 'turmas']);
        $route->get('/academico/turnos', [AcademicoPublicAPIController::class, 'turnos']);
        $route->get('/academico/cursosSeriesTurmas', [AcademicoPublicAPIController::class, 'cursosSeriesTurmas']);
        $route->post('/academico/unidades', [AcademicoPublicAPIController::class, 'unidades']);
        $route->post('/academico/cursos', [AcademicoPublicAPIController::class, 'cursos']);
        $route->post('/academico/series', [AcademicoPublicAPIController::class, 'series']);
        $route->post('/academico/turmas', [AcademicoPublicAPIController::class, 'turmas']);
        $route->post('/academico/turnos', [AcademicoPublicAPIController::class, 'turnos']);
    });

/** Integração BiMachine */
$router
    ->group('/api/public/v1', function (RouteGroup $route) {
        $route->get('/bimachine-alunos', [BiAlunoController::class, 'index']);
        $route->get('/bimachine-ocorrencias', [BiOcorrenciaController::class, 'index']);
        $route->get('/bimachine-responsaveis', [BiResponsavelController::class, 'index']);
        $route->get('/bimachine-marketing', [BiMarketingController::class, 'index']);
        $route->get('/bimachine-crm', [BiCrmController::class, 'index']);
        $route->get('/bimachine-contasreceber', [BiContasreceberController::class, 'index']);
        $route->get('/bimachine-contaspagar', [BiContaspagarController::class, 'index']);
        $route->get('/bimachine-pedagogico', [BiPedagogicoController::class, 'index']);
    })->lazyMiddleware(AuthMiddleware::class);

/** Integração Opencart */
$router->post('/api/v1/opencart/user', [LoginOpencartController::class, 'usuario']);
$router->post('/api/v1/opencart/matriculas', [LoginOpencartController::class, 'matriculas']);
$router->post('/api/v1/opencart/order', [CheckoutOpencartController::class, 'pedido']);

/** Integração Asaas - Webhooks */
$router->post('/api/v1/asaas/recebimento', [AsaasWebhookController::class, 'recebimento']);
$router->post('/api/v1/asaas/excluir', [AsaasWebhookController::class, 'excluir']);
$router->post('/api/v1/asaas/alteracao', [AsaasWebhookController::class, 'alteracao']);
$router->post('/api/v1/asaas/criacao', [AsaasWebhookController::class, 'criacao']);
$router->post('/api/v1/asaas/dinheiroDesfeito', [AsaasWebhookController::class, 'dinheiroDesfeito']);
$router->post('/api/v1/asaas/pagamentos', [AsaasWebhookController::class, 'pagamentos']);

/** Core */
$router->group('/api/v1/core', function (RouteGroup $route) {
    $route->get('/pessoas/{id:number}', [PessoaController::class, 'show']);
    $route->post('/pessoas/{id:number}', [PessoaController::class, 'store']);
    $route->get('/estados', [LocalidadesController::class, 'listaEstadosBrasileiros']);
    $route->get('/estados/{id:number}/cidades', [LocalidadesController::class, 'listaCidades']);
})->lazyMiddleware(AuthMiddleware::class);

/** Academico */
// TODO: Revisar rotas dao e .php
$router->post('dao/mensagensinstitucionais.php', [DaoMensagensInstitucionaisController::class, 'action']);
$router->post('documentos_mensagens_salvar', [DaoMensagensInstitucionaisController::class, 'salvar'])
    ->lazyMiddleware(AuthMiddleware::class);

$router->group('/api/v1/academico', function (RouteGroup $route) {
    $route->put('/notas/{id:number}', [NotasController::class, 'update']);
    $route->get('/anos-letivos', [AnoLetivoController::class, 'index']);
    $route->get('/alunos/{id:number}/anamnese', [AnamneseAlunoController::class, 'show']);

    $route->put('/matricula/{id:number}', [MatriculaAPIController::class, 'save']);
    $route->put('/matriculas/{id:number}/trocar-turma', [MatriculaAPIController::class, 'trocarTurma']);
    $route->get('/matriculas/configuracoesMatriculaOnline', [MatriculaAPIController::class, 'configMatriculaOnlineIndex']);
    $route->post('/matriculas/configuracoesMatriculaOnline/save', [MatriculaAPIController::class, 'configMatriculaOnlineSave']);

    $route->post('/lancamento-rematricula', LancamentoRematriculaController::class);

    // DocumentosRematriculaController
    $route->get('/documentos-rematricula', [DocumentosRematriculaController::class, 'all']);
    $route->post('/documentos-rematricula', [DocumentosRematriculaController::class, 'create']);
    $route->get('/documentos-rematricula/{id:number}', [DocumentosRematriculaController::class, 'show']);
    $route->put('/documentos-rematricula/{id:number}', [DocumentosRematriculaController::class, 'update']);
    $route->delete('/documentos-rematricula/{id:number}', [DocumentosRematriculaController::class, 'delete']);

    // Rematricula
    $route->get('/rematriculas', [RematriculaController::class, 'index']);
    $route->post('/rematriculas/gerar-matriculas', GerarRematriculaController::class);
    $route->post('/rematriculas/{id:number}', [RematriculaController::class, 'update']);
    $route->get('/rematriculas/{id:number}', [RematriculaController::class, 'show']);
    $route->patch('/rematriculas/{id:number}/aprova-documentos', [RematriculaController::class, 'aprovaDocs']);
    $route->patch('/rematriculas/{id:number}/rejeita-documentos', [RematriculaController::class, 'rejeitaDocs']);

    // Rematrícula / Exportação para excel
    $route->get('/rematriculas.xls', [RematriculaController::class, 'downloadAsSheet']);

    // Rematrícula / Título
    $route->get('/rematriculas/{id:number}/titulo', TituloRematriculaController::class);

    // Rematrícula / Contrato
    $route->get('/rematriculas/{id:number}/contrato', ContratoRematriculaController::class);

    // Rematrícula / Título
    $route->get('/rematriculas/{id:number}/docs', [RematriculaController::class, 'docsParaAssinar']);

    // Rematrícula / Documentos requeridos
    $route->post('/rematriculas/{id:number}/docs/{docId:number}', [DocumentosEnviadosController::class, 'upload']);

    // Matricula / Documentos requeridos
    $route->post('/matriculas/{id:number}/docs/{docId:number}', [DocumentosEnviadosController::class, 'uploadM']);
    $route->get('/matriculas/docs/{id}', [DocumentosEnviadosController::class, 'serveM']);

    // Rematrícula / Documentos enviados
    $route->get('/rematriculas/{id:number}/documentos-enviados', [DocumentosEnviadosController::class, 'index']);
    $route->patch(
        '/rematriculas/{id:number}/documentos-enviados/{docId:number}/{situacao}',
        [DocumentosEnviadosController::class, 'situacao']
    );

    // Reiniciar rematrícula
    $route->patch('/rematriculas/{id:number}/restart', RestartRematriculaController::class);

    $route->get('/series/{id:number}/turmas', [SerieController::class, 'listarTurmas']);

    // histórico rematrícula / Exportação para excel
    $route->get('/historico-de-rematricula.xls', [HistoricoDeRematriculaController::class, 'downloadAsSheet']);

    $route->post('historico', [HistoricoController::class, 'salvar']);
})->lazyMiddleware(AuthMiddleware::class);

/** Marketing */
$router->group('/api/v1/marketing', function (RouteGroup $route) {
    $route->post('/cadastrar', [
        ProspeccaoAPIController::class,
        'cadastrar'
    ]);
})->lazyMiddleware(AuthMiddleware::class);

/** Financeiro */
$router
    ->group('/api/v1/financeiro', function (RouteGroup $route) {
        $route->get('/eventos-financeiros', [EventoFinanceiroController::class, 'index']); // Não mais usado em RematriculaModal.tsx

        $route->put(
            '/contas-a-receber/{id:number}',
            [ContasAReceberAPIController::class, 'update']
        );

        $route->post('/getSerieFinanceiro', [FinanceiroSerieAPIController::class, 'getData']);
        $route->get('/conta', [ContasAPIController::class, 'index']); // Não mais usado em RematriculaModal.tsx
        $route->put('/conta', [ContasAPIController::class, 'storeOrUpdate']);
        $route->put('/conta/{id:number}', [ContasAPIController::class, 'storeOrUpdate']);

        $route->get('/condicao-de-parcelamento/eventos', [CondicoesDeParcelamentoAPIController::class, 'listEventos']);
        $route->post('/condicao-de-parcelamento', [CondicoesDeParcelamentoAPIController::class, 'store']);
        $route->delete('/condicao-de-parcelamento/{id:number}', [CondicoesDeParcelamentoAPIController::class, 'delete']);
    })
    ->lazyMiddleware(AuthMiddleware::class);

/** Config */
$router
    ->group('/api/v1/config', function (RouteGroup $route) {
        $route->post('/opencart', [OpencartConfigController::class, 'store'])
            ->lazyMiddleware(VerifyCsrfTokenMiddleware::class);

        $route->put('/opencart/{id:number}', [OpencartConfigController::class, 'store'])
            ->lazyMiddleware(VerifyCsrfTokenMiddleware::class);

        $route->delete('/opencart/{id:number}', [OpencartConfigController::class, 'delete']);

        //Logo
        $route->post('/logo', [LogoController::class, 'store'])
            ->lazyMiddleware(VerifyCsrfTokenMiddleware::class);

        // Signatários
        $route->get('/signatarios', [SignatarioController::class, 'index']);
        $route->post('/signatarios', [SignatarioController::class, 'store']);
        $route->delete('/signatarios/{id:number}', [SignatarioController::class, 'delete']);

        // Ocorrências
        $route->post('/ocorrencia', [OcorrenciaConfigController::class, 'store']);
        $route->put('/ocorrencia/{id:number}', [OcorrenciaConfigController::class, 'store']);
        $route->delete('/ocorrencia/{id:number}', [OcorrenciaConfigController::class, 'delete']);
    })
    ->lazyMiddleware(AuthMiddleware::class);

/** Documentos */
$router->get('/api/v1/documento/{id:number}', [DocumentoController::class, 'show'])->lazyMiddleware(AuthMiddleware::class);
$router->post('/api/v1/documento/{id:number}', [DocumentoController::class, 'save'])->lazyMiddleware(AuthMiddleware::class);
$router->put('/api/v1/documento/{id:number}', [DocumentoController::class, 'save'])->lazyMiddleware(AuthMiddleware::class);
$router->delete('/api/v1/documento/{id:number}', [DocumentoController::class, 'delete'])->lazyMiddleware(AuthMiddleware::class);

/** Perfil */
$router->get('/api/v1/img/perfil/{fotoId}', [PerfilController::class, 'showImgById']);
$router->get('/api/v1/perfil/{id}/img', [PerfilController::class, 'showImg']);
$router->post('/api/v1/perfil/{id}/img', [PerfilController::class, 'uploadImg']);

/** Notas inconsistentes */
$router->get('/api/v1/notas-inconsistentes', [NotasController::class, 'notasInconsistentes']);
$router->post('/api/v1/notas-inconsistentes', [NotasController::class, 'consertarNotasInconsistentes']);

// Recursos acadêmicos
$router->get('/api/v1/unidade', [UnidadeController::class, 'index']);
$router->get('/api/v1/unidade/{unidadeId:number}/cursos', [CursoController::class, 'index']);
$router->get('/api/v1/curso/{cursoId:number}/series', [SerieController::class, 'index']);
$router->get('/api/v1/serie/{serieId:number}/turmas', [TurmaController::class, 'index']);

// API V2
$router->get('/api/v2/perfil', [PerfilController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class);

$router->get('/api/v2/matriculas', [MatriculaAPIController::class, 'matriculasUsuario'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/trabalho-de-casa', [TrabalhoDeCasaController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/trabalho-de-aula', [TrabalhoDeAulaController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/planejamento-pedagogico', [PlanejamentoPedagogicoController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/periodos', [BoletimPeriodoController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/periodos/{periodoId}/avaliacoes', [NotasController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/ocorrencias', [OcorrenciaController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/mensagens', [MensagemInstitucionalController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->put('/api/v2/m/{matriculaId}/mensagens/{responsavelLogin}/marcar-como-lida', [MensagemInstitucionalController::class, 'marcarMensagemComoLida'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/feed', [FeedController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/arquivos', [ArquivoController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/titulos', [TituloController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/boletim', [BoletimController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);

$router->put('/api/v2/firebase-token/{token}', [FirebaseTokenController::class, 'store'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class);

$router->get('/api/v2/m/{matriculaId}/carteirinha', [CarteirinhaAppController::class, 'index'])
    ->lazyMiddleware(ValidateOAuthMiddleware::class)
    ->lazyMiddleware(GuardaMatriculaMiddleware::class);
