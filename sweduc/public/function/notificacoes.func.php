<?php

function buscarNotificacoes()
{
    $rowNotificacoes = [];
    $sql = 'SELECT * FROM tipo_notificacoes;';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowNotificacoes[] = $row;
    }

    return $rowNotificacoes;
}

function buscarPessoaNotificacoes()
{
    $rowNotificacoes = [];
    $sql = 'SELECT 
                tn.notificacoes, p.nome, np.email, np.id
            FROM
                notificacoes_pessoas np
                    INNER JOIN
                tipo_notificacoes tn ON np.id_notificacoes = tn.id
                    INNER JOIN
                pessoas p ON np.id_pessoas = p.id';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowNotificacoes[] = $row;
    }

    return $rowNotificacoes;
}

function buscarMotivos()
{
    $rowMotivo = [];
    $sql = 'SELECT 
                *
            FROM
                motivo
                ORDER BY aplicacao,motivo ASC';
    $result = mysql_query($sql);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $rowMotivo[] = $row;
    }

    return $rowMotivo;
}

function cadastroNotificacoes($id_notificacoes, $id_pessoas, $email)
{
    $query = 'INSERT INTO notificacoes_pessoas (id_notificacoes, id_pessoas, email) VALUES (' . $id_notificacoes . ', ' . $id_pessoas . ', "' . $email . '");';

    if ($result = mysql_query($query)) {
        $status = mysql_insert_id();
    } else {
        $status = -1;
    }
    return $status;
}
