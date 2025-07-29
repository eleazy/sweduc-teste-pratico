<?php
include ('headers.php');
include ('conectar.php');
include_once ('function/ultilidades.func.php');
$debug=0;

$agora = date("Y-m-d H:i");
$agora1 = date("d/m/Y H:i");

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=".$idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario=$row1['id'];

$header_arq=$blocos[0];
$header_lote=$blocos[1];
$trailer_arq=$blocos[$cnt-1];
$numerobanco = substr($header_arq,0,3);
if ($numerobanco!="033") { ?>
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div id="page-heading">
            <h3>O ARQUIVO RETORNO NÃO É DO BANCO SANTANDER (033)</h3>
        </div>
    </div>
    <?php
} else {
    $numLotes=substr($trailer_arq,17,6);
    $razaosocial = substr($header_arq,72,30);
    $cnpj = substr($header_arq,18,15);
    $agencia = substr($header_arq,32,4);//."-".substr($header_arq,36,1);
    $contacorrente =substr($header_arq,37,10)+0;//."-".substr($header_arq,46,1);
    $cnpj=format_string("##.###.###/####-##", $cnpj);
    ?>

    <br />
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div>
            <br clear="all" />
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
                    $query2="INSERT INTO financeiro_retornos (agencia, conta, banco, razaosocial, cnpj, nomearquivo, dataupload, arquivoupload, bancocod) VALUES ('$agencia', '$contacorrente', 'ITAU', '$razaosocial', '$cnpj', '$arquivo', '$agora', '" . $arquivoUpload . "', 'santanderSD-242')";
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

                    for ($i=2; $i<$numLotes+2; $i++) {

                        $segmentoT=$blocos[$i];$i++;
                        $segmentoU=$blocos[$i];

                        $nossonumero = substr($segmentoT,40,12)+0;
                        //$seunumero = substr($segmentoT,54,15);
                        $vencimento = substr($segmentoT,69,2).'/'.substr($segmentoT,71,2).'/'.substr($segmentoT,73,4);
                        $datavencimento =  substr($segmentoT,73,4).'-'.substr($segmentoT,71,2).'-'.substr($segmentoT,69,2);
                        $valortitulo = (substr($segmentoT,77,13)+0).'.'.(substr($segmentoT,90,2));
                        $valor = (substr($segmentoT,77,13)+0).'.'.(substr($segmentoT,90,2));

                        $tarifas = (substr($segmentoT,193,13)+0).','.(substr($segmentoT,206,2));
                        $tarifavalor =str_replace(",",".",$tarifas);

                        $valorpago = (substr($segmentoU,77,13)+0).'.'.(substr($segmentoU,90,2));

                        $dtpagamento = substr($segmentoU,137,2).'/'.substr($segmentoU,139,2).'/'.substr($segmentoU,141,4);
                        $datapagamento =  substr($segmentoU,141,4).'-'.substr($segmentoU,139,2).'-'.substr($segmentoU,137,2);

                        $juross = (substr($segmentoU,17,13)+0).','.(substr($segmentoU,30,2));
                        $juros = (substr($segmentoU,17,13)+0).'.'.(substr($segmentoU,30,2));

                        $descontos = (substr($segmentoU,32,13)+0).'.'.(substr($segmentoU,45,2));

                        $abatimentos = 0; //((substr($detalhe_arq,227,11)+0).'.'.(substr($detalhe_arq,238,2)));

                        $descontosTotal= $descontos+$abatimentos+0;

                        $status = (substr($segmentoT,15,2));

                        ?>
                        <tr>
                            <?php
                            /*
                                                $query3a="SELECT tarifaboleto FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa AND empresas.cnpj='".$cnpj."' AND contasbanco.agencia='$agencia' AND contasbanco.conta='$contacorrente'";
                                                $result3a = mysql_query($query3a);
                                                $row3a = mysql_fetch_array($result3a, MYSQL_ASSOC);
                                                $tarifaboleto=$row3a['tarifaboleto'];
                                                  $tarifaboletovalor=str_replace(",",".",$tarifaboleto);

                                                $valortitulo=$valor-$tarifavalor+$tarifaboletovalor;


                                                $valorpago=$valorpago-$tarifavalor+$tarifaboletovalor;

                            */
                            $valortituloHTML=money_format('%.2n', $valortitulo);
                            $valorpagoHTML=money_format('%.2n', $valorpago);

                            $query3="SELECT nome, alunos.id as aid FROM pessoas, alunos, alunos_fichafinanceira WHERE alunos.idpessoa=pessoas.id AND alunos.id=alunos_fichafinanceira.idaluno AND alunos_fichafinanceira.titulo='$nossonumero'";
                            $result3 = mysql_query($query3);
                            $row3 = mysql_fetch_array($result3, MYSQL_ASSOC);
                            $idaluno=$row3['aid'];
                            ?>
                            <td><?=$cnt_rows?><?php $cnt_rows++;?></td>
                            <td><?=$row3['nome']?><br /><?=$nossonumero?></td>
                            <td><?=$valortituloHTML?></td>
                            <td><?=$vencimento?></td>
                            <td><?=$dtpagamento?></td>
                            <td>R$ <?=str_replace(".",",",$descontosTotal)?></td>
                            <td><?=$tarifas?></td>
                            <td>R$ <?=$juross?></td>
                            <td><?=$valorpagoHTML?></td>

                            <td>
                                <?php
                                //$query4  = "UPDATE alunos_fichafinanceira SET situacao=6, datarecebimento='$datapagamento', desconto='$descontos', juros='$juros' WHERE situacao=0 AND titulo='$nossonumero' AND valor='$valor' AND datavencimento='$datavencimento' AND idcontasbanco IN ( SELECT contasbanco.id FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa AND empresas.cnpj='".$cnpj."' AND contasbanco.agencia='$agencia' AND contasbanco.conta='$contacorrente' )";

                                /*
                                                    $queryTMP="SELECT contasbanco.id as cbid FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa AND empresas.cnpj='".$cnpj."' AND contasbanco.agencia='$agencia' AND contasbanco.conta='$contacorrente'";
                                                    $resultTMP = mysql_query($queryTMP);
                                                    $idcontasbanco="";
                                                    while ($rowTMP = mysql_fetch_array($resultTMP, MYSQL_ASSOC))
                                                      $idcontasbanco.=$rowTMP['cbid'].",";
                                                    $idcontasbanco=substr_replace($idcontasbanco, ' ', -1);

                                                    $queryTMP="SELECT alunos_fichafinanceira.id as idffin FROM alunos_fichafinanceira WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ($idcontasbanco)";
                                */

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

                                $queryTMP="SELECT alunos_fichafinanceira.id as idffin FROM alunos_fichafinanceira WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ( SELECT contasbanco.id as cbid FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa AND empresas.cnpj='".$cnpj."' AND contasbanco.agencia='$agencia' AND contasbanco.conta='$contacorrente' )";
                                $resultTMP = mysql_query($queryTMP);
                                $rowTMP = mysql_fetch_array($resultTMP, MYSQL_ASSOC);
                                $idfichafinanceira=$rowTMP['idffin'];
                                $idcontasbancotitulo=$rowTMP['idcbanco'];

                                // santander 240
                                if ($status == 6 || $status == 7 || $status == 8 || $status == 17) {
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
                                                if ($row6['cnt']==0) { echo "<br /><font color='red'><b>Título inexistente</b></font>";
                                                } else if ( ($row6['situacao']==6)||($row6['situacao']==1) ) { echo "<br /><font color='red'><b>Título já recebido.</b></font>";
                                                } else echo "<br /><font color='red'><b>Dados incorretos.</b></font>";
                                                //if ($tarifas!=$tarifaboleto) echo "<br /><font color='red'><b>Tarifa cobrada pelo banco diferente da tarifa cobrada pela escola.</b></font>";
                                            }

                                        }else {
                                            echo "<br /><font color='red'><b>Valores incorretos.</b></font>";
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
