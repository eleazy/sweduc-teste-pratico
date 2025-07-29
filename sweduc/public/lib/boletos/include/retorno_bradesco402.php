<?php
session_start();
include ('headers.php');
include ('conectar.php');

include_once ('function/ultilidades.func.php');
$debug=0;

$agora = date("Y-m-d H:i");
$agora1 = date("d/m/Y H:i");

$trailer_arq=$blocos[$cnt];
$numLotes=substr($trailer_arq,17,8)+0;

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=".$idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario=$row1['id'];

$header_arq=$blocos[0];
$numerobanco = substr($header_arq,76,3);
if ($numerobanco!="237") { ?>
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div id="page-heading">
            <input type="hidden" name="" numbanco="<?=$numerobanco?>">
            <h3>O ARQUIVO RETORNO NÃO É DO BANCO BRADESCO (237)</h3>
        </div>
    </div>
    <?php
} else {
    $razaosocial = substr($header_arq,46,30);

    $header_arq1=$blocos[1];

    $agencia = substr($header_arq1,25,4);
    $contacorrente =substr($header_arq1,30,6)."-".substr($header_arq1,36,1);

    $cnpj = substr($header_arq1,3,14);
    $cnpj=format_string("##.###.###/####-##", $cnpj);
    ?>

    <br />
    <!-- start content -->
    <div id="content" ag="<?=$agencia?>" cc="<?=$contacorrente?>" cnpj="<?=$cnpj?>">
        <!--  start page-heading -->
        <div id="page-heading">
            <h5>
                Arquivo: <?=$arquivo?><br /><br />
                Empresa: <?=$razaosocial?><br />
                CNPJ: <?=$cnpj?><br />
                Agência: <?=$agencia?><br />
                Conta Corrente: <?=$contacorrente?>
            </h5>
        </div>

        <input type="button" value="   IMPRIMIR   " class="button noprint" onClick="window.print()" />
        <!-- end page-heading -->
        <div id="content-table-inner">
            <?php
            $query1="SELECT Count(*) as cnt FROM financeiro_retornos WHERE nomearquivo='$arquivo' and agencia = " . $agencia . " and conta = " . $contacorrente;
            $result1 = mysql_query($query1);
            $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
            if ($row1['cnt']>0) echo "<h1>Esse arquivo já foi enviado</h1>";
            else {

                if (!isset($idrelaod)){
                    $query2="INSERT INTO financeiro_retornos (agencia, conta, banco, razaosocial, cnpj, nomearquivo, dataupload, arquivoupload, bancocod) VALUES ('$agencia', '$contacorrente', 'BRADESCO', '$razaosocial', '$cnpj', '$arquivo', '$agora', '" . $arquivoUpload . "', 'bradesco-402')";
                    $result2 = mysql_query($query2);
                    $idfinanceiro_retornos=mysql_insert_id();
                }

                ?>
                <script>$("#listaretornos").append("<tr id='linha<?=$idfinanceiro_retornos?>'><td><?=$arquivo?></td><td><?=$agora1?>h</td><td><input type='button' class='button bgred' onclick='exclui(<?=$idfinanceiro_retornos?>);' value=' X ' /></td></tr>");</script>


                <table border="1" width="100%" cellpadding="0" cellspacing="0"  class="table table-striped">
                    <thead>
                    <tr>
                        <th class=""></th>
                        <th class="">Aluno / Título</th>
                        <th class="">Valor Título</th>
                        <th class="">Data Vencimento</th>
                        <th class="">Data Pagamento</th>
                        <th class="">Descontos</th>
                        <th class="">Tarifa</th>
                        <th class="">Juros</th>
                        <th class="">Valor Recebido</th>
                        <th class="">Recebido ?</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php

                    $cnt_rows = 1;

                    for ($i=1; $i<$cnt; $i++) {
                        $detalhe_arq=$blocos[$i];

                        $nossonumero = substr($detalhe_arq,126,19)+0;
                        //$seunumero = substr($detalhe_arq,54,15);
                        $vencimento = substr($detalhe_arq,146,2).'/'.substr($detalhe_arq,148,2).'/20'.substr($detalhe_arq,150,2);

                        $datavencimento =  '20'.substr($detalhe_arq,150,2).'-'.substr($detalhe_arq,148,2).'-'.substr($detalhe_arq,146,2);

                        $valortitulo = (substr($detalhe_arq,152,11)+0).','.(substr($detalhe_arq,163,2));
                        $valor = (substr($detalhe_arq,152,11)+0).'.'.(substr($detalhe_arq,163,2));

                        $tarifas = (substr($detalhe_arq,175,11)+0).','.(substr($detalhe_arq,186,2));
                        $tarifavalor =str_replace(",",".",$tarifas);

                        $valorpago = intval(substr($detalhe_arq,253,13)); //.','.(substr($detalhe_arq,261,2));
                        $valorpago=$valorpago/100;

                        //$valorpago = (substr($detalhe_arq,253,11)+0).','.(substr($detalhe_arq,264,2));
                        $valorpago = (substr($detalhe_arq,253,11)+0).'.'.(substr($detalhe_arq,264,2));

                        $dtpagamento = substr($detalhe_arq,295,2).'/'.substr($detalhe_arq,297,2).'/20'.substr($detalhe_arq,299,2);
                        $datapagamento = '20'.substr($detalhe_arq,299,2).'-'.substr($detalhe_arq,297,2).'-'.substr($detalhe_arq,295,2);

                        $juross = (substr($detalhe_arq,266,11)+0).','.(substr($detalhe_arq,277,2));
                        $juros = (substr($detalhe_arq,266,11)+0).'.'.(substr($detalhe_arq,277,2));


                        $abatimentos = ((substr($detalhe_arq,227,11)+0).'.'.(substr($detalhe_arq,238,2)));
                        $descontos = ((substr($detalhe_arq,240,11)+0).'.'.(substr($detalhe_arq,251,2)));
                        $descontosTotal= $descontos+$abatimentos+0;

                        $status = (substr($detalhe_arq,108,2));

                        ?>
                        <tr>
                            <?php
                            $query3="SELECT nome, alunos.id as aid FROM pessoas, alunos, alunos_fichafinanceira WHERE alunos.idpessoa=pessoas.id AND alunos.id=alunos_fichafinanceira.idaluno AND alunos_fichafinanceira.titulo='$nossonumero'";

                            $result3 = mysql_query($query3);
                            $row3 = mysql_fetch_array($result3, MYSQL_ASSOC);
                            $idaluno=$row3['aid'];
                            ?>
                            <td><?=$cnt_rows?><?php $cnt_rows++;?></td>
                            <td><?=$row3['nome']?><br /><?=$nossonumero?></td>
                            <td>R$ <?=$valortitulo?></td>
                            <td><?=$vencimento?></td>
                            <td><?=$dtpagamento?></td>
                            <td>R$ <?=str_replace(".",",",$descontosTotal)?></td>
                            <td>R$ <?=$tarifas?></td>
                            <td>R$ <?=$juross?></td>
                            <td>R$ <?=str_replace(".",",",$valorpago)?></td>
                            <td>
                                <?php
                                //$query4  = "UPDATE alunos_fichafinanceira SET situacao=6, datarecebimento='$datapagamento', desconto='$descontos', juros='$juros' WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ( SELECT contasbanco.id FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa AND empresas.cnpj='".$cnpj."' AND contasbanco.agencia='$agencia' AND contasbanco.conta='$contacorrente' )";


                                $queryTMP1 = "SELECT
                                                                    cb.id AS cbid, cb.valor_baixa_min
                                                                FROM
                                                                    empresas e
                                                                        INNER JOIN
                                                                    contasbanco cb ON e.id = cb.idempresa
                                                                        INNER JOIN
                                                                    alunos_fichafinanceira af ON cb.id = af.idcontasbanco
                                                                WHERE
                                                                    e.cnpj = '" . $cnpj . "'
                                                                        AND cb.agencia = '$agencia'
                                                                        AND cb.conta LIKE '%$contacorrente%'
                                                                        AND cb.banconum = '$numerobanco'
                                                                        AND af.titulo = $nossonumero";
                                $resultTMP1 = mysql_query($queryTMP1);
                                $idcontasbanco="";
                                while ($rowTMP1 = mysql_fetch_array($resultTMP1, MYSQL_ASSOC)) {
                                    $idcontasbanco .= $rowTMP1['cbid'] . ",";
                                    $baixa_min = $rowTMP1['valor_baixa_min'];
                                }
                                $idcontasbanco=substr_replace($idcontasbanco, ' ', -1);

                                $queryTMP="SELECT alunos_fichafinanceira.id as idffin FROM alunos_fichafinanceira WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ($idcontasbanco)";
                                $resultTMP = mysql_query($queryTMP);
                                $rowTMP = mysql_fetch_array($resultTMP, MYSQL_ASSOC);
                                $idfichafinanceira=$rowTMP['idffin'];
                                $idcontasbancotitulo=$rowTMP['idcbanco'];



                                if($status == 06 || $status == 15 || $status == 17) {
                                    if($valor > 0) {
                                        if (($valor - ((($valor) / 100) * $baixa_min)) <= ($valorpago + $tarifavalor)) {
                                            $query4 = "UPDATE alunos_fichafinanceira SET idfuncionario=$idfuncionario, situacao=6, datarecebimento='$datapagamento', desconto='" . ($descontosTotal + $tarifavalor) . "', juros='$juros', valorrecebido='$valorpago' WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ($idcontasbanco)";
                                            $result4 = mysql_query($query4);
                                            if ($debug)
                                                echo $query4 . " " . $result4 . "<br />";

                                            if (mysql_affected_rows()>0) {
                                                $query5  = "INSERT INTO alunos_fichasrecebidas (idalunos_fichafinanceira, idfuncionario, datarecebido, datacompensado, valorrecebido, formarecebido, idcontasbanco, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma) VALUES ($idfichafinanceira, $idfuncionario, '$datapagamento', '$datapagamento', '$valorpago','4','$idcontasbancotitulo', '', '', '', '', '', '')";
                                                $result5 = mysql_query($query5);
                                                if ($debug) echo $query5." ".$result5."<br />";

                                                echo "<font color='green'><b>SIM</b></font>";
                                                $query5="INSERT INTO financeiro_retornos_titulos (idfinanceiro_retornos,idfichafinanceira) VALUES ($idfinanceiro_retornos,$idfichafinanceira)";
                                                $result5 = mysql_query($query5);

                                                $query2 = "UPDATE
                                                                          contasbanco
                                                                       SET
                                                                          saldoatual = saldoatual+" . $valorpago . "
                                                                       WHERE id = " . $idcontasbanco;
                                                mysql_query($query2);

                                            } else {
                                                $query6="SELECT COUNT(*) as cnt, situacao FROM alunos_fichafinanceira WHERE titulo='$nossonumero'";
                                                $result6 = mysql_query($query6);
                                                $row6 = mysql_fetch_array($result6, MYSQL_ASSOC);
                                                echo "<font color='red'><b>NÃO</b></font>";
                                                if ($row6['cnt']==0) echo "<br /><font color='red'><b>Título inexistente</b></font>";
                                                else if ( ($row6['situacao']==6)||($row6['situacao']==1) ) echo "<br /><font color='red'><b>Título já recebido.</b></font>";
                                                else echo "<br /><font color='red'><b>Dados incorretos.</b></font>";
                                            }
                                        }else {
                                            echo "<br /><font color='red'><b>Valor pago abaixo do esperado.</b></font>";
                                        }
                                    }else{

                                        echo "<br /><font color='red'><b>Valor recebido igual a zero.</b></font>";
                                    }

                                }else {

                                    echo "<font color='blue'><b>Remessa/Ocorrências - " . $status . "</b></font>";

                                }

                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
<?php } ?>
