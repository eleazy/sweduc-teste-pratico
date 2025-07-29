<?php

declare(strict_types=1);

namespace App\Model\OAuth\Repository;

use App\Model\OAuth\AuthCode;
use App\Model\OAuth\Scope;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $authCode = new AuthCode();
        $authCode->setIdentifier($authCodeEntity->getIdentifier());
        $authCode->setUserIdentifier($authCodeEntity->getUserIdentifier());
        $authCode->setClient($authCodeEntity->getClient());

        foreach ($authCodeEntity->getScopes() as $scope) {
            $authCode->addScope(Scope::fromString($scope));
        }

        $authCode->revoked = 0;
        $authCode->setExpiryDateTime($authCodeEntity->getExpiryDateTime());
        $authCode->save();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        $authCode = AuthCode::find($codeId);
        $authCode->revoked = 1;
        $authCode->save();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $authCode = AuthCode::find($codeId);
        return !$authCode || $authCode->revoked;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCode();
    }
}
