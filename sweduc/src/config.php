<?php

use App\Framework\Http\ErrorRendererController;
use App\Framework\Http\ErrorRendererInterface;
use App\Model\OAuth\Repository\AccessTokenRepository;
use App\Model\OAuth\Repository\AuthCodeRepository;
use App\Model\OAuth\Repository\ClientRepository;
use App\Model\OAuth\Repository\RefreshTokenRepository;
use App\Model\OAuth\Repository\ScopeRepository;
use App\Framework\Router\ControllerAwareStrategy;
use App\Logger\AppLogger;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use League\Event\EventDispatcher;
use League\Event\EventDispatchingListenerRegistry;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use League\Route\Strategy\ApplicationStrategy;
use Monolog\Logger;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

use function DI\create;
use function DI\get;

return [
    /**
     * Constante enviada no header HTTP das respostas, ao mudar a versão
     * um banner aparecerá para o usuário pedindo para recarregar a página
     */
    'ASSETS_VERSION' => 36,
    Logger::class => create(AppLogger::class),
    LoggerInterface::class => get(AppLogger::class),
    ResponseInterface::class => fn ($container) => new Response(),
    ServerRequestFactoryInterface::class => create(ServerRequestFactory::class),
    ServerRequestInterface::class => ServerRequestFactory::fromGlobals(),
    ResponseFactoryInterface::class => create(ResponseFactory::class),
    StreamFactoryInterface::class => create(StreamFactory::class),
    NumberFormatter::class => fn() => new NumberFormatter('pt_BR', NumberFormatter::DEFAULT_STYLE),
    EventDispatchingListenerRegistry::class => get(EventDispatcher::class),
    EventDispatcherInterface::class => get(EventDispatchingListenerRegistry::class),
    ClientInterface::class => get(Client::class),
    RequestFactoryInterface::class => get(RequestFactory::class),
    ApplicationStrategy::class => get(ControllerAwareStrategy::class),
    ClientRepositoryInterface::class => get(ClientRepository::class),
    ScopeRepositoryInterface::class => get(ScopeRepository::class),
    AccessTokenRepositoryInterface::class => get(AccessTokenRepository::class),
    AuthCodeRepositoryInterface::class => get(AuthCodeRepository::class),
    RefreshTokenRepositoryInterface::class => get(RefreshTokenRepository::class),
    ResourceServer::class => create()->constructor(
        get(AccessTokenRepositoryInterface::class),
        __DIR__ . '/../storage/.oauth_public.key'
    ),
    ErrorRendererInterface::class => get(ErrorRendererController::class),
];
