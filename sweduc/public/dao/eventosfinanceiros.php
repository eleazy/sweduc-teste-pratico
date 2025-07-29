<?php

use App\Model\Financeiro\EventoFinanceiro;

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
include 'common.php';

$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];


function updateFichasFinanceiras($idaluno, $nummatricula, $dataatual, $reajuste)
{
    // $qupdtaff = "SELECT * FROM alunos_fichafinanceira WHERE nummatricula='".$nummatricula."' AND idaluno=".$idaluno." AND datavencimento >= '".$dataatual."' AND situacao=0 AND bolsa > 0 AND reajustado=0";
    $qupdtaff = "SELECT * FROM alunos_fichafinanceira WHERE nummatricula='" . $nummatricula . "' AND idaluno=" . $idaluno . " AND datavencimento >= '" . $dataatual . "' AND situacao=0 AND reajustado=0";
    $rupdtaff = mysql_query($qupdtaff);
    while ($aff = mysql_fetch_array($rupdtaff, MYSQL_ASSOC)) {
        // busca fichaitens
        $qfi = "SELECT * FROM alunos_fichaitens WHERE idalunos_fichafinanceira=" . $aff['id'];
        $rfi = mysql_query($qfi);

        $valortotal = 0;
        while ($afi = mysql_fetch_array($rfi, MYSQL_ASSOC)) {
            // if( strpos($afi['eventofinanceiro'], 'ensalidade') ||
            if (
                $afi['codigo'] == '11020000' ||
                $afi['codigo'] == '11030000' ||
                $afi['codigo'] == '11040000' ||
                $afi['codigo'] == '11050000' ||
                $afi['codigo'] == '12010000' ||
                $afi['codigo'] == '12020000' ||
                $afi['codigo'] == '12030000' ||
                $afi['codigo'] == '12040000' ||
                $afi['codigo'] == '12050000' ||
                $afi['codigo'] == '12060000'
            ) {
                $novovalor = $afi['valor'] + (($afi['valor'] * $reajuste) / 100);

                $qupdt2 = "UPDATE alunos_fichaitens SET valor='" . $novovalor . "' WHERE id=" . $afi['id'];
                $rupdt2 = mysql_query($qupdt2);

                $valortotal = $valortotal + $novovalor;
            } else {
                $valortotal = $valortotal + $afi['valor'];
            }
        }
        // fim fichaitens

        $novovalorbolsa =  ($aff['bolsa'] > 0) ? $aff['bolsa'] + (($aff['bolsa'] * $reajuste) / 100) : 0;

        $qupdt = "UPDATE alunos_fichafinanceira SET valor='" . $valortotal . "', bolsa='" . $novovalorbolsa . "', reajustado=1 WHERE id=" . $aff['id'];
        $rupdt = mysql_query($qupdt);
    }
}

if ($action == "cadastra") {
    $query  = "SELECT COUNT(*) as cnt FROM eventosfinanceiros WHERE codigo='$codigo' OR mascara='$mascara'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];

    if ($cnt == 0) {
        $query  = "INSERT INTO eventosfinanceiros (codigo, mascara, eventofinanceiro, eventoselecionavel) VALUES ('$codigo', '$mascara', '$nomeEvento', '$eventoselecionavel');";
        $result = mysql_query($query);
        echo mysql_insert_id() . "|" . $nomeEvento . "|" . $codigo . "|" . $mascara . "|Evento $nomeEvento cadastrado.";
        $msg = "ID: " . mysql_insert_id() . "| Nome do Evento: " . $nomeEvento . "| Código: " . $codigo . "| Mascara: " . $mascara . "|Evento: $nomeEvento cadastrado.";
    } else {
        echo "0|" . $nomeEvento . "|" . $codigo . "|" . $mascara . "|Evento $nomeEvento j� existe!.";
        $msg = "0| Evento: " . $nomeEvento . "| Código: " . $codigo . "| Máscara: " . $mascara . "|Evento $nomeEvento j� existe!.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "update") {
    $query  = "UPDATE eventosfinanceiros SET eventofinanceiro='$novoevento' WHERE id=$id";
    if ($result = mysql_query($query)) {
        $msg = "Evento atualizado.| Novo evento: " . $novoevento . " Código: " . $codigo;
        echo "blue|Evento atualizado.|" . $codigo;
    } else {
        $msg = "Erro na atualiza��o.|" . $codigo;
        echo "red|Erro na atualiza��o.|" . $codigo;
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "salvaQuota") {
    $quota = str_replace(",", ".", str_replace(".", "", mysql_real_escape_string($_POST['quota'])));
    $evento_id = mysql_real_escape_string($_POST['evento_id']);
    $empresa_id = mysql_real_escape_string($_POST['empresa_id']);
    $query = "INSERT INTO empresas_quota (evento_financeiro_id, empresa_id, quota) VALUES ($evento_id, $empresa_id, $quota) ON DUPLICATE KEY UPDATE quota = $quota";
    $msg = ($result = mysql_query($query)) ? "Limite do evento financeiro $evento_codigo alterado para $quota na empresa $empresa_nome" : "Falha ao alterar limite do evento $evento_codigo na empresa $empresa_nome. Erro: " . mysql_error();
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $query, $msg);
    echo $msg;
    return;
} elseif ($action == "listaQuota") {
    $evento_id = mysql_real_escape_string($_POST['evento_id']);
    $empresa_id = mysql_real_escape_string($_POST['empresa_id']);

    $query_result = mysql_query("SELECT quota FROM empresas_quota eq WHERE empresa_id='$empresa_id' AND evento_financeiro_id='$evento_id'");
    echo mysql_fetch_array($query_result, MYSQL_ASSOC)['quota'];
    return;
} elseif ($action == "apaga") {
    $query  = "DELETE FROM eventosfinanceiros WHERE id=$id";
    $queryPegaEvento = "SELECT eventofinanceiro AS evento FROM eventosfinanceiros WHERE id=$id";
    $resultadoDaQuery = mysql_query($queryPegaEvento);
    while ($linhaEvento = mysql_fetch_array($resultadoDaQuery)) {
        $eventoRemovido = $linhaEvento['evento'];
    }
    $msg = "Evento " . $eventoRemovido . " removido com sucesso!";
    if ($result = mysql_query($query)) {
        echo "blue|Evento removido.";
    } else {
        $msg = "Erro ao remover Evento.";
        echo "red|Erro ao remover Evento.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "removedolote") {
    $query  = "UPDATE alunos_fichafinanceira SET remessaenviado=0, remessalote=0 WHERE id IN ($idfichafinanceira)";
    if ($result = mysql_query($query)) {
        $msg = "Removido.|";
        echo "ok";
    } else {
        $msg = "Erro na atualização.|";
        echo "erro";
    }
} elseif ($action == "aplicareajuste") {
    $anoatual = date('Y');
    $d = explode('/', $reajustedata);
    $hoje = $d[2] . '-' . $d[1] . '-' . $d[0];

    // anoletivo
    $qal = "SELECT id FROM anoletivo WHERE anoletivo=" . $anoatual;
    $ral = mysql_query($qal);
    $al = mysql_fetch_array($ral, MYSQL_ASSOC);

    $reajuste = str_replace(',', '.', $reajuste);

    // salva log reajuste
    $qreajuste = "INSERT INTO reajusteanual (idanoletivo, idturma,percentualreajuste,datareajuste,idfuncionario) VALUES (" . $al['id'] . ", " . $idturma . ",'" . $reajuste . "','" . $hoje . "'," . $idpessoalogin . ");";
    $rreajuste = mysql_query($qreajuste);

    // atualiza alunos_matriculas
    $qmatriculas = "SELECT
            alunos_matriculas.*
        FROM alunos_matriculas
        INNER JOIN alunos ON alunos_matriculas.idaluno=alunos.id
        WHERE
            recebereajuste=1 AND
            turmamatricula = '$idturma' AND
            idaluno NOT IN (
                select
                    id
                from
                    alunos a
                where
                    a.numeroaluno like '2017%' or
                    a.numeroaluno like '2018%' or
                    a.numeroaluno like '2019%' or
                    a.numeroaluno like '2020%' or
                    a.numeroaluno like '2021%' or
                    a.numeroaluno like '2022%' or
                    a.numeroaluno like '2023%' or
                    a.numeroaluno like '2024%'
            )";

    $rmatriculas = mysql_query($qmatriculas);

    while ($mat = mysql_fetch_array($rmatriculas, MYSQL_ASSOC)) {
        $qupdt = "UPDATE alunos_matriculas SET reajustado = 1, datareajuste = '" . $hoje . "' WHERE id=" . $mat['id'];
        $rupdt = mysql_query($qupdt);

        $updateffin = updateFichasFinanceiras($mat['idaluno'], $mat['nummatricula'], $hoje, $reajuste);
    }

    echo $reajuste . '|' . $reajustedata;
} elseif ($_REQUEST['action'] == "mudarHabilitado") {
    // Atribuindo valores da requisição
    $id      = $_REQUEST['id'];
    $ativado = $_REQUEST['active'] == "true";

    // Validando entrada
    $idInvalido = !isset($id) || $id < 0;
    $ativadoInvalido = !($_REQUEST['active'] == "true" || $_REQUEST['active'] == "false");
    if ($idInvalido || $ativadoInvalido) {
        return response(
            "Não foi possível completar a requisição, os parâmetros são inválidos",
            null,
            400
        );
    }

    // Executando modificação
    $queryHabilitado = "UPDATE `eventosfinanceiros` SET habilitado = " . ($ativado ? "1" : "0") . " WHERE id = $id";
    mysql_query($queryHabilitado);

    return response("Evento " . ($ativado ? 'ativado' : 'desativado') . " com sucesso.");
} elseif ($action == "changeApareceNoInformeRencimentos") {
    $id      = $_REQUEST['id'];
    $ativado = $_REQUEST['active'] == "true";

    // Validando entrada
    $idInvalido = !isset($id) || $id < 0;
    $ativadoInvalido = !($_REQUEST['active'] == "true" || $_REQUEST['active'] == "false");
    if ($idInvalido || $ativadoInvalido) {
        return response(
            "Não foi possível completar a requisição, os parâmetros são inválidos",
            null,
            400
        );
    }

    // Executando modificação
    $queryHabilitado = "UPDATE `eventosfinanceiros` SET informeRendimentos = " . ($ativado ? "1" : "0") . " WHERE id = $id";
    mysql_query($queryHabilitado);

    return response("Evento " . ($ativado ? 'ativado' : 'desativado') . " com sucesso.");
}
