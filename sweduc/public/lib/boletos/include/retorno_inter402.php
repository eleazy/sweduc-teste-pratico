<?php
session_start();
include('headers.php');
include('conectar.php');

include_once('function/ultilidades.func.php');
$debug = 0;

$agora = date("Y-m-d H:i");
$agora1 = date("d/m/Y H:i");

$trailer_arq = $blocos[$cnt];
$numLotes = substr($trailer_arq, 17, 8) + 0;

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$header_arq = $blocos[0];
$numerobanco = substr($header_arq, 76, 3);
if ($numerobanco != "077") { ?>
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div id="page-heading">
            <input type="hidden" name="" numbanco="<?=$numerobanco?>">
            <h3>O ARQUIVO RETORNO NÃO É DO BANCO INTER (077)</h3>
        </div>
    </div>
    <?php
}

if ($numerobanco == "077") {
    $razaosocial = substr($header_arq, 46, 30);

    $header_arq1 = $blocos[1];

    $agencia = substr($header_arq1, 140, 4);
    $contacorrente = substr($header_arq1, 29, 7) . "-" . substr($header_arq1, 36, 1);

    $cnpj = substr($header_arq1, 3, 14);
    $cnpj = format_string("##.###.###/####-##", $cnpj);
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
            $query1 = "SELECT Count(*) as cnt FROM financeiro_retornos WHERE nomearquivo='$arquivo' and agencia = " . $agencia . " and conta = " . $contacorrente;
            $result1 = mysql_query($query1);
            $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
            if ($row1['cnt'] > 0) {
                echo "<h1>Esse arquivo já foi enviado</h1>";
            } else {
                if (!isset($idrelaod)) {
                    $query2 = "INSERT INTO financeiro_retornos (agencia, conta, banco, razaosocial, cnpj, nomearquivo, dataupload, arquivoupload, bancocod) VALUES ('$agencia', '$contacorrente', 'INTER', '$razaosocial', '$cnpj', '$arquivo', '$agora', '" . $arquivoUpload . "', 'inter-402')";
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

                    for ($i = 1; $i < $cnt; $i++) {
                        $detalhe_arq = $blocos[$i];

                        $numerodocumento = (int) substr($detalhe_arq, 107, 11);

                        if ($numerodocumento > 0) {
                            $status = substr($detalhe_arq, 89, 2);
                            $numerotitulo = intval(substr($detalhe_arq, 97, 10));

                            $statusNomes = [
                                "02" => "Em aberto",
                                "03" => "Erro",
                                "06" => "Pago",
                                "07" => "Baixado",
                            ];

                            $statusNome = $statusNomes[$status];

                            $identificacaobanco = substr($detalhe_arq, 107, 11);
                            $vencimento = substr($detalhe_arq, 118, 2) . '/' . substr($detalhe_arq, 120, 2) . '/20' . substr($detalhe_arq, 122, 2);

                            $datavencimento =  '20' . substr($detalhe_arq, 122, 2) . '-' . substr($detalhe_arq, 120, 2) . '-' . substr($detalhe_arq, 118, 2);

                            $valortitulo = (substr($detalhe_arq, 124, 11) + 0) . ',' . (substr($detalhe_arq, 135, 2));
                            $valor = (substr($detalhe_arq, 124, 11) + 0) . '.' . (substr($detalhe_arq, 135, 2));

                            $valorpago = intval(substr($detalhe_arq, 159, 13)); //.','.(substr($detalhe_arq,261,2));
                            $valorpago = $valorpago / 100;

                            $dtpagamento = substr($detalhe_arq, 172, 2) . '/' . substr($detalhe_arq, 174, 2) . '/20' . substr($detalhe_arq, 176, 2);
                            $datapagamento = '20' . substr($detalhe_arq, 176, 2) . '-' . substr($detalhe_arq, 174, 2) . '-' . substr($detalhe_arq, 172, 2);

                            if ($datapagamento == '2000-00-00') {
                                $dtpagamento = '--/--/----';
                                $datapagamento = '';
                            }
                            ?>
                            <tr>
                                <?php
                                $query3 = "SELECT nome, alunos.id as aid FROM pessoas, alunos, alunos_fichafinanceira WHERE alunos.idpessoa=pessoas.id AND alunos.id=alunos_fichafinanceira.idaluno AND alunos_fichafinanceira.titulo='$numerotitulo'";

                                $result3 = mysql_query($query3);
                                $row3 = mysql_fetch_array($result3, MYSQL_ASSOC);
                                $idaluno = $row3['aid'];
                                ?>
                                <td><?=$cnt_rows?><?php $cnt_rows++;?></td>
                                <td><?=$row3['nome']?><br /><?=$numerotitulo?></td>
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
                                                          AND af.titulo = $numerotitulo";

                                    $resultTMP1 = mysql_query($queryTMP1);
                                    $idcontasbanco = "";
                                    while ($rowTMP1 = mysql_fetch_array($resultTMP1, MYSQL_ASSOC)) {
                                        $idcontasbanco .= $rowTMP1['cbid'] . ",";
                                        $baixa_min = $rowTMP1['valor_baixa_min'];
                                    }
                                    $idcontasbanco = substr_replace($idcontasbanco, ' ', -1);

                                    $queryTMP = "SELECT alunos_fichafinanceira.id as idffin FROM alunos_fichafinanceira WHERE situacao=0 AND titulo='$numerotitulo' AND idcontasbanco IN ($idcontasbanco)";
                                    $resultTMP = mysql_query($queryTMP);
                                    $rowTMP = mysql_fetch_array($resultTMP, MYSQL_ASSOC);
                                    $idfichafinanceira = $rowTMP['idffin'];
                                    $idcontasbancotitulo = $rowTMP['idcbanco'];

                                    if ($status == 06 || $status == 15 || $status == 17) {
                                        if ($valor > 0) {
                                            if (($valor - ((($valor) / 100) * $baixa_min)) <= ($valorpago + $tarifavalor)) {
                                                $query4 = "UPDATE alunos_fichafinanceira SET idfuncionario=$idfuncionario, situacao=6, datarecebimento='$datapagamento', desconto='" . ($descontosTotal + $tarifavalor) . "', juros='$juros', valorrecebido='$valorpago', identificacao_banco=$identificacaobanco WHERE situacao=0 AND titulo='$numerotitulo' AND idcontasbanco IN ($idcontasbanco)";
                                                $result4 = mysql_query($query4);
                                                if ($debug) {
                                                    echo $query4 . " " . $result4 . "<br />";
                                                }

                                                if (mysql_affected_rows() > 0) {
                                                    $query5  = "INSERT INTO alunos_fichasrecebidas (idalunos_fichafinanceira, idfuncionario, datarecebido, datacompensado, valorrecebido, formarecebido, idcontasbanco, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, outroForma) VALUES ($idfichafinanceira, $idfuncionario, '$datapagamento', '$datapagamento', '$valorpago','4','$idcontasbancotitulo', '', '', '', '', '', '')";
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
                                                    $query6 = "SELECT COUNT(*) as cnt, situacao FROM alunos_fichafinanceira WHERE titulo='$numerotitulo'";
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
                                                echo "<br /><font color='red'><b>Valor pago abaixo do esperado.</b></font>";
                                            }
                                        } else {
                                            echo "<br /><font color='red'><b>Valor recebido igual a zero.</b></font>";
                                        }
                                    } elseif ($status == 02 || $status == 07) {
                                        $datavencimento =  '20' . substr($detalhe_arq, 122, 2) . '-' . substr($detalhe_arq, 120, 2) . '-' . substr($detalhe_arq, 118, 2);
                                        $queryVencimento = "SELECT datavencimento FROM alunos_fichafinanceira WHERE situacao=0 AND titulo='$numerotitulo' AND idcontasbanco IN ($idcontasbanco) LIMIT 1";
                                        $resultVencimento = mysql_query($queryVencimento);
                                        $fichaFinanceiraVencimento = mysql_fetch_array($resultVencimento, MYSQL_ASSOC);

                                        if ($fichaFinanceiraVencimento['datavencimento'] == $datavencimento) {
                                            $query7 = "UPDATE alunos_fichafinanceira SET idfuncionario=$idfuncionario, identificacao_banco=$identificacaobanco WHERE situacao=0 AND titulo='$numerotitulo' AND idcontasbanco IN ($idcontasbanco)";
                                            $result7 = mysql_query($query7);

                                            echo "<font color='blue'><b>Boleto Disponível com Sucesso - " . $status . " - <strong>" . $statusNome . "</strong></b></font>";
                                        } else {
                                            echo "<font color='red'><b>Boleto Não Disponível!</b></font>";
                                        }

                                    } else {
                                        echo "<font color='blue'><b>Remessa/Ocorrências - " . $status . " - <strong>" . $statusNome . "</strong></b></font>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
<?php } ?>
