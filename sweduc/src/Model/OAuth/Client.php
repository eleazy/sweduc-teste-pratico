<?php

declare(strict_types=1);

namespace App\Model\OAuth;

use App\Framework\Model;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client extends Model implements ClientEntityInterface
{
    protected $table = 'oauth2_clients';
    protected $keyType = 'string';

    /**
     * Generate a random secret token
     *
     * @return string Random 32 length string
     */
    public static function generateSecret(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Get the client's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * Get the client's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the registered redirect URI (as a string).
     *
     * Alternatively return an indexed array of redirect URIs.
     *
     * @return string|string[]
     */
    public function getRedirectUri()
    {
        return $this->redirect;
    }

    /**
     * Returns true if the client is confidential.
     *
     * @return bool
     */
    public function isConfidential()
    {
        return !empty($this->secret);
    }
}
