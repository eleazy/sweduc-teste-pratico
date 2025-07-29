<?php

declare(strict_types=1);

namespace App\Model\OAuth\Repository;

use App\Model\OAuth\Client;
use Illuminate\Database\RecordsNotFoundException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getClientEntity($clientIdentifier)
    {
        try {
            $client = Client::find($clientIdentifier);
        } catch (RecordsNotFoundException) {
            $client = null;
        }

        return $client;
    }

    /**
     * @inheritDoc
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        try {
            $client = Client::findOrFail($clientIdentifier);
        } catch (RecordsNotFoundException) {
            return false;
        }

        if (
            $client->isConfidential()  === true
            && password_verify($clientSecret, $client->secret) === false
        ) {
            return false;
        }

        return true;
    }
}
