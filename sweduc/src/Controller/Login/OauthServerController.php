<?php

declare(strict_types=1);

namespace App\Controller\Login;

use App\Controller\Controller;
use App\Model\Core\Usuario;
use App\Model\OAuth\Repository\UserRepository;
use App\Model\OAuth\User;
use Carbon\CarbonInterval;
use Defuse\Crypto\Key;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class OauthServerController extends Controller
{
    protected AuthorizationServer $server;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ClientRepositoryInterface $clientRepository,
        ScopeRepositoryInterface $scopeRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        UserRepository $userRepository,
        protected LoggerInterface $logger
    ) {
        parent::__construct($responseFactory);

        // Init our repositories
        $privateKey = __DIR__ . '/../../../storage/.oauth_private.key';
        $encryptionKey = $_SERVER['OAUTH_ENCRYPTION_KEY'];

        // Setup the authorization server
        $this->server = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            Key::loadFromAsciiSafeString($encryptionKey)
        );

        // Enable the authentication code grant
        $accessGrant = new AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            CarbonInterval::minutes(10) // authorization codes will expire after 10 minutes
        );
        $accessGrant->setRefreshTokenTTL(CarbonInterval::year());
        $this->server->enableGrantType($accessGrant);

        // Enable refresh token grant
        $refreshGrant = new RefreshTokenGrant($refreshTokenRepository);
        $refreshGrant->setRefreshTokenTTL(CarbonInterval::year());
        $this->server->enableGrantType($refreshGrant, CarbonInterval::year());

        // Enable password grant
        $passwordGrant = new PasswordGrant($userRepository, $refreshTokenRepository);
        $passwordGrant->setRefreshTokenTTL(CarbonInterval::year());
        $this->server->enableGrantType($passwordGrant, CarbonInterval::year());
    }

    public function authorize(ServerRequestInterface $request)
    {
        $response = $this->createResponse();

        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->server->validateAuthorizationRequest($request);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.
            $usuario = $request->getAttribute(Usuario::class);
            $userEntity = new User($usuario->toArray());

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($userEntity); // an instance of UserEntityInterface

            // if ($request->getMethod() == 'GET') {
            //     $queryParams = $request->getQueryParams();
            //     $scopes = isset($queryParams['scope'])
            //                  ? explode(" ", $queryParams['scope'])
            //                  : ['default'];

            //     return $this->container
            //                 ->get(Twig::class)
            //                 ->render($response,
            //                          "authorize.twig",
            //                          [
            //                              'pageTitle' => 'Authorize',
            //                              'clientName' => $authRequest->getClient()->getName(),
            //                              'scopes' => $scopes
            //                          ]);
            // }

            // $params = (array)$request->getParsedBody();

            // // Once the user has approved or denied
            // // the client update the status
            // // (true = approved, false = denied)
            // $authorized = $params['authorized'] == 'true';
            // $authRequest->setAuthorizationApproved($authorized);

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            return $this->server->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $exception) {
            $this->logger->warning("$exception", compact('exception'));

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);
        } catch (Exception $exception) {
            $this->logger->warning("$exception", compact('exception'));

            // Unknown exception
            $response->getBody()->write($exception->getMessage());
            return $response->withStatus(500);
        }
    }

    public function accessToken(ServerRequestInterface $request)
    {
        $this->logger->debug('Endpoint de token de acesso oauth acessado');
        $response = $this->createResponse();

        try {
            // Try to respond to the request
            return $this->server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            $this->logger->warning('Exeção ao gerar token de acesso oauth', compact('exception'));

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);
        } catch (Exception $exception) {
            $this->logger->warning('Exeção ao gerar token de acesso oauth', compact('exception'));

            // Unknown exception
            $response->getBody()->write($exception->getMessage());
            return $response->withStatus(500);
        }
    }
}
