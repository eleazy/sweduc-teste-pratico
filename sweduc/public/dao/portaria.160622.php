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
    if ($idturma > 0) {
        $sql .= " AND alunos_matriculas.idaluno=portaria.idaluno AND alunos_matriculas.turmamatricula=$idturma ";
        $sqlfrom .= ", alunos_matriculas";
    }
    if (trim($idtipo) != "") {
        $sql .= " AND portaria.tipo='$idtipo' ";
    }

    $query  = "SELECT alunos.id as aid, portaria.id as portid, pessoas.nome, alunos.numeroaluno, portaria.tipo,dataportaria, DATE_FORMAT(dataportaria,'%d/%m/%Y %H:%i:%s') as dtportaria, DATE_FORMAT(dataportaria,'%H:%i:%s') as horaportaria FROM alunos, pessoas, portaria" . $sqlfrom . " WHERE alunos.idpessoa=pessoas.id AND portaria.idaluno=alunos.id AND DATE_FORMAT(dataportaria,'%d/%m/%Y')='" . $databusca . "' AND portaria.idunidade=$idunidade " . $sql . " ORDER BY dataportaria DESC";
    $result = mysql_query($query);
    $volta = "";
    $csv = "";
    $i = 1;
    if ($debug == 1) {
        echo $query . " " . $result . "<br />";
    }

    $expldata = explode('/', $databusca);

    $reghorarios = [];
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $volta .= "<tr id='row" . $row['portid'] . "'><td>" . $i . "</td><td>" . $unidade . "</td><td>" . $row['nome'] . "<br />" . $row['numeroaluno'] . "</td><td>";
        $csv .= $i . "," . $unidade . "," . $row['nome'] . ",";

        $saida = 0;
        $plano = "";

        $query1  = "SELECT turma, saida, TIMEDIFF('" . $row['horaportaria'] . "',saida) as excedente, codigo, DATE_FORMAT(entrada,'%H:%i') as ent, DATE_FORMAT(saida,'%H:%i') as sai, TIMEDIFF(saida,entrada) AS difhoras, anoletivo.id as idanoletivo FROM alunos_matriculas, turmas, planohorarios, anoletivo WHERE alunos_matriculas.status=1 AND alunos_matriculas.anoletivomatricula=anoletivo.id AND alunos_matriculas.idplanohorario=planohorarios.id AND alunos_matriculas.turmamatricula=turmas.id AND alunos_matriculas.idaluno=" . $row['aid'] . " AND anoletivo=" . $expldata[2];
        $result1 = mysql_query($query1);
        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
            if ($saida < $row1['excedente']) {
                $saida = $row1['excedente'];
            }
            $volta .= $row1['turma'] . "<br />";
            $plano .= $row1['codigo'] . "<br /><span class='planhor'>Entrada:" . $row1['ent'] . "h<br />Saída:" . $row1['sai'] . "h<br /></span><span class='difhor'>Horas: " . substr($row1['difhoras'], 0, 5) . "h</span>";
            $csv .= $row1['turma'] . " ";

            $horasplano = $row1['difhoras'];
            $horassaida = $row1['saida'];
        }
        $volta .= "</td><td>" . $plano . "</td><td>" . $row['tipo'] . "</td><td>" . $row['dtportaria'] . "h";
        $volta .= (trim($row['tipo']) != "Entrada") ? "<br><span class='difhor' id='totalhoras'></span>" : "";
        $volta .= "</td>";

        if (trim($row['tipo']) == "Entrada") {
            $volta .= "<td> NA </td></tr>";
        }
    // else if ( $saida>0 ) $volta.= "<td>".$saida."h</td></tr>";
        else {
            $volta .= "<td><span class='planhor' id='horasexcedper'></span> <span class='difhor' id='horasexced'></span> </td></tr>";
        }

        $csv .= "," . $row['numeroaluno'] . "," . $row['tipo'] . "," . $row['dtportaria'] . "h\r\n";
        $i++;
        if ($debug == 1) {
            echo $query1 . " " . $result1 . "<br />";
        }

        $reghorarios[] = $row['dataportaria'];
    }

  // $difintervalos = (count($reghorarios)>1)?date_diff($reghorarios[0],$reghorarios[1]):'';

    if (count($reghorarios) > 1) {
        $datetime1 = strtotime("$reghorarios[1]");
        $datetime2 = strtotime("$reghorarios[0]");
        $interval = $datetime2 - $datetime1;
        $difintervalos = gmdate("H:i", ($interval));

        $datetimeplano = strtotime("$horasplano");
        $intervalutil = $interval - $datetimeplano;
        $diffutil = gmdate("H:i", ($intervalutil));

      // por periodo
        $vaux = explode(' ', $reghorarios[0]);
        $vauxdiff = $vaux[0] . ' ' . $horassaida;
        $datetimes1 = strtotime("$reghorarios[0]");
        $datetimes2 = strtotime("$vauxdiff");
        $intervals = $datetimes1 - $datetimes2;
        $difintervalosper = gmdate("H:i", ($intervals));
    } else {
        $difintervalos = '';
        $interval = 0;
        $intervalutil = 0;
        $datetimes1 = 0;
        $datetimes2 = 0;
    }


    echo $volta;
    $novoNome = "../clientes/" . $cliente . "/portaria" . date("Ymd") . ".csv";
    $file_handle = fopen($novoNome, "w");
    fwrite($file_handle, $csv);
    fclose($file_handle);
    ?>
  <tr><td colspan='8'><input type='button' class="button" value='Download CSV' onClick='window.open("downloadcsv.php?cliente=<?=$cliente?>")' ></td></tr>
  <tr><td colspan='8'><span style="font-size:12px; color:#66A";>* importar usando UTF-8 e separação de dados por vírgulas</span></td></tr>
  <tr><td colspan='8' style="text-align:right;padding-right:10px;">
    Exibir: <input type="checkbox" name="formaexibicaohor" id="formaexibicaohor" value="1" style="display:inline;" checked> Horas &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="checkbox" name="formaexibicaotur" id="formaexibicaotur" value="1" style="display:inline;" checked> Período
  </td></tr>

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

  $("#totalhoras").html("Total Horas: <?=$difintervalos?>");

  if(<?=$interval?> > <?=$intervalutil?>) {
    $("#horasexced").html("Horas: <?=$diffutil?>");
  }

  if(<?=$datetimes1?> > <?=$datetimes2?>) {
    $("#horasexcedper").html("Período: <?=$difintervalosper?><br>");
  }

  console.log("hor: ","<?=$reghorarios[0]?>");
  console.log("saida", "<?=$vauxdiff?>");
  console.log("saida", "<?=$datetimes1?>");
  console.log("saida", "<?=$datetimes2?>");
  console.log("$query1", "<?=$query1?>");
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
