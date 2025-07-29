<?php

declare(strict_types=1);

namespace App\Model\OAuth\Repository;

use App\Model\OAuth\AccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $accessToken = new AccessToken();
        $accessToken->setIdentifier($accessTokenEntity->getIdentifier());
        $accessToken->setUserIdentifier($accessTokenEntity->getUserIdentifier());
        $accessToken->setClient($accessTokenEntity->getClient());

        foreach ($accessTokenEntity->getScopes() as $scope) {
            $accessToken->addScope($scope);
        }

        $accessToken->setExpiryDateTime($accessTokenEntity->getExpiryDateTime());
        $accessToken->save();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        $accessToken = AccessToken::find($tokenId);
        if ($accessToken) {
            $accessToken->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        $accessToken = AccessToken::find($tokenId);
        return !$accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        $accessToken = new AccessToken();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);

        return $accessToken;
    }
}
