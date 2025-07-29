<?php

use App\Model\Core\Usuario;

require 'conectar.php';
require 'logs.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php';
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_REQUEST);
foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}
$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $query  = "SELECT COUNT(*) as cnt FROM ocorrencias WHERE ocorrencia='$ocorrencia'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];
    $id = 0;
    if ($cnt == 0) {
        $query  = "INSERT INTO ocorrencias (ocorrencia) VALUES ('$ocorrencia');";
        if ($result = mysql_query($query)) {
            $id = mysql_insert_id();
            echo $id . "|Ocorrência cadastrada com sucesso.|" . $ocorrencia;
            $msg = 'Ocorrência cadastrada com sucesso';
            $status = 0;
        } else {
            echo "0|Erro ao cadastrar a ocorrência!|" . $ocorrencia;
            $msg = 'Erro ao cadastrar a ocorrência!';
            $status = 1;
        }
        $parametroscsv = $id . ',' . $ocorrencia;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else {
        echo "0|Ocorrência $ocorrencia já existe!|" . $ocorrencia;
        $msg = "Ocorrência $ocorrencia já existe!";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $valor;
} elseif ($action == "apaga") {
    $query  = "DELETE FROM ocorrencias WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Ocorrência removida.";
    } else {
        echo "red|Erro ao remover a ocorrência.";
    }
    if ($result = mysql_query($query)) {
        echo "blue|Ocorrência removida com sucesso.";
        $msg = "Ocorrência removida com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao remover a ocorrência.";
        $msg = "Erro ao remover a ocorrência.";
        $status = 1;
    }
    $parametroscsv = $id;
} elseif ($action == "recebeOcorrencias") {
    $query1 = "SELECT * FROM ocorrencias ORDER BY ocorrencia ASC";
    $result1 = mysql_query($query1);
    $tem = 0;
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo '<option value="' . $row1['id'] . '">' . $row1['ocorrencia'] . '</option>';
    }
} elseif ($action == "datainicialalunos") {
    $query  = "UPDATE configuracoes SET datainicialocorrenciasalunos='" . $datainicialocorrenciasalunos . "'";
    if ($result = mysql_query($query)) {
        echo "blue|Data inicial de busca de ocorrência para login 'aluno' atualizada.";
    } else {
        echo "red|Erro ao alterar data.";
    }
} elseif ($action == "addlista") {
    if ($idlista > 0 && $idocorrencia > 0) {
        $sql = "INSERT INTO ocorrencias_listagem
                        (idtipolistagem,idocorrencia)
                VALUES (" . $idlista . ", " . $idocorrencia . ");";
        if ($result = mysql_query($sql)) {
            $id = mysql_insert_id();
            echo $id . "|Lista cadastrada com sucesso.|" . $idocorrencia;
            $msg = 'Lista cadastrada com sucesso';
            $status = 0;
        } else {
            echo "0|Erro ao cadastrar a Lista!|" . $idocorrencia;
            $msg = 'Erro ao cadastrar a Lista!';
            $status = 1;
        }
    } else {
        echo "0|Erro ao cadastrar a Lista!| Deve escolher uma lista e uma ocorrência ";
        $msg = 'Erro ao cadastrar a Lista!| Deve escolher uma lista e uma ocorrência!';
        $status = 1;
    }
} elseif ($action == "apagaItemLista") {
    $sql = "DELETE FROM ocorrencias_listagem WHERE id = " . $id;

    if ($result = mysql_query($sql)) {
        $id = mysql_insert_id();
        echo "blue|Removido com sucesso a Ocorrência da Lista.";
        $msg = 'Lista cadastrada com sucesso';
        $status = 0;
    } else {
        echo "0| Erro ao removido a Ocorrência da Lista.";
        $msg = 'Erro ao cadastrar a Lista!';
        $status = 1;
    }
} elseif ($action == "diarioOcorrencia") {
    $data = $_REQUEST['data'] ?? date('Y-m-d');
    $funcionario = Usuario::fromSession($_SESSION)->funcionario;
    $funcionarioId = $funcionario->id ?? 0;

    if ($cond == 'true') {
        $query = "INSERT
            INTO alunos_ocorrencias
            (
                idunidade,
                idaluno,
                idocorrencia,
                iddepartamento,
                iddisciplina,
                idfuncionario,
                nummatricula,
                datahora,
                assunto
            ) VALUES (
                '$unidade',
                '$aluno',
                '$ocorrencia',
                5,
                '$disciplina',
                '$funcionarioId',
                '$nummartricula',
                '$data',
                ''
            );";
    } else {
        $query = "DELETE FROM alunos_ocorrencias WHERE id = '$idlista'";
    }

    if ($result = mysql_query($query)) {
        $id = mysql_insert_id();
        echo $id;
        $msg = 'Lista cadastrada com sucesso';
        $status = 0;
    } else {
        $error = mysql_error();
        $msg = 'Erro ao cadastrar a Lista!';
        $status = 1;
    }
}

if ($action == "ocorrenciasJson") {
    $query = "SELECT
                  ao.id,
                  o.ocorrencia,
                  ao.idfuncionario,
                  a.id AS aluno_id,
                  p.id AS pessoa_id,
                  p.nome,
                  d.id as disciplina_id,
                  COALESCE(d.disciplina, '') as disciplina,
                  f.id as professor_id,
                  COALESCE(fp.nome, '') as professor,
                  am.nummatricula,
                  am.idunidade as unidade_id,
                  am.turmamatricula as turma_id,
                  dep.id as departamento_id,
                  COALESCE(dep.departamento, '') as departamento,
                  ao.assunto,
                  DATE_FORMAT(datahora, '%d/%m/%Y - %H:%i') AS 'dthora'
              FROM
                  alunos_ocorrencias ao
                  LEFT JOIN ocorrencias o ON o.id = ao.idocorrencia
                  LEFT JOIN disciplinas d ON d.id = ao.iddisciplina
                  LEFT JOIN departamentos dep ON dep.id = ao.iddepartamento
                  LEFT JOIN funcionarios f ON f.id = ao.idfuncionario
                  LEFT JOIN alunos_matriculas am ON am.nummatricula = ao.nummatricula
                  LEFT JOIN alunos a ON a.id = am.idaluno
                  LEFT JOIN pessoas p ON p.id = a.idpessoa
                  LEFT JOIN pessoas fp ON fp.id = f.idpessoa
              WHERE
                  ao.idaluno = $idaluno
              GROUP BY ao.id
              ORDER BY ao.id DESC";
    $result = mysql_query($query);

    $ocorrencias = [];
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $ocorrencias[] = $row;
    }

    echo json_encode($ocorrencias, JSON_THROW_ON_ERROR);
}

salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
