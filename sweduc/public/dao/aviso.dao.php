<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

if ($action == "cadastra") {
    $ok = 0;
    $erro = 0;
    $query = "INSERT INTO aviso (assunto, aviso, id_funcionario, data) VALUES ('" . $assunto . "', '" . nl2br($aviso) . "',  '" . $idfuncionario . "' ,now());";


    $id = 0;
    if ($result = mysql_query($query)) {
        $id = mysql_insert_id();
        echo " Aviso cadastrado com sucesso. | ";
        $msg = "Aviso cadastrado com sucesso.";
        $status = 0;
    } else {
        echo "Erro ao cadastrar aviso ";
        $msg = "Erro ao cadastrar aviso";
        $status = 1;
    }

    if ($id != 0) {
        foreach ($idfuncionarios as $ids) {
            $query = "INSERT INTO aviso_funcionarios (id_aviso, id_funcionarios, situacao) VALUES ('" . $id . "', '" . $ids . "',  0);";

            if ($result = mysql_query($query)) {
                $ok++;

                $msg = " Aviso enviado com sucesso.";
                $status = 0;
            } else {
                $erro++;

                $msg = "Erro ao enviado aviso";
                $status = 1;
            }
        }
        echo $msg;
    }

    $parametroscsv = $id . ',' . $departamento;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == 'ediatar') {
    $query = "UPDATE aviso
                    SET
                    assunto = '" . $assunto . "',
                    aviso = '" . $aviso . "',
                    data = now()
                    WHERE id = " . $idEditar;

    if ($result = mysql_query($query)) {
        echo $id . "| Aviso cadastrado com sucesso.|" . $departamento;
        $msg = "Aviso cadastrado com sucesso.";
        $status = 0;
    } else {
        echo "0|Erro ao cadastrar aviso ";
        $msg = "Erro ao cadastrar aviso";
        $status = 1;
    }

    $query = "DELETE FROM aviso_funcionarios
              WHERE  id_aviso = " . $idEditar;
    if ($result = mysql_query($query)) {
        foreach ($idfuncionarios as $ids) {
            $query = "INSERT INTO aviso_funcionarios (id_aviso, id_funcionarios, situacao) VALUES ('" . $idEditar . "', '" . $ids . "',  0);";
            if ($result = mysql_query($query)) {
                echo $id . "| Aviso cadastrado com sucesso.|" . $departamento;
                $msg = "Aviso cadastrado com sucesso.";
                $status = 0;
            } else {
                echo "0|Erro ao cadastrar aviso ";
                $msg = "Erro ao cadastrar aviso";
                $status = 1;
            }
        }
    }
} elseif ($action == "apagaAviso") {
    $query = "DELETE FROM aviso WHERE id=$id";
    $query2 = "DELETE FROM aviso_pessoa WHERE id_aviso=$id";



    if ($result = mysql_query($query)) {
        if ($query2 = mysql_query($query)) {
            echo "blue|Aviso removido com sucesso.";
            $msg = "Aviso removido com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao remover aviso.";
            $msg = "Erro ao remover aviso.";
            $status = 1;
        }
    } else {
        echo "red|Erro ao remover aviso.";
        $msg = "Erro ao remover aviso.";
        $status = 1;
    }
} elseif ($action == "lido") {
    $query = "UPDATE aviso_funcionarios SET situacao=1 WHERE id=$id";

    if ($result = mysql_query($query)) {
        echo "blue|Aviso alterado com sucesso.";
        $msg = "Aviso alterado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao altera aviso.";
        $msg = "Erro ao altera aviso.";
        $status = 1;
    }
} elseif ($action == 'cadastra_assunto') {
    $query = "INSERT INTO aviso_assunto (assunto) VALUES ('" . $assunto . "')";
    $id = 0;
    if ($result = mysql_query($query)) {
        $id = mysql_insert_id();
        echo "blue| Assunto de aviso cadastrado com sucesso.|" . $departamento;
        $msg = "Aviso cadastrado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar aviso ";
        $msg = "Erro ao cadastrar assunto de aviso";
        $status = 1;
    }
} elseif ($action == 'buscarNaoLido') {
    $query = "SELECT
                count(a.id) quant
            FROM
                aviso_funcionarios af
                    INNER JOIN
                aviso a ON af.id_aviso = a.id
            WHERE af.situacao = 0 and af.id_funcionarios = " . $id_funcionario;
    $result1 = mysql_query($query);
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo $row1['quant'];
    }
} elseif ($action == 'importante') {
    $query = "UPDATE aviso_funcionarios
                    SET
                importante = " . $valor . " where id = " . $id;
    if ($result = mysql_query($query)) {
        $id = mysql_insert_id();
        echo "blue| Marcado imoprtante com sucesso.|" . $departamento;
        $msg = "Marcado imoprtante com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao marcado imoprtante de aviso.";
        $msg = "Erro ao marcado imoprtante de aviso";
        $status = 1;
    }
}
