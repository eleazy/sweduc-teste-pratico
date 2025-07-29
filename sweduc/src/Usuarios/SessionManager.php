<?php

namespace App\Usuarios;

class SessionManager
{
    public function start()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $this->startCsrfToken();
    }

    public function startCsrfToken()
    {
        if (!isset($_SESSION['csrfToken'])) {
            $this->resetCsrfToken();
        }
    }

    public function resetCsrfToken()
    {
        $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
    }

    /**
     * Test if the received CSRF token is valid
     *
     * @param string $receivedToken User submited token
     * @return bool Returns TRUE when the two tokens are equal, FALSE otherwise.
     */
    public function validateCsrfToken(string $receivedToken): bool
    {
        $userToken = $_SESSION['csrfToken'];
        return hash_equals($userToken, $receivedToken);
    }
}
