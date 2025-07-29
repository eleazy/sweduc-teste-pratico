<?php

include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
include(__DIR__ . '/../permissoes.php');
$agora = date("Y-m-d H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$ignoraNoBoletim = isset($ignoraNoBoletim) ? 1 : 0;

if ($action == "cadastra") {
    if ($id == "0") {
        $query = "INSERT INTO disciplinas (idanoletivo, numordem, disciplina, abreviacao, basenacional, descricao, ignoraNoBoletim) VALUES ($idanoletivo, '$numordem', '$disciplina', '$abreviacao', $basenacional, '$descricao', '$ignoraNoBoletim');";
        if ($result = mysql_query($query)) {
            echo "blue|Disciplina $disciplina cadastrada com sucesso.";
            $msg = "Disciplina $disciplina cadastrada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao cadastrar nova disciplina.";
            $msg = "Erro ao cadastrar nova disciplina.";
            $status = 1;
        }
        $parametroscsv = $idanoletivo . ',' . $numordem . ',' . $disciplina . ',' . $abreviacao . ',' . $basenacional;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else { //UPDATE
        $query = "UPDATE disciplinas SET numordem='$numordem', disciplina='$disciplina', abreviacao='$abreviacao', basenacional='$basenacional', descricao='$descricao', ignoraNoBoletim='$ignoraNoBoletim' WHERE id=" . $id;

        if ($result = mysql_query($query)) {
            echo "blue|Disciplina $disciplina atualizada com sucesso.";
            $msg = "Disciplina $disciplina atualizada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ($erro) ao atualizar disciplina.";
            $msg = "Erro ($erro) ao atualizar disciplina.";
            $status = 1;
        }
        $parametroscsv = $id . ',' . $numordem . ',' . $disciplina . ',' . $abreviacao . ',' . $basenacional;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "apaga") {
    $query = "DELETE FROM disciplinas WHERE id=$id";
    $queryPegaDis = "SELECT disciplina FROM disciplinas WHERE id=$id";
    $resultadoDis = mysql_query($queryPegaDis);
    while ($linhaTurma = mysql_fetch_array($resultadoDis)) {
        $disDeletada = $linhaTurma['disciplina'];
    }
    $msg = "Disciplina: " . $disDeletada . " removida com sucesso.";
    if ($result = mysql_query($query)) {
        echo "blue|Disciplina removida com sucesso.";

        $status = 0;
    } else {
        echo "red|Erro ao remover disciplina.";
        $msg = "Erro ao remover disciplina.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeDisciplinas") {
    $filtro_anoletivo = !empty($idanoletivo) ? "AND grade.idanoletivo=" . mysql_real_escape_string($idanoletivo) : '';

    $query = "SELECT disciplina, disciplinas.id as did FROM grade, disciplinas WHERE grade.iddisciplina=disciplinas.id AND grade.idturma=$idturma AND grade.idserie=$idserie $filtro_anoletivo GROUP BY disciplinas.id ORDER BY disciplina ASC";
    // echo $query;return;
    $result = mysql_query($query);
//echo '<option value="-1">'.$query.'</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['did'] . '">' . $row['disciplina'] . '</option>';
    }
} elseif ($action == "recebeDisciplinasPorAno") {
    $query = "SELECT disciplina, disciplinas.id as did, abreviacao FROM grade, disciplinas WHERE grade.idturma=" . $idturma . " AND grade.iddisciplina=disciplinas.id AND grade.idanoletivo=" . $idanoletivo . " ORDER BY disciplinas.numordem, disciplina ASC";
    $result = mysql_query($query); //echo '<option value="-1">'.$query.'</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['did'] . '@' . $row['abreviacao'] . '">' . $row['disciplina'] . '</option>';
    }
} elseif ($action == "avaliacoesAno") {
    // $query = "SELECT id,avaliacao FROM avaliacoes WHERE idanoletivo=".$idanoletivo;
    // $result = mysql_query($query);//echo '<option value="-1">'.$query.'</option>';
    // while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    //   echo '<option value="'.$row['id'].'@'.$row['avaliacao'].'">'.$row['avaliacao'].'</option>';

    $query  = "SELECT anoletivo FROM anoletivo WHERE id=" . $idanoletivo;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $anoletivo = $row['anoletivo'];
    $alunosnotas = 'alunos_notas';

    $query = "SELECT an.idmedia, an.idavaliacao, dc.id, dc.disciplina,GROUP_CONCAT(DISTINCT concat(av.id,'***',av.avaliacao) SEPARATOR '##') as itemavaliacao
            FROM " . $alunosnotas . " an
            INNER JOIN medias md ON an.idmedia=md.id
            INNER JOIN avaliacoes av ON an.idavaliacao=av.id
            INNER JOIN grade gd ON md.idgrade=gd.id
            INNER JOIN disciplinas dc ON gd.iddisciplina=dc.id
            WHERE dc.id=" . $iddisciplina . "  AND av.idanoletivo=" . $idanoletivo . " AND md.idperiodo=" . $idperiodo . "
            GROUP BY dc.disciplina";
    $result = mysql_query($query); //echo '<option value="-1">'.$query.'</option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $listatd = explode('##', $row['itemavaliacao']);

        for ($i = 0; $i < count($listatd); $i++) {
            $aval = explode('***', $listatd[$i]);
            echo '<option value="' . $aval[0] . '@' . $aval[1] . '">' . $aval[1] . '</option>';
        }
    }
} elseif ($action == "avaliacoesAnoDisc") {
    $disciplinas = json_encode($disciplinas, JSON_THROW_ON_ERROR);

    $disciplinas = str_replace(['[', ']', '"'], ['', '', ''], $disciplinas);

    $fserie = (!empty($idserie) && $idserie > 0) ? ' AND gd.idserie=' . $idserie : '';

    // $query = "SELECT an.idmedia, an.idavaliacao, dc.id as did, dc.disciplina,GROUP_CONCAT( DISTINCT ( concat(av.id,'***',av.avaliacao,'***',dc.id) ) SEPARATOR '##') as itemavaliacao
    $ldisciplinas = explode(',', $disciplinas);

    $loptions = '';

    $query  = "SELECT anoletivo FROM anoletivo WHERE id=" . $idanoletivo;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $anoletivo = $row['anoletivo'];
    $alunosnotas = 'alunos_notas';

    for ($j = 0; $j < count($ldisciplinas); $j++) {
        $query = "SELECT concat(av.id,'***',av.avaliacao,'***',dc.id) as itemavaliacao, an.idmedia, an.idavaliacao, dc.id as did, dc.disciplina
              FROM " . $alunosnotas . " an
              INNER JOIN medias md ON an.idmedia=md.id
              INNER JOIN avaliacoes av ON an.idavaliacao=av.id
              INNER JOIN grade gd ON md.idgrade=gd.id
              INNER JOIN disciplinas dc ON gd.iddisciplina=dc.id

              inner join anoletivo a on a.id = gd.idanoletivo
              WHERE dc.id = " . $ldisciplinas[$j] . "
             --  AND av.idanoletivo=" . $idanoletivo . "
              AND md.idperiodo=" . $idperiodo . $fserie . "

               and gd.idanoletivo = " . $idanoletivo . "
                and gd.idturma = " . $idturma . "

                    and year(an.datahora) = a.anoletivo
                          GROUP BY itemavaliacao";




        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            // $listatd = explode('##',$row['itemavaliacao']);
            // for($i=0;$i<count($listatd);$i++) {
            $aval = explode('***', $row['itemavaliacao']);
            $loptions .= '<option q="' . $query . '" value="' . $aval[0] . '@' . $aval[1] . '@' . $row['did'] . '">' . $aval[1] . ' - ' . $row['disciplina'] . '</option>';
            // $loptions.= '<option value="' . $aval[0] . '@' . $aval[1] . '@' . $row['did'] . '">' . $aval[1] . ' - ' . $row['disciplina'] . '</option>';
            // }
        }
    }

    echo $loptions;
} elseif ($action == "turmaDisciplina") {
    $p = '';
    foreach ($idturma as $row) {
        $p .= $row . ', ';
    }
    $p = substr($p, 0, -2);
    $query = "SELECT
                    CONCAT(d.id) id, d.disciplina
                FROM
                    grade g
                        INNER JOIN
                    disciplinas d ON g.iddisciplina = d.id
                WHERE
                    idturma IN (" . $p . ")
                GROUP BY d.id
                ORDER BY d.numordem";

    $result = mysql_query($query);

    echo '<option value=""></option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['disciplina'] . '</option>';
    }
} elseif ($action == "disciplinaAvaliacao") {
    $query  = "SELECT anoletivo FROM anoletivo WHERE id=" . $idanoletivo;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $anoletivo = $row['anoletivo'];
    $alunosnotas = 'alunos_notas';

    $query = "SELECT
                    CONCAT(av.id) AS id,
                    av.avaliacao,
                    an.idmedia,
                    an.idavaliacao,
                    dc.id AS did,
                    dc.disciplina
                FROM
                    " . $alunosnotas . " an
                        INNER JOIN
                    medias md ON an.idmedia = md.id
                        INNER JOIN
                    avaliacoes av ON an.idavaliacao = av.id
                        INNER JOIN
                    grade gd ON md.idgrade = gd.id
                        INNER JOIN
                    disciplinas dc ON gd.iddisciplina = dc.id
                WHERE
                    gd.id IN (" . $iddisciplina . ")
                        AND av.idanoletivo = " . $idanoletivo . "
                        AND md.idperiodo = " . $idperiodo . "
                GROUP BY av.id";
    $result = mysql_query($query);

    //echo '<option value="">'  . $query . '</option>';

    echo '<option value=""></option>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['id'] . '">' . $row['avaliacao'] . '</option>';
    }
} elseif ($action == "recebeDisciplinas2") {
    $query = "SELECT
            disciplina, disciplinas.id as did, disciplinas.numordem
        FROM
            grade,
            disciplinas
        WHERE
            grade.iddisciplina=disciplinas.id AND
            grade.idturma=$idturma AND
            grade.idserie=$idserie AND
            grade.idanoletivo=$idanoletivo
        ORDER BY disciplinas.numordem ASC";

    $result = mysql_query($query);
    $disciplinaAparecer = [];
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        if (!in_array($row['numordem'], $disciplinaAparecer)) {
            if ($row['numordem'] < 0) {
                $disciplinaAparecer[] = abs($row['numordem']);
            }
            echo '<option value="' . $row['did'] . '">' . $row['disciplina'] . '</option>';
        }
    }
} elseif ($action == "cadastraeletiva") {
    // verifica se já existe
    $queryel = "SELECT * FROM alunos_eletivas WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";
    $resultel = mysql_query($queryel);
    $quant = mysql_num_rows($resultel);

    if (isset($lancamentonotas) && $lancamentonotas == 1) {
        if ($quant > 0) {
            $query = "UPDATE alunos_eletivas SET obs='$observacao' WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";

            if ($result = mysql_query($query)) {
                echo "blue|Observação atualizada com sucesso.|1";
                $msg = "Observação atualizada com sucesso.";
                $status = 0;
            } else {
                echo "red|Erro ao atualizar.|0|" . mysql_error();
                $msg = "Erro ao atualizar.";
                $status = 1;
            }
        }
    } else {
        if ($quant < 1) {
            $query = "INSERT INTO alunos_eletivas (idanoletivo, idaluno, ideletiva, nummatricula,obs ) VALUES ('$idanoletivo', '$idaluno', '$ideletiva', '$nummatricula','$observacao');";

            if ($result = mysql_query($query)) {
                echo "blue|Disciplina cadastrada com sucesso.|1";
                $msg = "Disciplina cadastrada com sucesso.";
                $status = 0;
            } else {
                echo "red|Erro ao cadastrar nova disciplina.|0|" . mysql_error();
                $msg = "Erro ao cadastrar nova disciplina.";
                $status = 1;
            }
        } else {
            $query = "DELETE FROM alunos_eletivas WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";
            if ($result = mysql_query($query)) {
                echo "blue|Removido com sucesso.|0";
                $msg = "Removido com sucesso.";
                $status = 0;
            } else {
                echo "red|Erro ao remover.|1|" . mysql_error();
                $msg = "Erro ao remover.";
                $status = 1;
            }
        }
    }






    // if($cadastra==1) {
    //     // verifica se já existe
    //     $queryel = "SELECT * FROM alunos_eletivas WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";
    //     $resultel = mysql_query($queryel)
    //     $quant = mysql_num_rows($resultel);

    //     if($quant>0) {
    //         $query = "INSERT INTO alunos_eletivas (idanoletivo, idaluno, ideletiva, nummatricula,observacao ) VALUES ('$idanoletivo', '$idaluno', '$ideletiva', '$nummatricula','$observacao');";
    //     } else {
    //         $query = "UPDATE alunos_eletivas SET observacao='$observacao' WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";
    //     }

    //     if ($result = mysql_query($query)) {
    //         echo "blue|Disciplina cadastrada com sucesso.";
    //         $msg = "Disciplina cadastrada com sucesso.";
    //         $status = 0;
    //     } else {
    //         echo "red|Erro ao cadastrar nova disciplina.".mysql_error();
    //         $msg = "Erro ao cadastrar nova disciplina.";
    //         $status = 1;
    //     }
    // } else {
    //     $query = "DELETE FROM alunos_eletivas WHERE idanoletivo=$idanoletivo AND idaluno=$idaluno AND ideletiva=$ideletiva AND nummatricula=$nummatricula";
    //     if ($result = mysql_query($query)) {
    //         echo "blue|Removido com sucesso.";
    //         $msg = "Removido com sucesso.";
    //         $status = 0;
    //     } else {
    //         echo "red|Erro ao remover.".mysql_error();
    //         $msg = "Erro ao remover.";
    //         $status = 1;
    //     }
    // }
}
