<?php
include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");
$agorahumano = date("d/m/Y H:i:s");

$debug = 0;

$keys = array_keys($_REQUEST);
foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}
$numeroaluno = ltrim($numeroaluno, '0');

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

if ($action == "cadastra") {
    $numeroaluno = ($manual == 1) ? $numeroaluno : substr($numeroaluno, 0, -1);
    $query  = "SELECT *, alunos.id as aid FROM alunos, pessoas WHERE alunos.idpessoa=pessoas.id AND ( numeroaluno='$numeroaluno' OR CONCAT('0',numeroaluno)='$numeroaluno')";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $idaluno = $row['aid'];
    $alunonome = $row['nome'];
    if ($debug == 1) {
        echo $query . " " . $result . "<br />";
    }

    $query  = "INSERT INTO portaria(idaluno, idunidade, dataportaria, tipo) VALUES ($idaluno, $idunidade, '$agora', '$tipo');";
    if ($result = mysql_query($query)) {
        $lastid = mysql_insert_id();
        echo "blue|<tr id='row" . $lastid . "'><td>" . $unidade . "</td><td>" . $alunonome . "</td><td>" . $numeroaluno . "</td><td>" . $tipo . "</td><td>" . $agorahumano . "h</td><td><input type='button' class='button bgred' value=' X ' onClick='apaga(" . $lastid . ");' ></td></tr>";

        $msg = "Dados de portaria do aluno inseridos com sucesso.";
        $status = 0;
    } else {
        echo "Erro ao incluir dados da portaria do aluno. " . $numeroaluno;
        $msg = "Erro ao incluir dados da portaria do aluno.";
        $status = 1;
    }
    $parametroscsv = $idaluno . ',' . $numeroaluno . ',' . $alunonome;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "lista") {
    $sql = " ";
    $sqlfrom = " ";
    if (trim($nomealuno) != "") {
        $sql .= " AND pessoas.nome LIKE '%$nomealuno%' ";
    }
    if (trim($idtipo) != "") {
        $sql .= " AND portaria.tipo='$idtipo' ";
    }
    $sql .= $idturma > 0 ? " AND turmas.id='$idturma'" : '';

    $dataexp = explode('/', $periodode);
    $ano_incial = $dataexp[2];
    $data1 = $dataexp[2] . '-' . $dataexp[1] . '-' . $dataexp[0];
    $dataexp = explode('/', $periodoate);
    $ano_final = $dataexp[2];
    $data2 = $dataexp[2] . '-' . $dataexp[1] . '-' . $dataexp[0];
    $anos_letivos = $ano_incial != $ano_final ? "'$ano_incial', '$ano_final'" : $ano_incial;

    $query  = "SELECT alunos.id as aid, portaria.id as portid, pessoas.nome, alunos.numeroaluno, portaria.tipo,dataportaria, DATE_FORMAT(dataportaria,'%d/%m/%Y %H:%i:%s') as dtportaria, DATE_FORMAT(dataportaria,'%H:%i:%s') as horaportaria, turmas.turma, planohorarios.saida, TIMEDIFF('" . $row['horaportaria'] . "',planohorarios.saida) as excedente, codigo, DATE_FORMAT(planohorarios.entrada,'%H:%i') as ent, DATE_FORMAT(planohorarios.saida,'%H:%i') as sai, TIMEDIFF(planohorarios.saida,planohorarios.entrada) AS difhoras, anoletivo.id as idanoletivo FROM alunos, pessoas, portaria JOIN alunos_matriculas ON alunos_matriculas.idaluno=portaria.idaluno JOIN turmas ON alunos_matriculas.turmamatricula=turmas.id JOIN anoletivo ON alunos_matriculas.anoletivomatricula=anoletivo.id AND anoletivo IN ($anos_letivos) JOIN planohorarios ON alunos_matriculas.idplanohorario=planohorarios.id " . $sqlfrom . " WHERE alunos.idpessoa=pessoas.id AND portaria.idaluno=alunos.id AND DATE(dataportaria) BETWEEN '$data1' AND '$data2' AND portaria.idunidade=$idunidade AND turmas.id=alunos_matriculas.turmamatricula AND alunos_matriculas.status = 1 " . $sql . " ORDER BY dataportaria DESC";

    $result = mysql_query($query);
    $volta = "";
    $csv = "-,Unidade,Nome,Turma,Numero Aluno,Tipo,Data - Hora,Per. Excedente,Hora Excedente\r\n";
    $i = 1;
    if ($debug == 1) {
        echo $query . " " . $result . "<br />";
    }
    $expldata = explode('/', $periodode);

    $reghorarios = [];
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $queryFin = "SELECT COUNT(*) as cnt FROM
											alunos_fichafinanceira ff
												INNER JOIN
											alunos_matriculas am ON ff.nummatricula = am.nummatricula
										WHERE
											situacao = 0
												AND datavencimento < CURDATE()
												AND ff.idaluno = " . $row['aid'] . "
                        AND am.idaluno = " . $row['aid'];
        $sqlQueryFin = mysql_query($queryFin);
        $resultQueryFin = mysql_fetch_array($sqlQueryFin, MYSQL_ASSOC);
        $situacaoAluno = ($resultQueryFin['cnt'] > 0) ? '<br /><span class="label label-danger">Inadimplente</span>' : '<br /><span class="label label-success">Adimplente</span>';

        $volta .= "<tr id='row" . $row['portid'] . "'><td>" . $i . "</td><td>" . $unidade . "</td><td>" . $row['nome'] . "<br />" . $row['numeroaluno'] . $situacaoAluno . "</td><td>";
        $csv .= $i . "," . $unidade . "," . $row['nome'] . ",";
        $saida = 0;
        $plano = "";
        $numaluno = $row['aid'];

        if ($saida < $row['excedente']) {
            $saida = $row['excedente'];
        }
        $volta .= $row['turma'] . "<br />";
        $plano .= $row['codigo'] . "<br /><span class='planhor'>Entrada:" . $row['ent'] . "h<br />Saída:" . $row['sai'] . "h<br /></span><span class='difhor'>Horas: " . substr($row['difhoras'], 0, 5) . "h</span>";
        $csv .= $row['turma'] . " ";
        $horasplano = $row['difhoras'];
        $horassaida = $row['saida'];

        $volta .= "</td><td>" . $plano . "</td><td>" . $row['tipo'] . "</td><td>" . $row['dtportaria'] . "h";
        $volta .= (trim($row['tipo']) != "Entrada") ? "<br><span class='difhor' id='totalhoras" . $numaluno . "'></span>" : "";
        $volta .= "</td>";

        if (trim($row['tipo']) == "Entrada") {
            $volta .= "<td> NA </td></tr>";
        } else {
            $volta .= "<td><span class='planhor' id='horasexcedper" . $numaluno . "'></span> <span class='difhor' id='horasexced" . $numaluno . "'></span> </td></tr>";
        }
        $csv .= "," . $row['numeroaluno'] . "," . $row['tipo'] . "," . $row['dtportaria'] . "h,";
        $i++;

        $reghorarios[$numaluno][] = $row['dataportaria'];
        echo $volta;
        unset($volta);

        if (count($reghorarios[$numaluno]) > 1) {
            $reghorarios1 = $reghorarios[$numaluno][1];
            $reghorarios0 = $reghorarios[$numaluno][0];
          // periodo utilizado
            $datetime1 = strtotime("$reghorarios1");
            $datetime2 = strtotime("$reghorarios0");
            $interval = $datetime2 - $datetime1;
            $difintervalos = gmdate("H:i", ($interval));

            sscanf($horasplano, "%d:%d:%d", $hours, $minutes, $seconds);
            $datetimeplano = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
            $intervalutil = $interval - $datetimeplano;
            $diffutil = gmdate("H:i", ($intervalutil));
          // por periodo
            $vaux = explode(' ', $reghorarios[$numaluno][0]);
            $vauxdiff = $vaux[0] . ' ' . $horassaida;

            $datetimes1 = $datetime2;
            $datetimes2 = strtotime("$vauxdiff");
            $intervals = $datetimes1 - $datetimes2;
            $difintervalosper = gmdate("H:i", ($intervals));
            ?>
        <script>
          // console.log(<?=$numaluno?>+'-'+<?=$interval?>);
          // console.log(<?=$numaluno?>+'-'+"<?=$horasplano?>");
          // console.log(<?=$numaluno?>+'-'+<?=$datetimeplano?>);
          $("#totalhoras<?=$numaluno?>").html("Total Horas: <?=$difintervalos?>");
          // if(<?=$interval?> > <?=$intervalutil?>) {
          if(<?=$interval?> > <?=$datetimeplano?>) {
            $("#horasexced<?=$numaluno?>").html("Horas: <?=$diffutil?>");
          }
          if(<?=$datetimes1?> > <?=$datetimes2?>) {
            $("#horasexcedper<?=$numaluno?>").html("Período: <?=$difintervalosper?><br>");
          }
        </script>
            <?php
        } else {
            $difintervalos = '';
            $interval = 0;
            $intervalutil = 0;
            $datetimes1 = 0;
            $datetimes2 = 0;
            $diffutil = '';
            $difintervalosper = '';
        }
        $csv .= $difintervalosper . "," . $diffutil . "\r\n";
    }

    $novoNome = "../clientes/" . $cliente . "/portaria" . date("Ymd") . ".csv";
    $file_handle = fopen($novoNome, "w");
    fwrite($file_handle, $csv);
    fclose($file_handle);
    ?>
  <tr id="rodape-table-portaria-1" class="noPrint"><td colspan='8'>
    <button type='button' class="btn primary-color" onClick='window.open("downloadcsv.php?cliente=<?=$cliente?>")'>
      <i class="fa fa-download"></i> Download CSV
    </button>
    </td>
  </tr>
  <tr id="rodape-table-portaria-2" class="noPrint"><td colspan='8'><span style="font-size:12px; color:#66A";>* importar usando UTF-8 e separação de dados por vírgulas</span></td></tr>
  <tr id="rodape-table-portaria-3" class="noPrint">
    <td colspan='8' style="text-align:right;padding-right:10px;">
      Exibir: <input type="checkbox" name="formaexibicaohor" id="formaexibicaohor" value="1" style="display:inline;" checked> Horas &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="checkbox" name="formaexibicaotur" id="formaexibicaotur" value="1" style="display:inline;" checked> Período
    </td>
  </tr>

<script>
  $("#formaexibicaohor").click(function () {
    if($("#formaexibicaohor").is(':checked')) {
        $('.difhor').show();
    } else {
        $('.difhor').hide();
    }
  });
  $("#formaexibicaotur").click(function () {
    if($("#formaexibicaotur").is(':checked')) {
      $('.planhor').show();
    } else {
      $('.planhor').hide();
    }
  });
  $("#product-table tbody").find("tr:even").css("background-color", "#aaa");
  $("#product-table tbody").find("tr:odd").css("background-color", "#eee");
  $('#rodape-table-portaria-1, #rodape-table-portaria-2, #rodape-table-portaria-3').css("background-color", "#fff");
</script>
    <?php
} elseif ($action == "apaga") {
    $query  = "DELETE FROM portaria WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "Registro da Portaria removido.";
        $msg = "Registro da Portaria removido.";
        $status = 0;
    } else {
        echo "Erro ao remover Registro da Portaria do aluno.";
        $msg = "Erro ao remover Registro da Portaria do aluno.";
        $status = 1;
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
?>
