<?php

include '../headers.php';
include 'conectar.php';
include_once 'logs.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php';
include 'common.php';

use App\Model\Config\ContasBanco;

$agora = date("Y-m-d H:i:s");
$debug = 0;

$keys = array_keys($_POST); foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$valor_baixa_min = $valor_baixa_min != '' ? $valor_baixa_min : 0;
include '../permissoes.php';

if ($debug) {
    echo $action . "<br />";
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionariologin = $row1['id'];


if ($datainicial) {
    $dtinicial = explode("/", $datainicial);
    $datainicial = $dtinicial[2] . "-" . $dtinicial[1] . "-" . $dtinicial[0];
} else {
    $datainicial = "0000-00-00";
}

if ($saldoinicial == "") {
    $saldoinicial = 0;
}
if ($tarifaboleto == "") {
    $tarifaboleto = 0;
}

$saldoinicial = str_replace(',', '.', str_replace('.', '', $saldoinicial));
$tarifaboleto = str_replace(',', '.', str_replace('.', '', $tarifaboleto));

if ($tipo == 0) {
    $banco = explode("@", $banco);
    $banconum = $banco[0];
    $banconome = $banco[1];
    $bancoarquivo = $banco[2];
} elseif ($tipo == 1) {
    $banconum = 0;
    $banconome = "";
    $bancoarquivo = "";
} elseif ($tipo == 2) {
    $banconum = 0;
    $banconome = "CAIXA";
    $bancoarquivo = "carne";
}

if ($action == "cadastra") {
    echo "red|Erro ao cadastrar !";
} elseif ($action == "apaga") {
    $query  = "SELECT COUNT(*) as cnt FROM alunos_fichafinanceira WHERE idcontasbanco=" . $id;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($row['cnt'] > 0) {
        echo "red|Erro ao remover Conta. Existem títulos lançados nessa conta";
    } else {
        $query  = "DELETE FROM contasbanco WHERE id=" . $id;
        if ($result = mysql_query($query)) {
            $msg = "Conta removida.";
            echo "blue|Conta removida.";
        } else {
            $msg = "Erro ao remover Conta.";
            echo "red|Erro ao remover Conta. ";
        }
        $parametroscsv = $query;
        salvaLog($idfuncionariologin, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "recebeContas") {
    $query  = "SELECT * FROM contasbanco WHERE tipo=0 ORDER BY nomeb ASC"; //AND idempresa=$idempresa
    $result = mysql_query($query);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '"';
        if ($row['id'] == $contasbanco) {
            echo "selected";
        }
        echo ' >' . $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'] . '</option>';
    }

    $query  = "SELECT *, contasbanco.id as cid FROM contasbanco, unidades, funcionarios, pessoas WHERE contasbanco.idfuncionario=funcionarios.id AND funcionarios.idempresa=unidades.idempresa AND funcionarios.idpessoa=pessoas.id AND contasbanco.tipo=1 AND contasbanco.idempresa=$idempresa ORDER BY nome ASC";


    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '" ';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >CAIXA DE ' . $row['nome'] . '</option>';
    }
} elseif ($action == "recebeContasTodas") {
    if ($idunidade == -1) {
        $idempresa = 'contasbanco.idempresa';
    } elseif ($idempresa == "") {
        $query  = "SELECT idempresa FROM unidades WHERE id=$idunidade";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $idempresa = $row['idempresa'];
    }

    if (in_array($financeiro[9], $arraydo2)) {
        $query  = "SELECT * FROM contasbanco WHERE contasbanco.tipo=0 ORDER BY nomeb ASC"; // 0: BANCO     AND idempresa=$idempresa
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '" >' . $row['nomeb'] . '::' . $row['banconome'] . '::' . $row['agencia'] . '::' . $row['conta'] . '</option>';
        }
    }
    if ((in_array($financeiro[7], $arraydo2)) || (in_array($financeiro[8], $arraydo2))) {
        if (in_array($financeiro[8], $arraydo2)) {
            $query  = "SELECT contasbanco.id as cid, pessoas.nome FROM empresas, contasbanco, funcionarios, pessoas WHERE contasbanco.idempresa=empresas.id AND contasbanco.idempresa=$idempresa AND empresas.id=funcionarios.idempresa AND contasbanco.idfuncionario=funcionarios.id AND funcionarios.idpessoa=pessoas.id AND contasbanco.tipo=1 ORDER BY razaosocial ASC, nome ASC";       // 1: FUNCIONARIOS  - TODAS AS CONTAS
        } else {
            $query  = "SELECT contasbanco.id as cid, pessoas.nome FROM empresas, contasbanco, funcionarios, pessoas WHERE contasbanco.idempresa=empresas.id AND empresas.id=funcionarios.idempresa AND contasbanco.idfuncionario=funcionarios.id AND funcionarios.idpessoa=pessoas.id AND pessoas.id=$idpessoalogin AND contasbanco.tipo=1 ORDER BY razaosocial ASC, nome ASC";       // 1: FUNCIONARIOS  - CONTA PRÓPRIA APENAS
        }

        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['cid'] . '" >CAIXA DE ' . mb_strtoupper($row['nome'], 'UTF-8') . '</option>';
        }
    }
    if (in_array($financeiro[10], $arraydo2)) {
        $query  = "SELECT id, nomeb FROM contasbanco WHERE contasbanco.tipo=2 AND idempresa=$idempresa ORDER BY nomeb ASC";  // ESCOLA
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '" >' . $row['nomeb'] . '</option>';
        }
    }
} elseif ($action == "recebeContasSemFuncionario") {
    $query  = "SELECT * FROM contasbanco WHERE tipo=0 ORDER BY banconome ASC"; //AND idempresa=$idempresa
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '"';
        if ($row['id'] == $contasbanco) {
            echo "selected";
        }
        echo ' >' . $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'] . '</option>';
    }


    $query  = "SELECT * FROM contasbanco WHERE tipo=2 AND idempresa=$idempresa ORDER BY banconome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '" ';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >' . $row['nomeb'] . ' - ' . $row['banconome'] . '</option>';
    }
} elseif ($action == "recebeContasBanco") {
    $query  = "SELECT * FROM contasbanco WHERE tipo=0 AND contasbanco.desativado_em IS NULL ORDER BY banconome ASC"; // AND idempresa=$idempresa
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '"';
        if ($row['id'] == $contasbanco) {
            echo "selected";
        }
        echo ' >' . $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'] . '</option>';
    }
} elseif ($action == "recebeContasUnidades") {
    $query  = "SELECT *, contasbanco.id as cid FROM contasbanco, unidades_empresas WHERE contasbanco.tipo=0 AND unidades_empresas.idunidade=$idunidade AND unidades_empresas.idempresa=contasbanco.idempresa ORDER BY banconome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '" ';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >' . $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'] . '</option>';
    }

    $query  = "SELECT *, contasbanco.id as cid FROM contasbanco, unidades_empresas WHERE contasbanco.tipo=2 AND unidades_empresas.idunidade=$idunidade AND unidades_empresas.idempresa=contasbanco.idempresa ORDER BY banconome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '"';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >' . $row['nomeb'] . ' - ' . $row['banconome'] . '</option>';
    }
} elseif ($action == "recebeContasEmpresaFuncionario") {
    $query  = "SELECT *, contasbanco.id as cid FROM contasbanco WHERE contasbanco.idempresa=$idempresa AND ( contasbanco.tipo=0 OR contasbanco.tipo=2 ) GROUP BY contasbanco.id ORDER BY tipo ASC, banconome ASC";

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '" ';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >';
        if ($row['tipo'] == 0) {
            echo $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'] . ' - ' . money_format('%.2n', $row['saldoatual']);
        } elseif ($row['tipo'] == 2) {
            echo $row['nomeb'] . ' - ' . $row['banconome'] . ' - ' . money_format('%.2n', $row['saldoatual']);
        }
        echo '</option>';
    }

    $query  = "SELECT *, contasbanco.id as cid FROM contasbanco, funcionarios WHERE funcionarios.idpessoa=$idpessoalogin AND contasbanco.idfuncionario=funcionarios.id AND contasbanco.idempresa=$idempresa AND contasbanco.tipo=1 GROUP BY contasbanco.id ORDER BY tipo ASC, banconome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '" ';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >' . $row['nomeb'] . '</option>';
    }
} elseif ($action == "recebeContasEmpresa") {
    $query  = "SELECT *, contasbanco.id as cid FROM contasbanco, unidades_empresas WHERE contasbanco.idempresa=$idempresa AND ( contasbanco.tipo=0 OR contasbanco.tipo=2 ) AND contasbanco.desativado_em IS NULL GROUP BY contasbanco.id ORDER BY tipo ASC, banconome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '" ';
        if (!empty($contasbanco) && $row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >';
        if ($row['tipo'] == 0) {
            echo $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'] . ' - ' . money_format('%.2n', $row['saldoatual']);
        } elseif ($row['tipo'] == 2) {
            echo $row['nomeb'] . ' - ' . $row['banconome'] . ' - ' . money_format('%.2n', $row['saldoatual']);
        }
        echo '</option>';
    }
} elseif ($action == "recebeContasEmpresaSimples") {
    $query  = "SELECT *, contasbanco.id as cid FROM contasbanco, unidades_empresas WHERE contasbanco.idempresa=$idempresa AND ( contasbanco.tipo=0 OR contasbanco.tipo=2 ) AND contasbanco.desativado_em IS NULL GROUP BY contasbanco.id ORDER BY tipo ASC, banconome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
         echo '<option value="' . $row['cid'] . '" data-banconum="' . htmlspecialchars($row['banconum']) . '"';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >';
        if ($row['tipo'] == 0) {
            echo $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'];
        } elseif ($row['tipo'] == 2) {
            echo $row['nomeb'] . ' - ' . $row['banconome'];
        }
        echo '</option>';
    }
} elseif ($action == "recebeContasBancoEmpresa") {
    $query  = "SELECT *, contasbanco.id as cid
        FROM contasbanco, unidades_empresas
        WHERE contasbanco.idempresa=$idempresa
        AND contasbanco.tipo IN (0,2)
        AND contasbanco.desativado_em IS NULL
        GROUP BY contasbanco.id
        ORDER BY tipo ASC, banconome ASC";

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['cid'] . '" ';
        if ($row['cid'] == $contasbanco) {
            echo "selected";
        }
        echo ' >';
        if ($row['tipo'] == 0) {
            echo $row['nomeb'] . "::" . $row['banconome'] . "::" . $row['agencia'] . "::" . $row['conta'];
        } elseif ($row['tipo'] == 2) {
            echo $row['nomeb'] . ' - ' . $row['banconome'];
        }
        echo '</option>';
    }
} elseif ($action == 'fechamentomanual') {
    $query  = "UPDATE configuracoes SET caixa_fechamentomanual='$fechamanual'";

    $situacao = ($fechamanual == 1) ? 'Habilitado' : 'Desabilitado';

    if ($result = mysql_query($query)) {
        echo "Alterado fechamento manual: " . $situacao;
    } else {
        echo "Erro ao atualizar fechamento manual!";
        $msg = "Erro ao atualizar !" . mysql_error();
    }
} elseif ($action == 'tiposRetorno') {
    $id = (int) $_REQUEST['id'];
    $query  = "SELECT * FROM remessa_retorno WHERE codigobanco ='$codbanco' AND retorno=1";
    $result = mysql_query($query);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        if ($row['id'] == $id) {
            echo '<option value="' . $row['id'] . '" selected>' . $row['descricao'] . '</option>';
        } else {
            echo '<option value="' . $row['id'] . '" >' . $row['descricao'] . '</option>';
        }
    }
} elseif ($_REQUEST['action'] == "mudarVisibilidade") {
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
    $contasBanco = new ContasBanco($id);
    $contasBanco->mudarVisibilidade($ativado);

    return response("Conta " . ($ativado ? 'ativada' : 'desativada') . " com sucesso.");
}
