<?php

declare(strict_types=1);

namespace App\Model\OAuth;

use App\Framework\Model;
use Carbon\Carbon;
use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;

class RefreshToken extends Model implements RefreshTokenEntityInterface
{
    public $timestamps = false;
    protected $keyType = 'string';
    protected $table = 'oauth2_refresh_tokens';

    public function getIdentifier()
    {
        return $this->id;
    }

    public function setIdentifier($identifier)
    {
        $this->id = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken)
    {
        $this->access_token = $accessToken->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return AccessToken::find($this->access_token);
    }

    /**
     * Get the token's expiry date time.
     *
     * @return DateTimeImmutable
     */
    public function getExpiryDateTime()
    {
        return Carbon::parse($this->expires_at)->toImmutable();
    }

    /**
     * Set the date time when the token expires.
     *
     * @param DateTimeImmutable $dateTime
     */
    public function setExpiryDateTime(DateTimeImmutable $dateTime)
    {
        $this->expires_at = $dateTime;
    }
}
