<?php

declare(strict_types=1);

namespace App\Framework\Environment;

use Dotenv\Exception\InvalidPathException;
use Dotenv\Dotenv;
use PDO;

class EnvironmentManager
{
    public static function isProductionEnv(): bool
    {
        if (empty($_SERVER['APP_ENV'])) {
            return false;
        }

        return $_SERVER['APP_ENV'] === 'production';
    }

    public static function isDevelopmentEnv(): bool
    {
        if (empty($_SERVER['APP_ENV'])) {
            return false;
        }

        return $_SERVER['APP_ENV'] === 'development';
    }

    /**
     * Carrega variáveis de ambiente
     */
    public function loadConfig(string $path): void
    {
        $dotenv = Dotenv::createImmutable($path);
        try {
            $dotenv->load();
        } catch (InvalidPathException) {
            //
        }
    }

    /**
     * Carrega variáveis de ambiente do cliente
     */
    public function loadClientConfig(): void
    {
        $host = $_SERVER['HTTP_HOST'];

        $PDO = new PDO(
            "mysql:host={$_SERVER['MASTER_DB_HOST']};dbname={$_SERVER['MASTER_DB_DATABASE']}",
            $_SERVER['MASTER_DB_USER'],
            $_SERVER['MASTER_DB_PASSWORD']
        );

        $stmt = $PDO->prepare('SELECT * FROM clientes WHERE host = :host;');
        $stmt->execute([':host' => $host]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            foreach ($result as $key => $value) {
                $_SERVER[strtoupper($key)] = $value;
                $_ENV[strtoupper($key)] = $value;
            }
        }
    }

    /**
     * Assure required basic configuration is set
     */
    public function isConfigReady(): bool
    {
        $isEnvSet = self::isProductionEnv() || self::isDevelopmentEnv();

        return isset(
            $_SERVER['APP_ENV'],
            $_SERVER['CDN_URL'],
            $_SERVER['DB_DATABASE'],
            $_SERVER['DB_HOST'],
            $_SERVER['DB_PASSWORD'],
            $_SERVER['DB_USER'],
            $_SERVER['OAUTH_ENCRYPTION_KEY'],
            $_SERVER['S3_ACCESS_KEY'],
            $_SERVER['S3_ACCESS_SECRET'],
            $_SERVER['S3_BUCKET'],
            $_SERVER['S3_ENDPOINT'],
            $_SERVER['S3_REGION'],
            $_SERVER['S3_ROOT_PATH'],
        ) && $isEnvSet;
    }
}
