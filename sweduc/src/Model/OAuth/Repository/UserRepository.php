<?php

declare(strict_types=1);

namespace App\Model\OAuth\Repository;

use App\Model\OAuth\User;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $usuario = User::where('login', $username)->first();
        if ($usuario && $usuario->verificaSenha($password, true)) {
            return $usuario;
        }

        return;
    }
}
