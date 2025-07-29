<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = addslashes($_POST[$k]);
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
      $assunto = 'Abertura de Solicitação';
      $assunto = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

      $query  = "SELECT email FROM unidades WHERE id=" . $idunidade;
      $result = mysql_query($query);
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $emailreplyto = $row['email'];

      $query  = "SELECT * FROM solicitacoes WHERE id=" . $idsolicitacao;
      $result = mysql_query($query);
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $emailto = $row['emails'];
      $solicitacao = $row['solicitacao'];

      $mensagem = "Nome do Aluno: " . $nomealuno . "\n";
      $mensagem .= "Solicitação: " . $solicitacao . "\n";
      $mensagem .= "Data Solicitação: " . $datasolicitacaohumano . "\n";
      $mensagem .= "Responsável: " . $responsavel . "\n";

      $headers = 'From: no-reply@syscodes.com.br' . "\r\n" .
          'Reply-To: ' . $emailreplyto . "\r\n" .
          'X-Mailer: PHP/' . phpversion();

      $a1 = @mail($emailto, $assunto, $mensagem, $headers);

            $query  = "INSERT INTO alunos_protocolo(idaluno, idsolicitacao, datasolicitacao, dataconclusao, dataentrega, responsavel) VALUES ($idaluno, $idsolicitacao, '$datasolicitacao', '0000-00-00','0000-00-00','$responsavel');";
    if ($result = mysql_query($query)) {
        echo "blue|Solicitação enviada com sucesso.";
        $msg = "Solicitação $solicitacao enviada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao enviar nova solicitação." . $query;
        $msg = "Erro ao enviar nova solicitação: $solicitacao.";
        $status = 1;
    }
      $parametroscsv = $idanoletivo . ',' . $numordem . ',' . $disciplina . ',' . $abreviacao . ',' . $basenacional;
      salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $assunto = 'Cancelamento de solicitação';
    $assunto = '=?UTF-8?B?' . base64_encode($assunto) . '?=';

    $query  = "SELECT email FROM unidades WHERE id=" . $idunidade;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $emailreplyto = $row['email'];
    $msg = $query . " - ";

    $query  = "SELECT * FROM solicitacoes WHERE id=" . $idsolicitacao;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $emailto = $row['emails'];
    $solicitacao = $row['solicitacao'];
    $msg .= $query . " - ";

    $query  = "SELECT * FROM alunos_protocolo WHERE id=" . $idalunos_protocolo;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $datasolicitacao = $row['datasolicitacao'];
    $responsavel = $row['responsavel'];
    $msg .= $query . " - ";

    $mensagem = "Nome do Aluno: " . $nomealuno . "\n";
    $mensagem .= "Solicitação: " . $solicitacao . "\n";
    $mensagem .= "Data Solicitação: " . $datasolicitacao . "\n";
    $mensagem .= "Responsável: " . $responsavel . "\n";

    $headers = 'From: no-reply@syscodes.com.br' . "\r\n" .
      'Reply-To: ' . $emailreplyto . "\r\n" .
      'X-Mailer: PHP/' . phpversion();

    $a1 = @mail($emailto, $assunto, $mensagem, $headers);

    $query  = "DELETE FROM alunos_protocolo WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Solicitação removida com sucesso.";
        $msg = "Solicitação removida com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao remover solicitação.";
        $msg = "Erro ao remover solicitação.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "update") {
    $query  = "UPDATE alunos_protocolo SET $campo='" . date("Y-m-d") . "' WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Solicitação atualizada com sucesso.";
        $msg = "Solicitação atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar solicitação." . $query;
        $msg = "Erro ao atualizar solicitação.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
