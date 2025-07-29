<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
include(__DIR__ . '/../permissoes.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
    ${$k} = str_replace(',', '.', str_replace('.', '', $_POST[$k]));
}

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $query = "SELECT COUNT(*) as cnt FROM series WHERE idcurso=$idcurso AND serie='$serie'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    if ($row['cnt'] == 0) {
        $query1 = "INSERT INTO series (idcurso, serie, valorAnuidade, dependencias, mediaaprovacao, mediaaprovacaorec, mediaaprovacaopf, pontosaprovacao, mediarecuperacao,limiterecuperacoes,limiteprovasfinais,tetonotas) VALUES ($idcurso, '$serie', '$valorAnuidade', '$dependencias', '$mediaaprovacao', '$mediaaprovacaorec', '$mediaaprovacaopf','$pontosaprovacao','$mediarecuperacao','$limiterecuperacoes','$limiteprovasfinais','$tetonotas')";
        if ($result1 = mysql_query($query1)) {
            $idserie = mysql_insert_id();
            $insok = 1;
            $parametroscsv = $idserie;
            foreach ($idplanosparcelamento as $idpparcelamento) {
                $query1 = "INSERT INTO serie_plano (idserie, idplanosparcelamento) VALUES ($idserie, $idpparcelamento)";
                if (!$result1 = mysql_query($query1)) {
                    $insok = 0;
                }
                $parametroscsv .= "," . $idpparcelamento;
            }
            if ($insok == 1) {
                echo "blue|" . htmlentities("Série " . $serie . " cadastrada.");
                $msg = "Série " . $serie . " cadastrada.";
                $status = 0;
                $parametroscsv = $idcurso . ',' . $serie . ',' . $valorAnuidade . ',' . $mediaaprovacao;
                salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
            } else {
                echo "red|" . htmlentities("Erro ao cadastrar planos de parcelamento da série.");
                $msg = "Erro ao cadastrar planos de parcelamento da série.";
                $status = 1;
                salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
            }
        } else {
            echo "red|" . htmlentities("Erro ao cadastrar nova série.");
            $msg = "Erro ao cadastrar nova série.";
            $status = 1;
            $parametroscsv = $idcurso . ',' . $serie . ',' . $valorAnuidade . ',' . $mediaaprovacao;
            salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
        }
    } else {
        echo "red|" . htmlentities("Série já existe!");
        $msg = "Série já existe!";
        $status = 1;
        $parametroscsv = $serie . ',' . $idcurso . ',' . $row['cnt'];
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "update") {
    $serieAntecessora = $_REQUEST['serie-antecessora-id'];
    $rematriculaAnamnese = !empty($_REQUEST['rematricula-anamnese']) ? 1 : 0;
    $primeiraParcelaAnuidade = str_replace(['.', ','], ['', '.'], $_REQUEST['primeira_parcela_anuidade']);

    $query = "UPDATE series SET
        serie='$serie',
        valorAnuidade='$valorAnuidade',
        primeira_parcela_anuidade='$primeiraParcelaAnuidade',
        dependencias='$dependencias',
        pontosaprovacao='$pontosaprovacao',
        mediaaprovacao='$mediaaprovacao',
        mediaaprovacaorec='$mediaaprovacaorec',
        mediaaprovacaopf='$mediaaprovacaopf',
        mediarecuperacao='$mediarecuperacao',
        limiterecuperacoes='$limiterecuperacoes',
        limiteprovasfinais='$limiteprovasfinais',
        tetonotas='$tetonotas',
        serie_antecessora_id='$serieAntecessora',
        rematricula_anamnese='$rematriculaAnamnese',
        rodape_aprovacao='$rodape_aprovacao',
        rodape_recuperacao='$rodape_recuperacao',
        rodape_intervalo_um='$rodape_intervalo_um',
        rodape_intervalo_dois='$rodape_intervalo_dois'
        WHERE id=$idserie";

    if (!mysql_query($query)) {
        echo "red|" . htmlentities("Erro na atualização da série.");
        $msg = "Erro na atualização da série.";
        $status = 1;
        $parametroscsv = $idserie . ',' . $serie . ',' . $idcurso . ',' . $valorAnuidade . ',' . $mediaaprovacao;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
        exit;
    }

    mysql_query("DELETE FROM series_documentos_rematricula WHERE serie_id=$idserie");

    $contratos = $_REQUEST['contrato-rematricula-id'] ?? [];

    if (!empty($contratos)) {
        $contratosValues = implode(',', array_map(fn($val) => "('$idserie','$val')", $contratos));

        $query = mysql_query("INSERT INTO series_documentos_rematricula (serie_id, documento_id) VALUES $contratosValues");

        if (!$query1) {
            $msg = "Erro na documentos de contrato.";
            echo "red|" . htmlentities($msg);
            $status = 1;
            salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
            exit;
        }
    }

    $query = "DELETE FROM serie_plano WHERE idserie=$idserie";

    if (!mysql_query($query)) {
        echo "red|" . htmlentities("Erro 2 na atualização dos planos de parcelamento. ");
        $msg = "Erro 2 na atualização dos planos de parcelamento.";
        $status = 1;
        $parametroscsv = $idserie;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
        exit;
    }

    if (isset($idplanosparcelamento)) {
        $parcelamentosValues = implode(',', array_map(fn($val) => "('$idserie','$val')", $idplanosparcelamento));

        $query1 = "INSERT INTO serie_plano (idserie, idplanosparcelamento) VALUES $parcelamentosValues";

        if (!mysql_query($query1)) {
            echo "red|" . htmlentities("Erro 1 na atualização dos planos de parcelamento. ");
            $msg = "Erro 1 na atualização dos planos de parcelamento.";
            $status = 1;
            salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
            exit;
        }

        $parametroscsv .= "," . $parcelamentosValues;
    }

    echo "blue|Série atualizada com sucesso.";
    $msg = "Série: " . $serie . " atualizada com sucesso.";
    $status = 0;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
    $query = "SELECT COUNT(*) as cnt FROM alunos_matriculas, turmas WHERE alunos_matriculas.turmamatricula=turmas.id AND turmas.idserie=" . $id;

    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    if ($row['cnt'] == 0) {
        $query = "DELETE FROM series WHERE id=$id";
        $queryPegaSerie = "SELECT serie FROM series WHERE id=$id";
        $resultadoQuery = mysql_query($queryPegaSerie);
        while ($linhaSerie = mysql_fetch_array($resultadoQuery)) {
            $serieDeletada = $linhaSerie['serie'];
        }
        $msg = "Série: " . $serieDeletada . " removida.";
        if ($result = mysql_query($query)) {
            $query = "DELETE FROM serie_plano WHERE idserie=$id";
            if ($result = mysql_query($query)) {
                echo "blue|" . htmlentities("Série removida.");

                $status = 0;
                $parametroscsv = $id;
                salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
            } else {
                echo "red|" . htmlentities("Série removida. Erro ao remover planos de pagamento da série.");
                $msg = "Série removida. Erro ao remover planos de pagamento da série.";
                $status = 1;
                $parametroscsv = $id;
                salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
            }
        } else {
            echo "red|" . htmlentities("Erro ao remover série.");
            $msg = "Erro ao remover série.";
            $status = 1;
            $parametroscsv = $id;
            salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
        }
    } else {
        echo "red|" . htmlentities("Erro ao remover série. Série contém alunos matrículados.");
        $msg = "Erro ao remover série. Série contém alunos matrículados.";
        $status = 1;
        $parametroscsv = $id . ',' . $row['cnt'];
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "recebeSeriesCardapio") {
    $query = "SELECT s.id as id, s.serie FROM grade g
                INNER JOIN series s ON g.idserie=s.id
                INNER JOIN cursos c ON s.idcurso=c.id
                WHERE
                    g.idanoletivo=$idanoletivo AND
                    c.idunidade=$idunidade
                GROUP BY s.id
                ORDER BY s.serie ASC";

    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
    }
} elseif ($action == "recebeSeries") {
    $query = "SELECT * FROM series WHERE idcurso=" . $idcurso . " ORDER BY serie ASC";
    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
    }
} elseif ($action == "recebeSeriesDiario") {
    $sql = "";
    if ($idpermissoes != 1 && !in_array($academico[15], $arraydo2)) {
        $sql =  " AND  idfuncionario = " . $idfuncionario ;
    }

    $query =  "SELECT
                    s.serie, s.id
                FROM
                    grade_funcionario gf
                        INNER JOIN
                    grade g ON gf.idgrade = g.id
                        INNER JOIN
                    series s ON g.idserie = s.id
                        INNER JOIN
                    cursos c ON s.idcurso = c.id
                WHERE
                     idunidade =  " . $idunidade . " and c.id = " . $idcurso . $sql . "   AND idanoletivo = " . $idanoletivo . "
                GROUP BY s.id  order by s.serie";


    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
    }
} elseif ($action == "recebeCursosSeriesDaUnidade") {
    $query = "SELECT cursos.curso,series.serie, series.id FROM series, cursos WHERE series.idcurso=cursos.id AND cursos.idunidade=" . $idunidade . " ORDER BY curso ASC, serie ASC";
    $result = mysql_query($query);
    echo '<option value="0" selected="selected" > - </option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['curso'] . ' - ' . $row['serie'] . '</option>';
    }
} elseif ($action == "recebeSeriesDaUnidade") {
    $query = "SELECT series.serie, series.id FROM series, cursos WHERE series.idcurso=cursos.id AND cursos.idunidade=" . $idunidade . " ORDER BY serie ASC";
    $result = mysql_query($query);
    //echo '<option value="todos" selected="selected">TODOS</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
    }

    /*
      } else if ($action=="recebeSeriesComTurmasDaUnidade") {
      $query = "SELECT series.serie, series.id FROM series, cursos, turmas WHERE turmas.idserie=series.id AND series.idcurso=cursos.id AND cursos.idunidade=".$idunidade." GROUP BY serie ORDER BY serie ASC";
      $result = mysql_query($query);
      //echo '<option value="todos" selected="selected">TODOS</option>';
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) echo '<option value="'.$row['id'].'">'.$row['serie'].'</option>';
     */
} elseif ($action == "recebeSeriesComTurmas") {
    if ($idcurso != "todos") {
        $query = "SELECT series.id, series.serie FROM series, turmas WHERE turmas.idserie=series.id AND series.idcurso=" . $idcurso . " GROUP BY serie ORDER BY serie ASC";

        $result = mysql_query($query);
        //echo '<option value="11111" selected="selected">'.$query.'</option>';
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
        }
    }
} elseif ($action == "recebeSeriesComTurmas2") {
    if ($idcurso != "todos") {
        $query = "SELECT DISTINCT GROUP_CONCAT(DISTINCT(series.id)) id, series.serie FROM series, turmas WHERE turmas.idserie=series.id AND series.idcurso in(" . str_replace('.', ',', $idcurso) . ") GROUP BY serie ORDER BY serie ASC";

        $result = mysql_query($query);
        //echo '<option value="11111" selected="selected">'.$query.'</option>';
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
        }
    }
} elseif ($action == "recebeSeriesComTurmas3") {
    if ($idcurso != "todos") {
        $p = '';
        foreach ($idcurso as $r) {
            $p .= str_replace('.', ',', $r) . ', ';
        }



        $p = substr($p, 0, -2);
        $query = "SELECT GROUP_CONCAT(series.id) id, series.serie FROM series, turmas WHERE turmas.idserie=series.id AND series.idcurso in(" . $p . ") GROUP BY serie ORDER BY serie ASC";

        $result = mysql_query($query);
        //echo '<option value="11111" selected="selected">'.$query.'</option>';
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['serie'] . '</option>';
        }
    }
} elseif ($action == "recebePontosAprovacao") {
    $query = "SELECT pontosaprovacao FROM series WHERE id=$idserie";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo number_format($row['pontosaprovacao'], 2, ',', '.') . "|" . $row['pontosaprovacao'];       // money_format('%.2n', $row['valorAnuidade'])
} elseif ($action == "recebeMediaAprovacao") {
    $query = "SELECT mediaaprovacao FROM series WHERE id=$idserie";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo number_format($row['mediaaprovacao'], 2, ',', '.') . "|" . $row['mediaaprovacao'];       // money_format('%.2n', $row['valorAnuidade'])
} elseif ($action == "recebeAnuidade") {
    $query = "SELECT valorAnuidade FROM series WHERE id=$idserie";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo number_format($row['valorAnuidade'], 2, ',', '.') . "|" . $row['valorAnuidade'];       // money_format('%.2n', $row['valorAnuidade'])
}
