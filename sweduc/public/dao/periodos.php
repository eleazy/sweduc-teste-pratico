<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
include('../helper_notas.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
require('../lib/QueryBuilder.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$dtde = explode("/", $datade);
$datade = "0000-" . $dtde[1] . "-" . $dtde[0];

$dtate = explode("/", $dataate);
$dataate = "0000-" . $dtate[1] . "-" . $dtate[0];

if ($action == "cadastra") {
    $query  = "SELECT COUNT(*) as cnt FROM periodos WHERE periodo='$periodo' AND colunaboletim=$colunaboletim";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];

    if ($cnt == 0) {
        $query  = "INSERT INTO periodos (periodo, colunaboletim, datade, dataate) VALUES ('$periodo', $colunaboletim, '$datade', '$dataate');";
        $result = mysql_query($query);
        echo mysql_insert_id() . "|$periodo|$colunaboletim|Periodo $periodo cadastrado.";
        $msg = "Periodo " . $periodo . " cadastrado.";
        $status = 1;
    } else {
        echo "0|$periodo|$colunaboletim|Periodo $periodo j� existe!.";
        $msg = "Periodo $periodo ja existe!.";
        $status = 0;
    }

    $parametroscsv = $query . "|$periodo|$colunaboletim|";
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "update") {
    $query  = "UPDATE periodos SET periodo='$periodo', colunaboletim=$colunaboletim, datade='$datade', dataate='$dataate' WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Periodo atualizado.";
        $msg = "Periodo Atualizado: " . $periodo . " Coluna Do Boletim: " . $colunaboletim . " De: " . $datade . " Ate: " . $dataate;

        $status = 1;
    } else {
        echo "red|Erro na atualiza��o.";
        $msg = "Erro na atualiza��o.";
        $status = 0;
    }
    $parametroscsv = $query . "|$periodo|$colunaboletim|$datade|$dataate|$id";
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateSituacaoFinal") {
    $qper = "SELECT colunaboletim FROM periodos WHERE id=" . $id;
    $rper = mysql_query($qper);
    $rowper = mysql_fetch_array($rper, MYSQL_ASSOC);
    $colBol = ($rowper['colunaboletim'] > 40) ? ' WHERE colunaboletim between 41 and 100 ' : ' WHERE colunaboletim <= 40 ';

    $query  = "UPDATE periodos SET situacaofinalanual=0" . $colBol;
    $result = mysql_query($query);
    $query  = "UPDATE periodos SET situacaofinalanual=1 WHERE id=$id";
    if ($result = mysql_query($query)) {
        $msg = "updateSituacaoFinal OK";
        $status = 1;
    } else {
        $msg = "updateSituacaoFinal ERRO";
        $status = 0;
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateRecuperacao") {
    $qper = "SELECT colunaboletim FROM periodos WHERE id=" . $id;
    $rper = mysql_query($qper);
    $rowper = mysql_fetch_array($rper, MYSQL_ASSOC);
    $colBol = ($rowper['colunaboletim'] > 40) ? ' WHERE colunaboletim between 41 and 100 ' : ' WHERE colunaboletim <= 40 ';

    $query  = "UPDATE periodos SET recuperacao=0" . $colBol;
    $result = mysql_query($query);
    $query  = "UPDATE periodos SET recuperacao=1 WHERE id=$id";
    if ($result = mysql_query($query)) {
        $msg = "updateRecuperacao OK";
        $status = 1;
    } else {
        $msg = "updateRecuperacao ERRO";
        $status = 0;
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateProvaFinal") {
    $qper = "SELECT colunaboletim FROM periodos WHERE id=" . $id;
    $rper = mysql_query($qper);
    $rowper = mysql_fetch_array($rper, MYSQL_ASSOC);
    $colBol = ($rowper['colunaboletim'] > 40) ? ' WHERE colunaboletim between 41 and 100 ' : ' WHERE colunaboletim <= 40 ';

    $query  = "UPDATE periodos SET provafinal=0" . $colBol;
    $result = mysql_query($query);
    $query  = "UPDATE periodos SET provafinal=1 WHERE id=$id";
    if ($result = mysql_query($query)) {
        $msg = "updateProvaFinal OK";
        $status = 1;
    } else {
        $msg = "updateProvaFinal ERRO";
        $status = 0;
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateMediaAnual") {
    $qper = "SELECT colunaboletim FROM periodos WHERE id=" . $id;
    $rper = mysql_query($qper);
    $rowper = mysql_fetch_array($rper, MYSQL_ASSOC);
    $colBol = ($rowper['colunaboletim'] > 40) ? ' WHERE colunaboletim between 41 and 100 ' : ' WHERE colunaboletim <= 40 ';

    $query  = "UPDATE periodos SET mediaanual=0 " . $colBol;
    $result = mysql_query($query);
    $query  = "UPDATE periodos SET mediaanual=1 WHERE id=$id";
    if ($result = mysql_query($query)) {
        $msg = "updateMediaAnual OK";
        $status = 1;
    } else {
        $msg = "updateMediaAnual ERRO";
        $status = 0;
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "atualizaContaFaltas") {
    if (isset($cursoId)) {
        $query  = "UPDATE cursos_periodos SET conta_faltas=$contaFaltas WHERE id=$id AND curso_id=$cursoId";
    } else {
        $query  = "UPDATE periodos SET conta_faltas=$contaFaltas WHERE id=$id";
    }

    if ($result = mysql_query($query)) {
        $msg = "updateMediaAnual OK";
        $status = 1;
    } else {
        $msg = "updateMediaAnual ERRO";
        $status = 0;
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query  = "DELETE FROM periodos WHERE id=$id";
    $queryPegarNome = "SELECT periodo FROM periodos WHERE id=" . $id;
    $resultadoPegarNome = mysql_query($queryPegarNome);
    while ($linhaNome = mysql_fetch_array($resultadoPegarNome)) {
        $periodoDeletado = $linhaNome['periodo'];
    }
     $msg = "Periodo: " . $periodoDeletado . " foi removido.";
    if ($result = mysql_query($query)) {
        echo "blue|Periodo removido.";

        $status = 1;
    } else {
        echo "red|Erro ao remover periodo.";
        $msg = "Erro ao remover periodo.";
        $status = 0;
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "periodosNovo") {
    $ano_letivo = getAnoLetivo($idanoletivo);
    $tipoperiodo = getTipoPeriodo($trimestre);
    $mudancaperiodo = getAnoMudanca($cliente);// $trimestre == idcurso
    $indice_boletim = ($tipoperiodo == 3 && $ano_letivo > $mudancaperiodo) ? " colunaboletim between 41 and 100 " : " colunaboletim between 0 and 40 ";

    // verifica se funcionario é professor
    $fun = "SELECT * FROM funcionarios WHERE id= {$_SESSION['id_funcionario']};";
    $rf = mysql_query($fun);
    $rfun = mysql_fetch_array($rf, MYSQL_ASSOC);

    if ($rfun['professor'] == 1) {
        $query = "SELECT id,periodo FROM periodos WHERE $indice_boletim AND ( DATE_FORMAT(NOW(),'%m%d') BETWEEN DATE_FORMAT(datade,'%m%d') AND DATE_FORMAT(dataate,'%m%d') ) ORDER BY colunaboletim ASC";
    } else {
        $query = "SELECT id,periodo FROM periodos WHERE $indice_boletim ORDER BY colunaboletim ASC";
    }
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "<option value='" . $row['id'] . "@" . $row['periodo'] . "'>" . $row['periodo'] . "</option>";
    }
} elseif ($action == "periodosNovoSimples") {
    $ano_letivo = getAnoLetivo($idanoletivo);
    $tipoperiodo = getTipoPeriodoPorGrade($trimestre);
    $mudancaperiodo = getAnoMudanca($cliente);// $trimestre == idcurso
    $indice_boletim = ($tipoperiodo == 3 && $ano_letivo > $mudancaperiodo) ? " colunaboletim between 41 and 100 " : " colunaboletim between 0 and 40 ";

    // verifica se funcionario é professor
    $fun = "SELECT * FROM funcionarios WHERE id=" . $id_funcionario;
    $rf = mysql_query($fun);
    $rfun = mysql_fetch_array($rf, MYSQL_ASSOC);

    if ($rfun['professor'] == 1) {
        $query = "SELECT id,periodo FROM periodos WHERE $indice_boletim AND ( DATE_FORMAT(NOW(),'%m%d') BETWEEN DATE_FORMAT(datade,'%m%d') AND DATE_FORMAT(dataate,'%m%d') ) ORDER BY colunaboletim ASC";
    } else {
        $query = "SELECT id,periodo FROM periodos WHERE $indice_boletim ORDER BY colunaboletim ASC";
    }
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "<option value='" . $row['id'] . "'>" . $row['periodo'] . "</option>";
    }
} elseif ($action == "receberPeriodoJson") {
    $periodo_id = $_REQUEST['periodo_id'];
    echo json_encode(QueryBuilder::on('periodos')->select('*, DATE_FORMAT(datade, "%d/%m") as inicio_em, DATE_FORMAT(dataate, "%d/%m") as termino_em')->where("id='$periodo_id'")->get(null)[0], JSON_THROW_ON_ERROR);
} elseif ($action == "associarCurso") {
    /**
     * Informações principais
     * Curso e período
     */
    $periodo_id    = $_REQUEST['periodo_id'];
    $curso_id      = $_REQUEST['curso_id'];

    // Validação dos dados principais
    if (!$periodo_id || !$curso_id) {
        json_encode([ 'msg' => 'Não é possível fazer a associação sem o curso e o período' ]);
        http_response_code('400');
        return;
    }

    /**
     * Configurações do período
     */
    $ordenacao     = $_REQUEST['colunaboletim'];
    $inicio_em     = date('Y') . '-' . join("-", array_reverse(explode('/', $_REQUEST['datade'])));
    $termino_em    = date('Y') . '-' . join("-", array_reverse(explode('/', $_REQUEST['dataate'])));
    $situacaofinalanual
                   = $_REQUEST['funcao'] == "situacao-final";
    $recuperacao   = $_REQUEST['funcao'] == "recuperao";
    $provafinal    = $_REQUEST['funcao'] == "prova-final";
    $mediaanual    = $_REQUEST['funcao'] == "media-anual";
    $conta_faltas  = isset($_REQUEST['contar_faltas']);

    $qb = QueryBuilder::on('cursos_periodos')->insert(
        compact(
            'periodo_id',
            'curso_id',
            'ordenacao',
            'inicio_em',
            'termino_em',
            'situacaofinalanual',
            'recuperacao',
            'provafinal',
            'mediaanual',
            'conta_faltas'
        )
    )->execute();
    $queryParaPegarPeriodo = "SELECT periodo FROM periodos WHERE id =" . $periodo_id;
    $queryParaPegarCurso = "SELECT curso FROM cursos WHERE id=" . $curso_id;
    $resultadoPeriodo = mysql_query($queryParaPegarPeriodo);
    $resultadoCurso = mysql_query($queryParaPegarCurso);
    while ($linhaCurso = mysql_fetch_array($resultadoCurso)) {
        $CursoAssociado = $linhaCurso['curso'];
    }
    while ($linhaPeriodo = mysql_fetch_array($resultadoPeriodo)) {
        $PeriodoAssociado = $linhaPeriodo['periodo'];
    }

    $msg = $qb['status'] ? "Período " . $PeriodoAssociado . " associado ao curso " . $CursoAssociado . " com sucesso!"
                         : "Erro ao associar período ao curso";
    $debug = "Query: {$qb['query']} | Erro: {$qb['error']}";

    http_response_code($qb['status'] ? 201 : 400);
    echo json_encode($_SESSION['login'] == 'suporte' ? compact('msg', 'debug') : compact('msg'), JSON_THROW_ON_ERROR);
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "alterarFuncao") {
    /**
     * Informações principais
     * Curso, período e função
     */
    $id       = $_REQUEST['id'];
    $curso_id = $_REQUEST['curso_id'];

    /**
     * Como a função representa um campo do banco de dados,
     * impede entrada de valores arbitrários como parametro função
     */
    $funcao  = $_REQUEST['funcao'];
    $funcoes = ["situacaofinalanual", "recuperacao", "provafinal", "mediaanual"];

    // Validação dos dados principais
    if (!$id || !$curso_id || !$funcao || !in_array($funcao, $funcoes)) {
        json_encode([ 'msg' => 'Não é possível atualizar a função sem o curso, o período e a função' ]);
        http_response_code('400');
        return;
    }

    $qb_remover_funcao = QueryBuilder::on('cursos_periodos')
      ->update([ $funcao => 0 ])
      ->where("curso_id = $curso_id")
      ->where("NOT id = $id")
      ->execute();

    $qb_adicionar_funcao = QueryBuilder::on('cursos_periodos')
      ->update([ $funcao => 1 ])
      ->where("id = $id")
      ->execute();

    $msg = $qb_remover_funcao['status'] && $qb_adicionar_funcao['status']
           ? "Período $periodo_id configurado para função {$funcao} no curso $curso_id com sucesso!"
           : "Erro ao alterar período da função curso.";
    $debug = "Remover função dos períodos anteriores - Query: {$qb_remover_funcao['query']} | Erro: {$qb_remover_funcao['error']}";
    $debug .= "Adiciona função no período - Query: {$qb_adicionar_funcao['query']} | Erro: {$qb_adicionar_funcao['error']}";

    http_response_code($qb_remover_funcao['status'] && $qb_adicionar_funcao['status'] ? 201 : 400);
    echo json_encode($_SESSION['login'] == 'suporte' ? compact('msg', 'debug') : compact('msg'), JSON_THROW_ON_ERROR);
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "atualizarPeriodoCurso") {
    $id         = $_REQUEST['id'];
    $ordenacao  = $_REQUEST['colunaboletim'];
    $inicio_em  = date('Y') . '-' . join("-", array_reverse(explode('/', $_REQUEST['inicio_em'])));
    $termino_em = date('Y') . '-' . join("-", array_reverse(explode('/', $_REQUEST['termino_em'])));

    $res = QueryBuilder::on('cursos_periodos')->update(
        compact(
            'ordenacao',
            'inicio_em',
            'termino_em'
        )
    )->where("id = $id")->execute();

    $msg = $res['status'] ? "Período do curso atualizado com sucesso!"
                         : "Erro ao atualizar período do curso";
    $debug = "Query: {$res['query']} | Erro: {$res['error']}";

    http_response_code($res['status'] ? 201 : 400);
    echo json_encode($_SESSION['login'] == 'suporte' ? compact('msg', 'debug') : compact('msg'), JSON_THROW_ON_ERROR);
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "desassociarPeriodoCurso") {
    $id = $_REQUEST['id'];

    $res = QueryBuilder::on('cursos_periodos')->delete()->where("id = $id")->execute();
    $msg = $res['status'] ? "Período do curso atualizado com sucesso!"
                       : "Erro ao atualizar período do curso";
    $debug = "Query: {$res['query']} | Erro: {$res['error']}";

    http_response_code($res['status'] ? 201 : 400);
    echo json_encode($_SESSION['login'] == 'suporte' ? compact('msg', 'debug') : compact('msg'), JSON_THROW_ON_ERROR);
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
