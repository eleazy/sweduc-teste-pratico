<?php

namespace App\Model;

require_once __DIR__ . '/../../public/dao/conectar.php';

use PDO;
use PDOException;

/**
 * Conexão com o banco
 *
 * @deprecated version
 */
class DbConnect
{
    public function connect(?string $database = null, ?string $host = null, ?string $user = null, ?string $password = null)
    {
        $db = null;
        $database ??= $_SERVER['DB_DATABASE'];
        $host ??= $_SERVER['DB_HOST'];
        $user ??= $_SERVER['DB_USER'];
        $password ??= $_SERVER['DB_PASSWORD'];

        // Conectando ao mysql
        try {
            $db = new PDO("mysql:host=$host;dbname=$database;", $user, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // PDO fetch docs: http://php.net/manual/en/pdostatement.fetch.php
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // checa por erro de conexão
            echo $e->getMessage();
        }
        return $db;
    }
}
