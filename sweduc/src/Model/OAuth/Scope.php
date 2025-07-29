<?php

declare(strict_types=1);

namespace App\Model\OAuth;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

class Scope implements ScopeEntityInterface
{
    use ScopeTrait;
    use EntityTrait;

    public static function fromString($scope)
    {
        $scopeInstance = new self();
        $scopeInstance->setIdentifier($scope);
        return $scopeInstance;
    }
}
