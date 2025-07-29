<?php

declare(strict_types=1);

namespace App\Model\OAuth;

use App\Model\Core\Usuario;
use League\OAuth2\Server\Entities\UserEntityInterface;

class User extends Usuario implements UserEntityInterface
{
    /**
     * Return the user's identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->id;
    }
}
