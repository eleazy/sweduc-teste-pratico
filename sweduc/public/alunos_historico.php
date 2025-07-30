<?php

use App\Academico\Model\Historico;
use App\Model\Core\ConfiguracaoKV;

include 'headers.php';
include 'dao/conectar.php';

$doc = 0;
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}
include 'permissoes.php';

$alunos = explode(",", $idaluno);
$arrAnosHistorico = explode(",", $anosHistorico);
$anosHistorico = "'" . str_replace(",", "','", $anosHistorico) . "'";
$frequenciaPadrao = $_REQUEST['frequencia'] ?? null;
$cargaHoraria = !filter_var($_REQUEST['ocultarCargaHoraria'] ?? false, FILTER_VALIDATE_BOOL);
$colspan = $cargaHoraria ? 2 : 1;

$ordem = ConfiguracaoKV::chave('HISTORICO_ORDEM') ?? 'padrao';

if (!function_exists('disciplinas')) {
    function disciplinas($idaluno, $anosHistorico, $ordem = 'padrao')
    {
        $disciplinas = [];
        $query = "SELECT DISTINCT
                ah.disciplina
            FROM alunos_historico ah
            LEFT JOIN disciplinas d ON d.disciplina = ah.disciplina
            WHERE ah.disciplina NOT LIKE '%faltas%'
            AND idaluno='$idaluno'
            AND serie IN ($anosHistorico)";

        if ($ordem == 'alfabetica') {
            $query .= " ORDER BY ah.disciplina";
        } else {
            $query .= " ORDER BY COALESCE(numordem, 9999), ordem, ah.disciplina";
        }

        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $disciplinas[] = $row;
        }

        return $disciplinas;
    }
}

foreach ($alunos as $key => $idaluno) :
    $query1 = "SELECT nome FROM pessoas, alunos WHERE alunos.id=$idaluno AND pessoas.id=alunos.idpessoa";
    $result1 = mysql_query($query1);
    $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
    $nomealuno = $row1['nome'];
    ?>

    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="content-table">
        <tr>
            <td>
                <table border="0" width="100%" cellpadding="50" cellspacing="50" class="table1">
                    <thead>
                        <tr>
                            <th>Disciplina</th>
                            <?php foreach ($arrAnosHistorico as $ah) : ?>
                                <th width="55px" style="white-space: nowrap;"><?= $ah ?></th>
                                <?php if ($cargaHoraria) : ?>
                                    <th width="30px" style="white-space: nowrap;">CH</th>
                                <?php endif ?>
                            <?php endforeach ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($arrAnosHistorico as $ah) {
                            $arrNotas[$ah] = ' - ';
                            $arrCH[$ah] = ' - ';
                            $cargahoraria[$ah] = ' - ';
                            $frequencia[$ah] = ' - ';
                            $estabelecimentoensino[$ah]['ano'] = ' - ';
                            $estabelecimentoensino[$ah]['serie'] = $ah;
                            $estabelecimentoensino[$ah]['escola'] = ' - ';
                            $estabelecimentoensino[$ah]['local'] = ' - ';
                            $estabelecimentoensino[$ah]['situacao'] = ' - ';
                        }

                        $query = "SELECT *, SUM(cargahoraria) as ch FROM alunos_historico WHERE idaluno=$idaluno GROUP BY ano,escola,serie ORDER BY ano ASC";
                        $result = mysql_query($query);
                        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                            $estabelecimentoensino[$row['serie']]['ano'] = $row['ano'];
                            $estabelecimentoensino[$row['serie']]['serie'] = $row['serie'];
                            $estabelecimentoensino[$row['serie']]['escola'] = $row['escola'];
                            $estabelecimentoensino[$row['serie']]['local'] = $row['local'];
                            $estabelecimentoensino[$row['serie']]['situacao'] = $row['situacao'];
                            $cargahoraria[$row['serie']] = $row['ch'];
                            $frequencia[$row['serie']] = $row['frequencia'];
                        }

                        $linha = 0;
                        foreach (disciplinas($idaluno, $anosHistorico, $ordem) as $row) {
                            $historico = Historico::where('idaluno', $idaluno)
                                ->where('disciplina', $row['disciplina'])
                                ->whereIn('serie', $arrAnosHistorico)
                                ->get();

                            if ($historico->every(fn(Historico $value) => empty($value->media) || $value->media === '-')) {
                                continue;
                            }

                            $linha++;
                            echo "<tr>";
                            echo "<td>" . $row['disciplina'] . "</td>";

                            foreach ($arrAnosHistorico as $k => $ah) {
                                $row1 = $historico
                                    ->where('serie', $ah)
                                    ->filter(fn($item) => !is_null($item->media))
                                    ->first()?->toArray();

                                ?>
                                <td style='text-align:center;'>
                                    <span id="txtnota<?= $idaluno . $linha . $k ?>">
                                        <?php
                                        if (trim($row1['media']) == "") {
                                            echo " - ";
                                        } else {
                                            echo $row1['media'];
                                        }
                                        ?></span><br>
                                </td>

                                <?php if ($cargaHoraria) : ?>
                                    <td style='text-align:center;'>
                                        <span id="txtch<?= $idaluno . $linha . $k ?>">
                                            <?php
                                            if (trim($row1['cargahoraria']) == "" || trim($row1['cargahoraria']) == "0") {
                                                echo " - ";
                                            } else {
                                                echo $row1['cargahoraria'];
                                            }
                                            ?></span><br>
                                    </td>
                                <?php endif ?>
                                <?php
                            }
                            echo "</tr>";
                        }

                        if ($cargaHoraria) {
                            echo "<tr><td><b>Carga Horária</b></td>";
                            foreach ($arrAnosHistorico as $k => $ah) {
                                $sql = "select * from alunos_historico where idaluno = $idaluno and serie = '$ah' ";
                                $result = mysql_query($sql);

                                $carga_horaria_total = 0;

                                while ($row_carga = mysql_fetch_array($result, MYSQL_ASSOC)) {
                                    $carga_horaria_total = $row_carga['carga_horaria_total'];
                                }

                                echo "<td style='text-align:center;' colspan='$colspan'>";
                                if ($carga_horaria_total == 0) {
                                    if (trim($cargahoraria[$ah]) == "" || trim($cargahoraria[$ah]) == "0") {
                                        echo "-";
                                    } else {
                                        echo $cargahoraria[$ah];
                                    }
                                } else {
                                    if (trim($carga_horaria_total) == "" || trim($carga_horaria_total) == "0") {
                                        echo "-";
                                    } else {
                                        echo $carga_horaria_total;
                                    }
                                }
                            }

                            echo "</td>";
                            echo "</tr>";
                        }

                        echo "<tr><td><b>% Freq.</b></td>";
                        foreach ($arrAnosHistorico as $k => $ah) {
                            echo "<td style='text-align:center;' colspan='$colspan'>";
                            if ($frequenciaPadrao) {
                                echo $frequenciaPadrao;
                            } elseif (trim($frequencia[$ah]) == "" || trim($frequencia[$ah]) == "0" || trim($frequencia[$ah]) == "-") {
                                echo "";
                            } else {
                                echo $frequencia[$ah] . "%";
                            }
                            echo "</td>";
                        }
                        echo "</tr>";
                        ?>
                    </tbody>
                </table>
                <br />
                <table border="0" width="100%" class="table1">
                    <thead>
                        <tr>
                            <th>Série</th>
                            <th>Ano</th>
                            <th>Estabelecimento de Ensino</th>
                            <th>Município-UF</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($estabelecimentoensino as $k => $eensino) {
                            $filterArr = array_filter($eensino, fn($v, $k) => $k == 'serie' ? '' : trim($v), ARRAY_FILTER_USE_BOTH);
                            $reduceArr = array_reduce($filterArr, fn($a, $b) => $a . $b, '');
                            if (empty($reduceArr)) {
                                continue;
                            }

                            if (in_array($eensino['serie'], $arrAnosHistorico)) {
                                ?>
                                <tr>
                                    <td><?= $eensino['serie'] ?></td>
                                    <td><span id="txtano<?= $idaluno . $k ?>"><?= $eensino['ano'] ?></span></td>
                                    <td><span id="txtescola<?= $idaluno . $k ?>"><?= $eensino['escola'] ?></span></td>
                                    <td><span id="txtlocal<?= $idaluno . $k ?>"><?= $eensino['local'] ?></span></td>
                                    <td><span id="txtsituacao<?= $idaluno . $k ?>"><?= $eensino['situacao'] ?></span></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <?php if ($key < count($alunos) - 1) : ?>
        <div style="page-break-after:always">&nbsp;</div>
    <?php endif ?>
<?php endforeach ?>

<script>
    $('#loader').hide();

    function update_this_rows() {
        $(".table1 tbody").find("tr:even").css("background-color", "#aaa");
        $(".table1 tbody").find("tr:odd").css("background-color", "#eee");
    }
</script>
