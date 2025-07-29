<?php

declare(strict_types=1);

if (($_SERVER['APP_ENV'] ?? 'dev') == 'production') {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
}

ini_set('display_errors', $_SERVER['DISPLAY_ERRORS']);
ini_set('log_errors', $_SERVER['LOG_ERRORS']);

setlocale(LC_MONETARY, ['pt_BR.UTF-8', 'pt_BR']);
date_default_timezone_set('America/Sao_Paulo');

$conn = mysql_connect($_SERVER['DB_HOST'], $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD']);
if (!$conn) {
    die('Não foi possível conectar: ' . mysql_error());
}

mysql_set_charset('utf8', $conn);
mysql_select_db($_SERVER['DB_DATABASE'], $conn);

// MENSAGENS EM PORTUGUÊS
mysql_query("SET lc_time_names = 'pt_BR';");

// MENSAGENS DE ERRO EM PORTUGUÊS
mysql_query("SET lc_messages = 'pt_BR';");

require_once __DIR__ . '/../public/dao/conectar.php';
