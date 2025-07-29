<?php

function getConfig($name)
{
    $res = mysql_query(
        "SELECT valor FROM configuracoes_kv WHERE chave = '$name' LIMIT 1;"
    );
    $row = mysql_fetch_array($res);

    if ($row) {
        return $row[0];
    }

    return null;
}

function setConfig($name, $value)
{
    $res = mysql_query(
        "REPLACE INTO configuracoes_kv (chave, valor) values ('$name', '$value');"
    );
}
