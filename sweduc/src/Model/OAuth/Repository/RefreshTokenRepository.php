<?php

declare(strict_types=1);

namespace App\Model\OAuth\Repository;

use App\Model\OAuth\RefreshToken;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
     /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $token = new RefreshToken();
        $token->setIdentifier($refreshTokenEntity->getIdentifier());
        $token->setAccessToken($refreshTokenEntity->getAccessToken());
        $token->setExpiryDateTime($refreshTokenEntity->getExpiryDateTime());
        $token->save();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        $token = RefreshToken::find($tokenId);
        if ($token) {
            $token->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = RefreshToken::find($tokenId);
        return !$refreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }
}
