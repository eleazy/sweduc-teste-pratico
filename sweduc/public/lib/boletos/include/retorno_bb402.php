<?php
session_start();
include('headers.php');
include('conectar.php');

include_once('function/ultilidades.func.php');
$debug = 0;
$agora = date("Y-m-d H:i");
$agora1 = date("d/m/Y H:i");

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$header_arq = $blocos[0];
// $header_lote=$blocos[1];
$trailer_arq = $blocos[(is_countable($blocos) ? count($blocos) : 0) - 1];
$numerobanco = substr($header_arq, 76, 3);
if ($numerobanco != "001") { ?>
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <section class="">
            <h4>O ARQUIVO RETORNO NÃO É DOBANCO DO BRASIL (001).<br />Enviado <?=$numerobanco?></h4>
        </section>
    </div>
    <?php
} else {
    $numLotes = substr($trailer_arq, 395, 6) - 1;
    $razaosocial = substr($header_arq, 46, 30);
    // $cnpj = substr($header_lote_aux,3,15);
    $cnpj = '';
    $agencia = substr($header_arq, 26, 4);//."-".substr($header_arq,36,1);
    $contacorrente = ltrim(substr($header_arq, 31, 8), '0');//."-".substr($header_arq,36,1);
    $convenio = substr($header_arq, 149, 7) + 0;//."-".substr($header_arq,46,1);
    // $cnpj=format_string("##.###.###/####-##", $cnpj);


    ?>

    <br />
    <!-- start content -->
    <div id="content">

        <input type="button" class="button" name="imprimir" onClick="window.print()" value = "  IMPRIMIR  " style="float:right; margin-right:30px;" />
        <!--  start page-heading -->

        <div id="page-heading">
            <h5>
                Arquivo: <?=$arquivo?><br /><br />
                Empresa: <?=$razaosocial?><br />
                CNPJ: <?=$cnpj?><br />
                Agência: <?=$agencia?><br />
                Convênio: <?=$convenio?>
            </h5>
        </div>

        <input type="button" value="   IMPRIMIR   " class="button noprint" onClick="window.print()" />
        <!-- end page-heading -->
        <div id="content-table-inner">
            <?php

            $query1 = "SELECT Count(*) as cnt FROM financeiro_retornos WHERE nomearquivo='$arquivo' and agencia = " . $agencia . " and conta = " . $convenio;

            $result1 = mysql_query($query1);
            $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
            if ($row1['cnt'] > 0) {
                echo "<h4>Esse arquivo já foi enviado</h4>";
            } else {
                if (!isset($idrelaod)) {
                    $query2 = "INSERT INTO financeiro_retornos (agencia, conta, banco, razaosocial, cnpj, nomearquivo, dataupload, arquivoupload, bancocod) VALUES ('$agencia', '$convenio', 'BB', '$razaosocial', '$cnpj', '$arquivo', '$agora', '" . $arquivoUpload . "', 'bb-402')";

                    $result2 = mysql_query($query2);
                    $idfinanceiro_retornos = mysql_insert_id();
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

                    for ($i = 1; $i < $numLotes; $i++) {
                        $segmento = $blocos[$i];

                        if (substr($segmento, 0, 1) == 5) {
                            continue;
                        }

                        $nossonumero = substr($segmento, 72, 8) + 0;
                        //$seunumero = substr($segmentoT,54,15);
                        $vencimento = substr($segmento, 146, 2) . '/' . substr($segmento, 148, 2) . '/' . substr($segmento, 150, 2);
                        $datavencimento = 20 . substr($segmento, 150, 4) . '-' . substr($segmento, 148, 2) . '-' . substr($segmento, 146, 2);
                        $valortitulo = (substr($segmento, 152, 11) + 0) . ',' . (substr($segmento, 163, 2));
                        $valor = (substr($segmento, 152, 11) + 0) . '.' . (substr($segmento, 163, 2));
                        $tarifas = (substr($segmento, 181, 5) + 0) . ',' . (substr($segmento, 186, 2));
                        $tarifavalor = str_replace(",", ".", $tarifas);

                        $valorpago = (substr($segmento, 253, 11) + 0) . '.' . (substr($segmento, 264, 2));

                        $dtpagamento = substr($segmento, 110, 2) . '/' . substr($segmento, 112, 2) . '/' . substr($segmento, 114, 2);
                        $datapagamento =  20 . substr($segmento, 114, 2) . '-' . substr($segmento, 112, 2) . '-' . substr($segmento, 110, 2);

                        $status = (substr($segmento, 108, 2));

                        $juross = (substr($segmento, 266, 11) + 0) . ',' . (substr($segmento, 277, 2));
                        $juros = (substr($segmento, 266, 11) + 0) . '.' . (substr($segmento, 277, 2));

                        $descontos = (substr($segmento, 240, 11) + 0) . '.' . (substr($segmento, 252, 2));

                        $abatimentos = 0; //((substr($detalhe_arq,227,11)+0).'.'.(substr($detalhe_arq,238,2)));

                        $descontosTotal = $descontos + $abatimentos + 0;



                        if (($valor - $valorpago) > 0) {
                            $descontosTotal = ($valor - $valorpago) - $tarifavalor - $juros;
                        }

                        $nossonumero = ltrim($nossonumero, '0');

                        ?>
                        <tr>
                        <?php
                        $queryTMP1 = "SELECT
                                            cb.id AS cbid, cb.valor_baixa_min, cb.tarifaboleto,cb.retorno_data_baixa
                                        FROM
                                            empresas e
                                                INNER JOIN
                                            contasbanco cb ON e.id = cb.idempresa
                                                INNER JOIN
                                            alunos_fichafinanceira af ON cb.id = af.idcontasbanco
                                        WHERE
                                                cb.agencia = '$agencia'
                                                AND cb.conta LIKE '%$contacorrente%'
                                                AND cb.banconum = '$numerobanco'
                                                AND af.titulo = $nossonumero";

                        $resultTMP1 = mysql_query($queryTMP1);
                        $idcontasbanco = "";
                        while ($rowTMP1 = mysql_fetch_array($resultTMP1, MYSQL_ASSOC)) {
                            $idcontasbanco .= $rowTMP1['cbid'] . ",";
                            $baixa_min = $rowTMP1['valor_baixa_min'];
                            $descontosTotal -= $rowTMP1['tarifaboleto'];
                            $retorno_data_baixa = $rowTMP1['retorno_data_baixa'];
                        }
                        $idcontasbanco = substr_replace($idcontasbanco, ' ', -1);

                        if ($retorno_data_baixa == 1) { // data de crédito
                            $dtpagamento = substr($segmento, 175, 2) . '/' . substr($segmento, 177, 2) . '/' . substr($segmento, 179, 2);
                            $datapagamento =  20 . substr($segmento, 179, 2) . '-' . substr($segmento, 177, 2) . '-' . substr($segmento, 175, 2);
                        } else { // padrão geral: data liquidação
                            $dtpagamento = substr($segmento, 110, 2) . '/' . substr($segmento, 112, 2) . '/' . substr($segmento, 114, 2);
                            $datapagamento =  20 . substr($segmento, 114, 2) . '-' . substr($segmento, 112, 2) . '-' . substr($segmento, 110, 2);
                        }


                        $query3 = "SELECT nome, alunos.id as aid FROM pessoas, alunos, alunos_fichafinanceira WHERE alunos.idpessoa=pessoas.id AND alunos.id=alunos_fichafinanceira.idaluno AND alunos_fichafinanceira.titulo='$nossonumero'";
                        $result3 = mysql_query($query3);
                        $row3 = mysql_fetch_array($result3, MYSQL_ASSOC);
                        $idaluno = $row3['aid'];
                        ?>
                        <td><?=$cnt_rows?><?php $cnt_rows++;?></td>
                        <td><?=(!empty($row3['nome'])) ? $row3['nome'] : '---'?><br /><?=$nossonumero?></td>
                        <td>R$ <?=$valortitulo?></td>
                        <td><?=$vencimento?></td>
                        <td><?=$dtpagamento?></td>
                        <td>R$ <?=str_replace(".", ",", $descontosTotal)?></td>
                        <td>R$ <?=$tarifas?></td>
                        <td>R$ <?=$juross?></td>
                        <td>R$ <?=str_replace(".", ",", $valorpago)?></td>

                        <td>
                        <?php
                        $queryTMP1 = "SELECT
                                                                    cb.id AS cbid, cb.valor_baixa_min, cb.tarifaboleto
                                                                FROM
                                                                    empresas e
                                                                        INNER JOIN
                                                                    contasbanco cb ON e.id = cb.idempresa
                                                                        INNER JOIN
                                                                    alunos_fichafinanceira af ON cb.id = af.idcontasbanco
                                                                WHERE
                                                                        cb.agencia = '$agencia'
                                                                        AND cb.conta LIKE '%$contacorrente%'
                                                                        AND cb.banconum = '$numerobanco'
                                                                        AND af.titulo = $nossonumero";

                        $resultTMP1 = mysql_query($queryTMP1);
                        $idcontasbanco = "";
                        while ($rowTMP1 = mysql_fetch_array($resultTMP1, MYSQL_ASSOC)) {
                            $idcontasbanco .= $rowTMP1['cbid'] . ",";
                            $baixa_min = $rowTMP1['valor_baixa_min'];
                            $descontosTotal -= $rowTMP1['tarifaboleto'];
                        }
                        $idcontasbanco = substr_replace($idcontasbanco, ' ', -1);


                        if ($debug) {
                            echo $queryTMP . " " . $rowTMP . "<br />";
                        }

                        $queryTMP = "SELECT alunos_fichafinanceira.id as idffin FROM alunos_fichafinanceira WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ( SELECT contasbanco.id as cbid FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa AND contasbanco.agencia='$agencia' AND contasbanco.convenio='$convenio' )";
                        $resultTMP = mysql_query($queryTMP);
                        $rowTMP = mysql_fetch_array($resultTMP, MYSQL_ASSOC);
                        $idfichafinanceira = $rowTMP['idffin'];

                        $queryTMPBanco = "SELECT alunos_fichafinanceira.idcontasbanco as idbancotitulo FROM alunos_fichafinanceira WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ( SELECT contasbanco.id as cbid FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa AND contasbanco.agencia='$agencia' AND contasbanco.convenio='$convenio' )";
                        $resultTMPBanco = mysql_query($queryTMPBanco);
                        $rowTMPBanco = mysql_fetch_array($resultTMPBanco, MYSQL_ASSOC);
                        $idbancotitulo = $rowTMPBanco['idbancotitulo'];


                        if ($debug) {
                            echo $queryTMP . " " . $rowTMP . "<br />";
                        }
                        // if($status == 21 || $status == 35){ // old head 353
                        if ($status == 06 || $status == 46) {
                            if ($valor > 0) {
                                if (($valor - ((($valor) / 100) * $baixa_min)) <= ($valorpago + $tarifavalor)) {
                                    $query4 = "UPDATE alunos_fichafinanceira SET idfuncionario=$idfuncionario, situacao=6, datarecebimento='$datapagamento', desconto='" . ($descontosTotal + $tarifavalor) . "', juros='$juros', valorrecebido='$valorpago' WHERE situacao=0 AND titulo='$nossonumero' AND idcontasbanco IN ($idcontasbanco)";
                                    $result4 = mysql_query($query4);
                                    if ($debug) {
                                        echo $query4 . " " . $result4 . "<br />";
                                    }

                                    if (mysql_affected_rows() > 0) {
                                        $query5  = "INSERT INTO alunos_fichasrecebidas (idalunos_fichafinanceira, idfuncionario, datarecebido, datacompensado, valorrecebido, formarecebido, idcontasbanco) VALUES ($idfichafinanceira, '$idfuncionario', '$datapagamento', '$datapagamento', '$valorpago','4','$idbancotitulo')";
                                        $result5 = mysql_query($query5);
                                        if ($debug) {
                                            echo $query5 . " " . $result5 . "<br />";
                                        }

                                        echo "<font color='green'><b>SIM</b></font>";
                                        $query5 = "INSERT INTO financeiro_retornos_titulos (idfinanceiro_retornos,idfichafinanceira) VALUES ($idfinanceiro_retornos,$idfichafinanceira)";
                                        $result5 = mysql_query($query5);

                                        $query2 = "UPDATE
                                                                      contasbanco
                                                                   SET
                                                                      saldoatual = saldoatual+" . $valorpago . "
                                                                   WHERE id = " . $idcontasbanco;
                                        mysql_query($query2);
                                    } else {
                                        $query6 = "SELECT COUNT(*) as cnt, situacao FROM alunos_fichafinanceira WHERE titulo='$nossonumero'";

                                        $result6 = mysql_query($query6);
                                        $row6 = mysql_fetch_array($result6, MYSQL_ASSOC);
                                        echo "<font color='red'><b>NÃO</b></font>";
                                        if ($row6['cnt'] == 0) {
                                            echo "<br /><font color='red'><b>Título inexistente</b></font>";
                                        } else if (($row6['situacao'] == 6) || ($row6['situacao'] == 1)) {
                                            echo "<br /><font color='red'><b>Título já recebido.</b></font>";
                                        } else {
                                            echo "<br /><font color='red'><b>Dados incorretos.</b></font>";
                                        }
                                    }
                                } else {
                                    echo "<br /><font color='red'><b>Valores incorretos.</b></font>";
                                }
                            } else {
                                echo "<br /><font color='red'><b>Valor recebido igual a zero.</b></font>";
                            }
                            ?>
                            </td>
                            </tr>
                            <?php
                        } else {
                            echo "<font color='blue'><b>Remessa/Ocorrências - " . $status . "</b></font>";
                        }
                    }
                    ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
<?php } ?>
