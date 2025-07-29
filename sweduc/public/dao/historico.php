<?php
include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

require_once("mysql.class.php");

$conn = new db();
$conn->open();

$agora = date("Y-m-d H:i:s");


$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = addslashes($_POST[$k]);
}

$idfuncionario = trim($idfuncionario);


if (($idfuncionario == "") || ($idfuncionario == 0)) {
    $query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
    $result1 = mysql_query($query1);
    $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
    $idfuncionario = $row1['id'];
}

if ($action == "deleta_serie") {
    $serie = trim($serie);

    $sql = "delete from alunos_historico where idaluno = $idaluno and serie='$serie' and ano = '$ano' ";

    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $sql, "DEBUG");

    mysql_query($sql);

    echo "green|Registro excluido com sucesso!";
    $msg = "Serie escluida com sucesso!";

    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}

if ($action == "deleta_materia") {
    $sql = "delete from alunos_historico where idaluno = $idaluno and disciplina='$materia' and serie = '$serie' ";
    mysql_query($sql);

    echo "green|Registro excluido com sucesso!";
    $msg = "Serie escluida com sucesso!";

    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}

if ($action == "cadastra") {
    $query  = "SELECT COUNT(*) as cnt FROM alunos_historico WHERE idaluno=$idalunoCadastra AND escola='$escolaCadastra' AND disciplina='$disciplinaCadastra' AND serie='$serieCadastra'";

    $result = mysql_query($query);

    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($disciplinaCadastra != "") {
        if ($row['cnt'] == 0) {
            //verifica se ja existe alguma materia com o mesmo nome para poder o mesmo numero de ordem
            $sql = "select * from alunos_historico where idaluno = $idalunoCadastra and disciplina = '$disciplinaCadastra' ";
            $rs = new query($conn, $sql);

            $ordem = 0;

            if ($rs->getrow()) {
                $ordem = $rs->field("ordem");
            } else {
                //pega o numero mais alto das ordens das materias e acrescenta um para a proxima materia
                $sql = "select max(ordem) as ordem from alunos_historico where idaluno = $idalunoCadastra ";
                $rs_ordem = new query($conn, $sql);

                if ($rs_ordem->getrow()) {
                    $ordem = $rs_ordem->field("ordem") + 1;
                } else {
                    $ordem = 1;
                }
            }

            $query1  = "INSERT INTO alunos_historico (idaluno, nomealuno, ano, escola, local, serie, disciplina, media, cargahoraria, frequencia, situacao, carga_horaria_total, ordem) VALUES ($idalunoCadastra, '$nomealunoCadastra', '$anoCadastra', '$escolaCadastra', '$localCadastra', '$serieCadastra', '$disciplinaCadastra', '$mediaCadastra', '$cargahorariaCadastra', '$frequenciaCadastra', '$situacaoCadastra', '$cargahorariatotal', '$ordem')";

            if ($result1 = mysql_query($query1)) {
                $sql = "update alunos_historico set carga_horaria_total = '$cargahorariatotal' ";
                $sql .= "where idaluno = '$idalunoCadastra' and serie = '$serieCadastra' ";
                //$sql .= "where idaluno = '$idalunoCadastra' and ano = '$anoCadastra' and escola = '$escolaCadastra' ";
                mysql_query($sql);

                $msg = "Dados cadastrados com sucesso.";
                echo "green|Dados cadastrados com sucesso.";
            } else {
                $msg = "Erro ao cadastrar dados.";
                echo "red|Erro ao cadastrar dados.";
            }
        } else {
            $msg = "Dados j� cadastrados!";
            echo "red|Dados ja cadastrados!";
        }
    }

    //atualiza a obs do aluno
    $sql = "update alunos set obs_fundamental = '$obsfundamental', obs_medio = '$obsmedio', obs_individual = '$obsindividual' where id = $idalunoCadastra";
    mysql_query($sql);

    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apaga") {
      $query1  = "DELETE FROM alunos_historico WHERE id=" . $id;
    if ($result1 = mysql_query($query1)) {
            $query  = "SELECT * FROM alunos_historico WHERE idaluno=$idaluno GROUP BY ano,escola,serie DESC";
            $result = mysql_query($query);
        echo "blue|";
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
                    <tr>
              <td><?=$row['escola']?><br /><?=$row['local']?></td>
              <td><?=$row['ano']?></td>
              <td><?=str_replace("@&@", " ", $row['serie'])?></td>
              <td><?=$row['cargahoraria']?>h</td>
              <td><?=$row['frequencia']?></td>
              <td> <?php
                $query1  = "SELECT disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['disciplina'] . "<br /><br /><br />";
                }
                ?></td>
              <td> <?php
                $query1  = "SELECT media,disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['media'] . "<br /><br /><br />";
                }
                ?></td>
<!--
              <td> <?php
                $query1  = "SELECT cargahoraria,disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['cargahoraria'] . "h<br /><br /><br />";
                }
                ?></td>
              <td> <?php
                $query1  = "SELECT frequencia,disciplina FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['frequencia'] . "<br /><br /><br />";
                }
                ?></td>
-->
              <td> <?php
                $query1  = "SELECT * FROM alunos_historico WHERE idaluno=$idaluno AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) { ?>
                   <input type="button" class="button" value=" E " onClick="editaHistorico(<?="'" . implode("','", $row1) . "'"?>)" />
                   <input type="button" class="button bgred" value=" X " onClick="apagaHistorico('<?=$row1['id']?>','<?=$idaluno?>')" />
                <?php } ?>
             </td>
            </tr>
        <?php    }
        $msg = "Erro ao excluir dados.";
    } else {
        echo "red|Erro ao excluir dados.";
        $msg = "Erro ao excluir dados.";
    }
    $parametroscsv = $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "atualizaEstab") {
    $query1  = "UPDATE alunos_historico SET ano='$ano', escola='$escola', local='$local', situacao='$situacao' WHERE idaluno=$idaluno AND serie='$serie'";

    if ($result1 = mysql_query($query1)) {
        $sql = "update alunos_historico set carga_horaria_total = $carga_horaria_total where idaluno = '$idaluno' and serie = '$serie' ";
        mysql_query($sql);

        echo "Dados atualizados com sucesso.";
    } else {
        echo "Erro ao atualizar dados.";
    }
    //echo $query1;
} elseif ($action == "atualizaNotaCH") {
    $sql  = "UPDATE alunos_historico SET ordem='$ordem' WHERE idaluno=$idaluno AND disciplina='$disciplina' ";
    mysql_query($sql);

    $query1  = "UPDATE alunos_historico SET media='$media', cargahoraria='$cargahoraria' WHERE idaluno=$idaluno AND serie='$serie' AND disciplina='$disciplina'";
    if ($result1 = mysql_query($query1)) {
        echo "Dados atualizados com sucesso.";
    } else {
        echo "Erro ao atualizar dados.";
    }
} elseif ($action == "atualizaFreq") {
    $query1  = "UPDATE alunos_historico SET frequencia='$frequencia' WHERE idaluno=$idaluno AND serie='$serie'";

    if ($result1 = mysql_query($query1)) {
        echo "Dados atualizados com sucesso.";
    } else {
        echo "Erro ao atualizar dados.";
    }
} elseif ($action == "apagaAntigo") {
      $query1  = "DELETE FROM alunos_historico WHERE id=" . $id;
    if ($result1 = mysql_query($query1)) {
        echo "blue|";
            $query  = "SELECT * FROM alunos_historico WHERE nomealuno='$nomealuno' GROUP BY ano,escola,serie DESC";
            $result = mysql_query($query);
// if ($debug) echo $query;

        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
                    <tr>
              <td><?=$row['escola']?><br /><?=$row['local']?></td>
              <td><?=$row['ano']?><br /><?=$row['situacao']?></td>
              <td><?=str_replace("@&@", " ", $row['serie'])?></td>
              <td><?=$row['cargahoraria']?>h</td>
              <td><?=$row['frequencia']?></td>
              <td> <?php
                $query1  = "SELECT disciplina FROM alunos_historico WHERE nomealuno='$nomealuno' AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['disciplina'] . "<br /><br /><br /><br /><br />";
                }
                ?></td>
              <td> <?php
                $query1  = "SELECT media,disciplina FROM alunos_historico WHERE nomealuno='$nomealuno' AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['media'] . "<br /><br /><br /><br /><br />";
                }
                ?></td>
<!--
              <td> <?php
                $query1  = "SELECT cargahoraria,disciplina FROM alunos_historico WHERE nomealuno='$nomealuno' AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['cargahoraria'] . "h<br /><br /><br />";
                }
                ?></td>
              <td> <?php
                $query1  = "SELECT frequencia,disciplina FROM alunos_historico WHERE nomealuno='$nomealuno' AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "'  AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                $result1 = mysql_query($query1);
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                    echo $row1['frequencia'] . "<br /><br /><br />";
                }
                ?></td>
-->

               <?php
                if ($perfil != "ALUNO") {
                    echo "<td>";
                        $query1  = "SELECT * FROM alunos_historico WHERE nomealuno='$nomealuno' AND ano='" . $row['ano'] . "' AND escola='" . $row['escola'] . "' AND local='" . $row['local'] . "' AND serie='" . $row['serie'] . "'  ORDER BY disciplina DESC";
                        $result1 = mysql_query($query1);
                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) { ?>
                     <input type="button" class="button" value=" E " onClick="editaHistorico(<?="'" . implode("','", $row1) . "'"?>)" />
                     <input type="button" class="button bgred" value=" X " onClick="apagaHistorico('<?=$row1['id']?>','$nomealuno')" />
                     <br /><br /><br /> <?php
                    }
                    echo "</td>";
                }
                ?>

            </tr>
        <?php    }
    } else {
        echo "red|Erro ao excluir dados.";
    }
}

?>
