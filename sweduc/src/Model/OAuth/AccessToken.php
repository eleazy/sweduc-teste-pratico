<?php

declare(strict_types=1);

namespace App\Model\OAuth;

use App\Framework\Model;
use DateTimeImmutable;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

class AccessToken extends Model implements AccessTokenEntityInterface
{
    use AccessTokenTrait;

    public $timestamps = false;
    protected $keyType = 'string';
    protected $table = 'oauth2_access_tokens';

    public function getClient()
    {
        return Client::find($this->client_id);
    }

    public function getExpiryDateTime()
    {
        return $this->expires_at;
    }

    public function getUserIdentifier()
    {
        return $this->user_id;
    }

    public function getScopes()
    {
        $scopes = explode(',', $this->scopes ?? '');
        return array_map(fn($scope) => Scope::fromString($scope), $scopes);
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    public function setIdentifier($identifier)
    {
        $this->id = $identifier;
    }

    public function setExpiryDateTime(DateTimeImmutable $dateTime)
    {
        $this->expires_at = $dateTime;
    }

    public function setUserIdentifier($userId)
    {
        $this->user_id = $userId;
    }

    public function setClient(ClientEntityInterface $client)
    {
        $this->client_id = $client->getIdentifier();
    }

    public function addScope(ScopeEntityInterface $scope)
    {
        $scopes = $this->scopes ? explode(',', $this->scopes) : [];
        $scopes[] = $scope->getIdentifier();
        $this->scopes = implode(',', array_unique($scopes));
    }
}
