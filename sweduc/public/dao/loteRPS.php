<?php

include('../headers.php');
include('conectar.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$debug = 0;

if ($action == "retiradolote") {
    $tira = implode(",", $tiradoLote);

    $query  = "UPDATE alunos_fichafinanceira SET numeroloterps=0 WHERE id IN ($tira)";
    $result = mysql_query($query);
    //echo $query."<br>";

    $query  = "SELECT valorconsiderado, idempresa FROM loterps WHERE numlote=$numlote";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $valorconsiderado = $row['valorconsiderado'];
    $idempresa = $row['idempresa'];

    $query  = "SELECT aliquotaISS FROM empresas WHERE id=$idempresa";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $aliquota = $row['aliquotaISS'] / 100;

  // if ($valorconsiderado=="valorrecebido" ) $vservicos="alunos_fichasrecebidas.valorrecebido";
    if ($valorconsiderado == "valorrecebido") {
        $vservicos = "alunos_fichafinanceira.valorrecebido";
    } elseif ($valorconsiderado == "valor") {
        $vservicos = "alunos_fichafinanceira.valor";
    }

    // if ($vservicos=="alunos_fichasrecebidas.valorrecebido") {
    if ($vservicos == "alunos_fichafinanceira.valorrecebido") {
       // $query = "UPDATE loterps SET valorservicos=( SELECT SUM(tab1.valorrecebido) FROM (  SELECT alunos_fichafinanceira.valorrecebido FROM alunos_fichasrecebidas, alunos_fichafinanceira WHERE alunos_fichasrecebidas.idalunos_fichafinanceira=alunos_fichafinanceira.id AND numeroloterps=$numlote GROUP BY titulo  ) as tab1 ) WHERE numlote=$numlote";
        $query = "UPDATE loterps SET valorservicos=( SELECT SUM(tab1.valorrecebido) FROM (  SELECT alunos_fichafinanceira.valorrecebido FROM alunos_fichafinanceira WHERE numeroloterps=$numlote GROUP BY titulo  ) as tab1 ) WHERE numlote=$numlote";
    } elseif ($vservicos == "alunos_fichafinanceira.valor") {
        $query = "UPDATE loterps SET valorservicos=( SELECT SUM(tab1.valor) FROM (  SELECT valor FROM alunos_fichafinanceira WHERE numeroloterps=$numlote GROUP BY titulo  ) as tab1 ) WHERE numlote=$numlote";
    }


    $result = mysql_query($query);
//echo $query."<br>";

    $query  = "UPDATE loterps SET valoriss=(valorservicos*" . $aliquota . ") WHERE numlote=$numlote";
    $result = mysql_query($query);
//echo $query."<br>";

    $query  = "SELECT valorservicos, valoriss FROM loterps WHERE numlote=$numlote";
    $result = mysql_query($query);
  //echo $query."<br>";
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $query1  = "SELECT numeroloterps, Count(*) as cnt FROM ( SELECT numeroloterps FROM alunos_fichafinanceira WHERE numeroloterps=$numlote GROUP BY titulo)as tab1 GROUP BY numeroloterps";
    $result1 = mysql_query($query1);
  //echo $query1."<br>";
    $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);

    echo (is_countable($tiradoLote) ? count($tiradoLote) : 0) . " RPS removidos|R$ " . number_format($row['valorservicos'], 2, ',', '.') . "|R$ " . number_format($row['valoriss'], 2, ',', '.') . "|" . $row1['cnt'];
} elseif ($action == "apagaLote") {
    $numlote = $_POST['numlote'];

    $query  = "UPDATE alunos_fichafinanceira SET numeroloterps=0 WHERE numeroloterps=$numlote";
    $result = mysql_query($query);

    $query  = "DELETE FROM loterps WHERE numlote=$numlote";
    $result = mysql_query($query);
    echo " Lote RPS removido";
} elseif ($action == "criaLote") {
    $periodocompetencia = $_POST['periodo'];
    $datade = explode("/", $datade);
    $datade = $datade[2] . "-" . $datade[1] . "-" . $datade[0];
    $dataate = explode("/", $dataate);
    $dataate = $dataate[2] . "-" . $dataate[1] . "-" . $dataate[0];

    if ($valorMin == '') {
        $valorMin = '0';
    } else {
        $valorMin = number_format($valorMin, 2, '.', '');
    }

    $outradata = explode("/", $outradata);
    $outradata = $outradata[2] . "-" . $outradata[1] . "-" . $outradata[0];

    $query  = "SELECT numlote, aliquotaISS FROM empresas WHERE id=$prestador";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $numlote = $row['numlote'] + 1;
    $aliquota = $row['aliquotaISS'];

    $evfin = "'" . implode("','", $eventosfinanceiros ?? []) . "'";
    $cur = '';
    if (!empty($cursos) && strlen($cursos > 1)) {
        $cur = " series.idcurso IN ( '" . implode("','", $cursos) . "' ) AND ";
    }

    $filtraconta = '';
    if (count($idbanco ?? [])) {
        $contasbanco = "'" . implode("','", $idbanco ?? []) . "'";
        $filtraconta = ' AND alunos_fichafinanceira.idcontasbanco IN (' . $contasbanco . ') ';
    }

    $valor_serviços = '';

    if ($valorservicos != 0) {
        if ($valorservicos == "valorrecebido") {
            $vservicos = "alunos_fichafinanceira.valorrecebido";
        } elseif ($valorservicos == "valor") {
            $vservicos = "alunos_fichafinanceira.valor";
        }

        $valor_serviços .=  "AND $vservicos >= $valorMin";
    }

    $qalunos = "SELECT  alunos.id as aid
        FROM unidades_empresas, turmas, series, alunos_matriculas, alunos
        WHERE
            alunos_matriculas.idaluno=alunos.id AND
            alunos_matriculas.turmamatricula=turmas.id AND
            turmas.idserie=series.id AND
            $cur
            alunos_matriculas.idunidade=unidades_empresas.idunidade AND
            unidades_empresas.idempresa=$prestador GROUP BY alunos.id ";

    $ralunos = mysql_query($qalunos);
    $lista_alunos = '';
    while ($rowal = mysql_fetch_array($ralunos, MYSQL_ASSOC)) {
        $lista_alunos .= $rowal['aid'] . ',';
    }
    $lista_alunos = rtrim($lista_alunos, ',');

    $query  = "SELECT alunos_fichafinanceira.id as fid
        FROM alunos_fichafinanceira
        LEFT JOIN alunos_fichasrecebidas on alunos_fichasrecebidas.idalunos_fichafinanceira=alunos_fichafinanceira.id
        LEFT JOIN alunos_fichaitens on alunos_fichaitens.idalunos_fichafinanceira=alunos_fichafinanceira.id
        WHERE alunos_fichaitens.eventofinanceiro IN (
                SELECT eventofinanceiro FROM eventosfinanceiros
                WHERE eventosfinanceiros.id IN ($evfin)
            )
        $valor_serviços
        $filtraconta
        AND alunos_fichafinanceira.idaluno IN ($lista_alunos)
        AND $periodocompetencia BETWEEN '$datade' AND '$dataate';
        ";

    $result = mysql_query($query);
    $idfichas = [];
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $idfichas[] = $row['fid'];
    }

    if (count($idfichas)) {
        $fichas = "'" . implode("','", $idfichas ?? []) . "'";
        $query2 = "UPDATE alunos_fichafinanceira
            SET numeroloterps=$numlote
            WHERE id IN ($fichas)
            AND (
                numeroloterps IS NULL OR
                numeroloterps = 0
            )";

        $result2 = mysql_query($query2);

        if ($dataemissaorps == 'outra') {
            $dtemissaorps = $outradata;
        } elseif ($row && $row[$dataemissaorps]) {
            $dtemissaorps = explode("/", $row[$dataemissaorps]);
            $dtemissaorps = $dtemissaorps[2] . "-" . $dtemissaorps[1] . "-" . $dtemissaorps[0];
        } else {
            // Quando não existem títulos válidos no período selecionado
            $dtemissaorps = '0000-00-00';
        }

        $formato = 'Desconhecido';

        if ($_REQUEST['rps-formato'] == 'rj-xml') {
            $formato = 'XML Nacional';
        }

        if ($_REQUEST['rps-formato'] == 'sp-txt') {
            $formato = 'Nota Paulista';
        }

        $query1 = "INSERT INTO loterps (
            idempresa,
            numlote,
            datacriacao,
            dataemissao,
            layout,
            valorconsiderado,
            periodocompetencia,
            datade,
            dataate
        ) VALUES (
            $prestador,
            $numlote,
            '$agora',
            '$dtemissaorps',
            '$formato',
            '$valorservicos',
            '$periodocompetencia',
            '$datade',
            '$dataate'
        );";

        $result1 = mysql_query($query1);

        $query = "SELECT aliquotaISS FROM empresas WHERE id=$prestador";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $aliquota = $row['aliquotaISS'] / 100;

        $query  = "UPDATE loterps SET valorservicos=( SELECT SUM(" . $valorservicos . ") FROM alunos_fichafinanceira WHERE numeroloterps=$numlote) WHERE numlote=$numlote";
        $result = mysql_query($query);

        $query  = "UPDATE loterps SET valoriss=(valorservicos*" . $aliquota . ") WHERE numlote=$numlote";
        $result = mysql_query($query);

        $query  = "UPDATE empresas SET numlote = '$numlote' WHERE id=$prestador";
        $result = mysql_query($query);

        echo "Lote $numlote criado.";
    } else {
        echo htmlentities("Não há nenhuma ficha financeira que corresponda a sua pesquisa!");
    }

    echo '<script type="text/javascript">parent.funblockUI();</script>';
}
