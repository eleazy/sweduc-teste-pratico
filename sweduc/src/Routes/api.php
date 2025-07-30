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



/** Academico */
// TODO: Revisar rotas dao e .php

$router->group('/api/v1/academico', function (RouteGroup $route) {

    $route->post('historico', [HistoricoController::class, 'salvar']);
})->lazyMiddleware(AuthMiddleware::class);


/** Config */
$router
    ->group('/api/v1/config', function (RouteGroup $route) {

        //Logo
        $route->post('/logo', [LogoController::class, 'store'])
            ->lazyMiddleware(VerifyCsrfTokenMiddleware::class);


    })
    ->lazyMiddleware(AuthMiddleware::class);
