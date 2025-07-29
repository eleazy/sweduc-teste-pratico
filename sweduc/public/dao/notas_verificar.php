<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');
include('../headers.php');
include('conectar.php');
include_once('logs.php');
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}
include('../permissoes.php');
include('helper_notas.php');

$agora = date("Y-m-d H:i:s");
$debug = 0;

$query1  = "SELECT id, professor FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];
$professor = $row1['professor'];
//echo $query1." ]][[ ".$idfuncionario." ]] [[ ".$professor."<br /><br />";



$query = "SELECT iddisciplina,idanoletivo FROM grade WHERE id=" . $idgrade;
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$iddisciplina = $row['iddisciplina'];
$aletivo = $row['idanoletivo'];

$query = "SELECT disciplina FROM disciplinas WHERE id=" . $row['iddisciplina'];
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$disciplina = $row['disciplina'];

$query = "SELECT periodo FROM periodos WHERE id=" . $idperiodo;
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$periodo = $row['periodo'];

$alunosnotas = bancoNotas($aletivo, $idgrade);

if ($action == "salvanota") {
// ***** CONTROLE DE NOTAS ************ //
    $i = 0;
    $salva = 0;
    $atualiza = 0;
    $mantida = 0;
    $salvaF = 0;
    $atualizaF = 0;
    $mantidaF = 0;
    $salvamsg = "";
    $atualizamsg = "";
    $msg1 = "";

    foreach ($idaluno as $ida) {
        $query = "SELECT nome FROM pessoas, alunos WHERE alunos.idpessoa=pessoas.id AND alunos.id=" . $ida;
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $nomealuno = $row['nome'];

        $coluna = 0;
        $notaavalia = $_POST['nota'];
        $notaava = str_replace(',', '.', $notaavalia[$ida]);

        foreach ($idavaliacao[$ida] as $idav) {
            $query  = "SELECT an.id, nota, medias.id as mid FROM " . $alunosnotas . " an,medias WHERE medias.id=an.idmedia AND medias.idgrade=" . $idgrade . " AND an.idavaliacao=$idav AND medias.idperiodo=$idperiodo AND an.idaluno=$ida";
            $result = mysql_query($query);
            if ($debug) {
                echo $query . "<br />";
            }
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            $idnota = $row['id'];
            $nota = $row['nota'];
            $idmedia = $row['mid'];

            $query = "SELECT avaliacao FROM avaliacoes WHERE id=" . $idav;
            $result = mysql_query($query);
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            $avaliacao = $row['avaliacao'];

            if ($idnota == "") {
                if ($notaava[$coluna] != "") {
                    $queryMedia  = "SELECT id FROM medias WHERE idgrade=" . $idgrade . " AND idperiodo=" . $idperiodo;
                    $resultMedia = mysql_query($queryMedia);
                    if ($debug) {
                        echo $queryMedia . "<br />";
                    }
                    $rowMedia = mysql_fetch_array($resultMedia, MYSQL_ASSOC);
                    $idmedia = $rowMedia['id'];

                    $salva++;
                    $query1 = "INSERT INTO " . $alunosnotas . "(idmedia, idavaliacao, idaluno, datahora, nota, faltas) VALUES ($idmedia, $idav, $ida, '$agora', '$notaava[$coluna]',0);";
                    $result1 = mysql_query($query1);

                    $salvamsg .= "Aluno:" . $nomealuno . " - Período: " . $periodo . " - Disciplina: " . $disciplina . " - Avaliação: " . $avaliacao . " - Nota: " . $notaava[$coluna] . "<br />";

                    if ($debug) {
                        echo $query1 . "<br />";
                    }
                }
            } else {
                if ($nota != $notaava[$coluna]) {
                    if ($notaava[$coluna] == "") {
                        $query1 = "DELETE FROM " . $alunosnotas . " WHERE id=" . $idnota;
                    } else {
                        $query1 = "UPDATE " . $alunosnotas . " SET nota='$notaava[$coluna]', datahora='$agora' WHERE id=" . $idnota;
                    }
                    $result1 = mysql_query($query1);
                    if ($debug) {
                        echo $query1 . "<br />";
                    }
                    $atualiza++;

                    $atualizamsg .= "Aluno:" . $nomealuno . " - Período: " . $periodo . " - Disciplina: " . $disciplina . " - Avaliação: " . $avaliacao . " - Nota anterior: " . $nota . " - Nota atualizada: " . $notaava[$coluna] . "<br />";
                } else {
                    $mantida++;
                }
            }
            $coluna++;
        }
        //$i++;
      // ****** CONTROLE DE FALTAS POR DISCIPLINA *****************************


        $faltasAluno = $faltas[$i];
        $query  = "SELECT an.id, faltas, medias.id as mid FROM " . $alunosnotas . " an, medias WHERE medias.id=an.idmedia AND medias.idgrade=" . $idgrade . " AND medias.idperiodo=" . $idperiodo . " AND an.idavaliacao=0 AND idaluno=" . $ida;
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $idmedia = $row['mid'];

        if ($row['id'] == "") {
            $queryMedia  = "SELECT id FROM medias WHERE idgrade=" . $idgrade . " AND idperiodo=" . $idperiodo;
            $resultMedia = mysql_query($queryMedia);
            if ($debug) {
                echo $queryMedia . "<br />";
            }
            $rowMedia = mysql_fetch_array($resultMedia, MYSQL_ASSOC);
            $idmedia = $rowMedia['id'];

            if ($faltasAluno != "") {
                $query1  = "INSERT INTO " . $alunosnotas . "(idmedia, idavaliacao, idaluno, datahora, nota, faltas) VALUES ($idmedia, 0, $ida, '$agora', 0,'$faltasAluno');";
                if ($result1 = mysql_query($query1)) {
                    $salvaF++;
                }
            }
        } else {
            if ($faltasAluno != $row['faltas']) {
                $query1  = "UPDATE " . $alunosnotas . " SET faltas='$faltasAluno', datahora='$agora' WHERE idmedia=$idmedia AND idavaliacao=0 AND idaluno=$ida";
                if ($result1 = mysql_query($query1)) {
                    $atualizaF++;
                }
            } else {
                $mantidaF++;
            }
        }
        $i++;
    }
      $msg1 = "";
    if (($salvaF > 0) || ($atualizaF > 0) || ($mantidaF > 0)) {
        $msg1 = $salvaF . " faltas salvas.<br />" . $atualizaF . " faltas atualizadas." . $mantidaF . " faltas inalteradas.";
        $status = 0;
    } else {
        $msg1 = "";
        $status = 1;
    }



      $msg = $salva . " notas salvas<br /><br />" . $salvamsg . "<hr />" . $atualiza . " notas atualizadas.<br /><br />" . $atualizamsg . "<hr />" . $mantida . " notas inalteradas." . $msg1;
      $parametroscsv = '';
      salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

      echo "blue|$salva notas salvas. $atualiza notas atualizadas. $mantida notas inalteradas." . $msg1;
} elseif ($action == "buscaTurmaSerie") {
    $query1  = "SELECT *, turmas.id as tid FROM turmas, series, cursos WHERE series.idcurso=cursos.id AND cursos.idunidade=$idunidade AND turmas.idserie=series.id ORDER BY curso,serie,turma ASC";
    $result1 = mysql_query($query1);
    echo '<option value="-1"> - </option>';
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo '<option value="' . $row1['tid'] . '">' . $row1['curso'] . " :: " . $row1['serie'] . " :: " . $row1['turma'] . '</option>';
    }
} elseif ($action == "buscaTurmaDisciplinas") {
    $sql = "";
    if ($perfil != "ADMIN") {
        if ($professor == 1) {
            $sqlfrom = " , grade_funcionario ";
            $sql = " AND grade_funcionario.idgrade=grade.id AND grade_funcionario.idfuncionario=" . $idfuncionario;
        }
    }

    $query1  = "SELECT *, grade.id as did FROM grade, disciplinas, turmas, series, cursos $sqlfrom WHERE grade.idserie=series.id AND series.idcurso=cursos.id AND cursos.idunidade=$idunidade AND grade.idturma=turmas.id " . $sql . " AND grade.iddisciplina=disciplinas.id AND grade.idanoletivo=" . $idanoletivo . " ORDER BY curso ASC, serie ASC, turma ASC, disciplina ASC";
    $result1 = mysql_query($query1);
    echo "<option value='-1'> - </option>";
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo '<option value="' . $row1['did'] . '">' . $row1['curso'] . " :: " . $row1['serie'] . " :: " . $row1['turma'] . " :: " . $row1['disciplina'] . '</option>';
    }
//    echo "@".$query1;
//echo "<br>".$query1."<br>";




/*
} else if ($action=="buscaTurmas") {
    $query1  = "SELECT *, turmas.id as tid FROM turmas, disciplina_turmas WHERE disciplina_turmas.iddisciplina=".$id." AND disciplina_turmas.idturma=turmas.id ORDER BY turma ASC";
    $result1 = mysql_query($query1);
    echo '<option value="-1"> - </option>';
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) echo '<option value="'.$row1['tid'].'">'.$row1['turma'].'</option>';
*/
} elseif ($action == "buscaAvaliacoes") {
    $query1 = "SELECT formula FROM medias WHERE idgrade=$idgrade AND idperiodo=" . $idperiodo;
    $result1 = mysql_query($query1);
    $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
    $formula = $row1['formula'];

    $pattern = "/#M([0-9]*)@/";
    $matches[0] = " ";
    while ($matches[0] != null) {
        preg_match($pattern, $formula, $matches);
        $queryIN = "SELECT formula FROM medias WHERE id=" . $matches[1];
        $resultIN = mysql_query($queryIN);
        $rowIN = mysql_fetch_array($resultIN, MYSQL_ASSOC);
        if ($debug) {
            echo $queryIN . " ][ " . $resultIN . "]<br />";
        }
        $formulaIN = $rowIN['formula']; // FORMULA DA MÉDIA INTERNA

        $patternAVA = "/#A([0-9]*)@/";
        $matchesAVA[0] = " ";
        while ($matchesAVA[0] != null) {
            preg_match($patternAVA, $formulaIN, $matchesAVA);
            $queryAVA = "SELECT nota FROM " . $alunosnotas . " WHERE idavaliacao=" . $matchesAVA[1] . " AND idmedia=" . $matches[1];
            $resultAVA = mysql_query($queryAVA);
            $rowAVA = mysql_fetch_array($resultAVA, MYSQL_ASSOC);
            if ($debug) {
                echo $queryAVA . " ][ " . $rowAVA['nota'] . "]<br />";
            }
            $formulaIN = str_replace($matchesAVA[0], $rowAVA['nota'], $formulaIN);
        }

        $formula = str_replace($matches[0], $formulaIN, $formula);
    }

    $patternAVA = "/#A([0-9]*)@/";
    $matchesAVA[0] = " ";
    $opts[] = "";
    while ($matchesAVA[0] != null) {
        preg_match($patternAVA, $formula, $matchesAVA);
        $queryAVA = "SELECT avaliacao FROM avaliacoes WHERE id=" . $matchesAVA[1] ;
        $resultAVA = mysql_query($queryAVA);
        $rowAVA = mysql_fetch_array($resultAVA, MYSQL_ASSOC);
        $formula = str_replace($matchesAVA[0], $rowAVA['avaliacao'], $formula);
        if (trim($rowAVA['avaliacao'])) {
            $opts[$matchesAVA[1]] = '<option value="' . $matchesAVA[1] . '">' . $rowAVA['avaliacao'] . '</option>';
            $ids[] = $matchesAVA[1];
        }
    }
    sort($ids);
    ksort($opts);
    echo '<option value="' . implode(',', $ids) . '">TODAS</option>' . implode(',', $opts);
           //<option value="0">-- Avaliações --</option>
} elseif ($action == "buscaProf") {
    $query1  = "SELECT nome FROM pessoas, funcionarios, grade_funcionario WHERE idgrade=" . $idgrade . " AND grade_funcionario.idfuncionario=funcionarios.id AND funcionarios.idpessoa=pessoas.id";
    $result1 = mysql_query($query1);
    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
        echo $row1['nome'] . "<br />";
    }
} elseif ($action == "buscaNotas") {
    //ticket com as notas para tirar as medias

    echo '<tr><th class="table-header-repeat line-left minwidth-1" colspan="2">Aluno</th>';
    echo '<th class="table-header-repeat line-left noPrint"></th>';
    if ($tipodefaltas == '1') {
        echo '<th class="table-header-repeat line-left" width="20px">Faltas</th>';
    }

    $query  = "SELECT * FROM avaliacoes WHERE avaliacoes.id IN ($idavaliacao) ORDER BY id ASC";
    $result = mysql_query($query);

//echo '<th><td colspan=4>'.$query.' ['.$idavaliacao.']</td></th>';


    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<th class="table-header-repeat line-left minwidth-1">' . $row['avaliacao'] . '</th>';
        $idaval[] = $row['id'];
    }
    //$idavaliacao = implode(",",$idava);
    echo '</tr>';

    echo "|";

    $finalquery = "";
    if ($idturma != "todos") {
        $finalquery = "AND alunos_matriculas.turmamatricula=(SELECT idturma FROM grade WHERE id=$idgrade)";
    }
    $query  = "SELECT *, alunos.id as aid FROM alunos, alunos_matriculas, pessoas, turmas WHERE alunos_matriculas.status<2 AND alunos_matriculas.turmamatricula=turmas.id AND alunos.idpessoa=pessoas.id AND alunos.id=alunos_matriculas.idaluno " . $finalquery . " AND alunos_matriculas.anoletivomatricula=" . $idanoletivo . " ORDER BY nome ASC";
    $result = mysql_query($query);
    $cnt = 0;
  //echo "<tr><td colspan='10'>".$query."</td></tr>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $idturma = $row['turmamatricula'];?>
        <tr><td width="10px" style="padding:5px;"><?=($cnt + 1)?></td><td width="400px"><input type="hidden" name="idaluno[]" value="<?=$row['aid']?>" /><?=$row['nome']?></td>
    <td class="noPrint"><input type="button" class="button bgdarkgreen" id="medias<?=$row['aid']?>" onclick="medias('<?=$idturma?>','<?=$row['anoletivomatricula']?>','<?=$row['nome']?>',<?=$row['aid']?>,<?=$row['nummatricula']?>,<?=$idgrade?>);" value="  MÉDIAS  " /></td>
        <?php
        if ($tipodefaltas == '1') {
            $queryA = "SELECT faltas FROM " . $alunosnotas . " an, medias WHERE idaluno=" . $row['aid'] . " AND an.idmedia=medias.id AND medias.idgrade=" . $idgrade . " AND medias.idperiodo=" . $idperiodo . " AND an.idavaliacao=0";
            $resultA = mysql_query($queryA);
            $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
            echo '<td width="20px"><input type="text" name="faltas[]" id="faltas' . $cnt . '" class="inp-form peq" value="' . $rowA['faltas'] . '"  /></td>';
        }

        $id_grade = 0;
        $id_periodo = 0;

        foreach ($idaval as $idava) {
            $queryA = "SELECT nota FROM " . $alunosnotas . ", medias WHERE idaluno=" . $row['aid'] . " AND idmedia=medias.id AND medias.idgrade=" . $idgrade . " AND medias.idperiodo=" . $idperiodo . " AND idavaliacao =" . $idava;
            $resultA = mysql_query($queryA);
            $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);

            $id_grade = $idgrade;
            $id_periodo = $idperiodo;

            echo '<td>' . "" . '<input type="text" name="nota[' . $row['aid'] . '][]" id="nota' . $cnt . '" class="inp-form peq noPrint soma' . $idava . '" value="' . $rowA['nota'] . '" onblur="if($.trim(this.value)<>\'\') if (!(isNaN(this.value))) this.value=parseFloat(this.value).toFixed(' . $notasdecimais . ')" />';

            $mediadaturma[$idava] = $mediadaturma[$idava] + $rowA['nota'];

            echo '<span class="impressao">' . $rowA['nota'] . '</span>';
            echo '<input type="hidden" name="idavaliacao[' . $row['aid'] . '][]" id="idavaliacao' . $cnt . '" class="inp-form peq" value="' . $idava . '" /></td>';
            ?>
<script>
$(".soma<?=$idava?>").blur(function(){
  valor=0;
  $(".soma<?=$idava?>").each(function(){
    valor = parseFloat($(this).val()) + valor ;
  });

  valor=valor/$("#totalalunos").val();
  $('#media<?=$idava?>').html(valor.toFixed(<?=$notasdecimais?>)); //( ((valor)/($("#cnt").val())) );
});
</script>
            <?php
        }

        echo '</tr>';
        $cnt++;
    }
    $colsp = 3;
    if ($tipodefaltas == '1') {
        $colsp = 4;
    }

    /**
    * Inicio da rotina para calcular as medias
    * @author Fabio Souza
    */

    //echo "<script>alert('$action');</script>";

    echo "<tr>";

    echo "<td></td><td></td><td><strong>MÉDIA:</strong> </td>";

    foreach ($idaval as $idava) {
        $query  = "SELECT * FROM avaliacoes WHERE avaliacoes.id IN ($idava) ORDER BY id ASC";
        $result = mysql_query($query);

        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $sql = "SELECT nota
			FROM " . $alunosnotas . ", medias
			WHERE idmedia = medias.id
			AND medias.idgrade = $idgrade
			AND medias.idperiodo = $idperiodo
			AND idavaliacao = $idava ";

            $result2 = mysql_query($sql);

            $total_notas = 0;
            $media = 0;

            while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
                $total_notas = $total_notas + $row2['nota'];

                if ($row2['nota'] != "") {
                    $media++;
                }
            }

            echo '<td>' . "" . round($total_notas / $media, 2) . '</td>';
        }
    }

    echo "</tr>";

    //fim das medias

    //   echo '<tr><td colspan="'.$colsp.'" style="text-align:center;">MÉDIA DA TURMA</td><td class="noPrint"><input type="hidden" id="idturma" value="'.$idturma.'" /><input type="hidden" id="totalalunos" value="'.$cnt.'"></td>';
    //
    //   foreach ( $idaval as $idava ) {
    //  echo '<td  style="text-align:center;"><span class="impressao">';
    //  if (isNaN($mediadaturma[$idava])) echo " NA ";
    //  else echo number_format(($mediadaturma[$idava]/$cnt), $notasdecimais, ".", "");
    //  echo '</span><span id="media'.$idava.'" class="noPrint">';
    //  if (isNaN($mediadaturma[$idava])) echo " NA ";
    //  else echo number_format(($mediadaturma[$idava]/$cnt), $notasdecimais, ".", "");
    //  echo '</span></td>';
    //   }
    // echo '</tr>';
}
?>
