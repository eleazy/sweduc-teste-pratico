<?php
include('headers.php');
include('conectar.php');
//use Illuminate\Database\Capsule\Manager as DB;

$debug = 0;

$agora = date("Y-m-d H:i");
$agora1 = date("d/m/Y H:i");

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$header_arq = $blocos[0];
$header_lote = $blocos[1];
$trailer_arq = $blocos[$cnt - 1];

$numerobanco = substr($header_arq, 76, 3);
if ($numerobanco != "033") { ?>
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div id="page-heading">
            <h1>O ARQUIVO RETORNO NÃO É DO BANCO SANTANDER (033)</h1>
        </div>
    </div>
    <?php
} else {
    $numLotes = substr($trailer_arq, 17, 6);
    $razaosocial = substr($header_arq, 46, 30);
    $cnpj = substr($header_arq, 18, 15);
    $agencia = substr($header_arq, 26, 4);//."-".substr($header_arq,36,1);
    $contacorrente = substr($header_arq, 30, 8) + 0;//."-".substr($header_arq,46,1);
    $header_arq1 = $blocos[1];
    $cnpj = substr($header_arq1, 3, 14);
    $cnpj = format_string("##.###.###/####-##", $cnpj);


    ?>
<br />
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div>
            <br clear="all" />
            <h3>
                Arquivo: <?=$arquivo?><br /><br />
                Empresa: <?=$razaosocial?><br />
                CNPJ: <?=$cnpj?><br />
                Agência: <?=$agencia?><br />
                Conta Corrente: <?=$contacorrente?>
            </h3>
        </div>

        <input type="button" value="   IMPRIMIR   " class="button noprint" onClick="window.print()" />

        <!-- end page-heading -->
        <div id="table-content">
            <?php
            $query1 = "SELECT Count(*) as cnt FROM financeiro_retornos WHERE nomearquivo='$arquivo' and agencia = " . $agencia . " and conta = " . $contacorrente;
            $result1 = mysql_query($query1);
            $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);

            if ($row1['cnt'] > 0) {
                echo "<h1>Esse arquivo já foi enviado</h1>";
            } else {
                if (!isset($idrelaod)) {
                    $query2 = "INSERT INTO financeiro_retornos (agencia, conta, banco, razaosocial, cnpj, nomearquivo, dataupload, arquivoupload, bancocod) VALUES ('$agencia', '$contacorrente', 'ITAU', '$razaosocial', '$cnpj', '$arquivo', '$agora', '" . $arquivoUpload . "', 'santander-242')";
                    $result2 = mysql_query($query2);
                    $idfinanceiro_retornos = mysql_insert_id();
                }
                ?>
                <script>$("#listaretornos").append("<tr id='linha<?=$idfinanceiro_retornos?>'><td><?=$arquivo?></td><td><?=$agora1?>h</td><td><input type='button' class='button bgred' onclick='exclui(<?=$idfinanceiro_retornos?>);' value=' X ' /></td></tr>");</script>


                <table border="0" width="100%" cellpadding="0" cellspacing="0" id="content-table-top">
                    <tr>
                        <th rowspan="3" class="sized" style="background-image:url('images/shared/side_shadowleft.jpg');background-size: 100% 100%;" width="20" height="79" alt="" /></th>
                        <th class="topleft"></th>
                        <td id="tbl-border-top-top">&nbsp;</td>
                        <th class="topright"></th>
                        <th rowspan="3" class="sized" style="background-image:url('images/shared/side_shadowright.jpg');background-size: 100% 100%;" width="20" height="79" alt="" /></th>
                    </tr>
                    <tr>
                        <td id="tbl-border-left-top"></td>
                        <td style="padding:30px;">
                            <table border="1" width="100%" cellpadding="0" cellspacing="0"  id="product-table">
                                <tr>
                                    <th class="table-header-repeat line-left"></th>
                                    <th class="table-header-repeat line-left">Aluno / Título</th>
                                    <th class="table-header-repeat line-left">Valor Título</th>
                                    <th class="table-header-repeat line-left">Data Pagamento</th>
                                    <th class="table-header-repeat line-left">Descontos</th>
                                    <th class="table-header-repeat line-left">Tarifa</th>
                                    <th class="table-header-repeat line-left">Juros</th>
                                    <th class="table-header-repeat line-left">Valor Recebido</th>
                                    <th class="table-header-repeat line-left">Recebido ?</th>
                                </tr>
                                <?php

                                $cnt_rows = 1;

                                for ($i = 1; $i < (is_countable($blocos) ? count($blocos) : 0); $i++) {
                                    $detalhe_arq = $blocos[$i];
                                    if (substr($detalhe_arq, 0, 1) == 1) {
                                        $status = (substr($detalhe_arq, 108, 2));

                                        if (!in_array($status, ["06", "07", "08", "17"])) {
                                            continue;
                                        }

                                        $nossonumero = substr($detalhe_arq, 62, 7);

                                        //$seunumero = substr($segmentoT,54,15);
                                        $vencimento = substr($detalhe_arq, 146, 2) . '/' . substr($detalhe_arq, 148, 2) . '/20' . substr($detalhe_arq, 150, 2);

                                        $datavencimento = '20' . substr($detalhe_arq, 150, 2) . '-' . substr($detalhe_arq, 148, 2) . '-' . substr($detalhe_arq, 146, 2);

                                        $valortitulo = (substr($detalhe_arq, 152, 11) + 0) . '.' . (substr($detalhe_arq, 163, 2));
                                        $valor = (substr($detalhe_arq, 152, 11) + 0) . '.' . (substr($detalhe_arq, 163, 2));

                                        $tarifas = (substr($detalhe_arq, 175, 11) + 0) . ',' . (substr($detalhe_arq, 186, 2));

                                        $tarifavalor = str_replace(",", ".", $tarifas);

                                        $valorpago = (substr($detalhe_arq, 253, 11) + 0) . '.' . (substr($detalhe_arq, 264, 2));

                                        $dtpagamento = substr($detalhe_arq, 110, 2) . '/' . substr($detalhe_arq, 112, 2) . '/20' . substr($detalhe_arq, 114, 2);
                                        $datapagamento = '20' . substr($detalhe_arq, 114, 2) . '-' . substr($detalhe_arq, 112, 2) . '-' . substr($detalhe_arq, 110, 2);


                                        $juross = (substr($detalhe_arq, 201, 11) + 0) . ',' . (substr($detalhe_arq, 212, 2));
                                        $juros = (substr($detalhe_arq, 201, 11) + 0) . '.' . (substr($detalhe_arq, 212, 2));
                                        $descontos = (substr($detalhe_arq, 240, 11) + 0) . '.' . (substr($detalhe_arq, 251, 2));

                                        $abatimentos = 0; //((substr($detalhe_arq,227,11)+0).'.'.(substr($detalhe_arq,238,2)));

                                        $descontosTotal = $descontos + $abatimentos + 0;

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
                                            $valortituloHTML = money_format('%.2n', $valortitulo);
                                            $valorpagoHTML = money_format('%.2n', $valorpago);

                                            $query3 = "SELECT nome, alunos.id as aid FROM pessoas, alunos, alunos_fichafinanceira WHERE alunos.idpessoa=pessoas.id AND alunos.id=alunos_fichafinanceira.idaluno AND alunos_fichafinanceira.titulo=$nossonumero";

                                            $result3 = mysql_query($query3);
                                            $row3 = mysql_fetch_array($result3, MYSQL_ASSOC);
                                            $idaluno = $row3['aid'];
                                            ?>
                                            <td><?=$cnt_rows?><?php $cnt_rows++;?></td>
                                            <td><?= $row3['nome'] ?><br/><?= $nossonumero ?></td>
                                            <td><?= $valortituloHTML ?></td>
                                            <td><?= $dtpagamento ?></td>
                                            <td>R$ <?= str_replace(".", ",", $descontosTotal) ?></td>
                                            <td><?= $tarifas ?></td>
                                            <td>R$ <?= $juross ?></td>
                                            <td><?= $valorpagoHTML ?></td>

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

                                                if ($status == 6 || $status == 7 || $status == 8 || $status == 17) {
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
                                                    $idcontasbanco = "";

                                                    while ($rowTMP1 = mysql_fetch_array($resultTMP1, MYSQL_ASSOC)) {
                                                        $idcontasbanco .= $rowTMP1['cbid'] . ",";
                                                        $baixa_min = $rowTMP1['valor_baixa_min'];
                                                    }
                                                    $idcontasbanco = substr_replace($idcontasbanco, ' ', -1);

                                                    $queryTMP = "SELECT alunos_fichafinanceira.id as idffin FROM alunos_fichafinanceira
                                                                    WHERE situacao=0 AND titulo=$nossonumero
                                                                    AND idcontasbanco IN
                                                                        ( SELECT contasbanco.id as cbid FROM empresas,contasbanco WHERE empresas.id=contasbanco.idempresa
                                                                            AND empresas.cnpj='" . $cnpj . "'
                                                                            AND contasbanco.agencia='$agencia' AND contasbanco.conta LIKE '%$contacorrente%' )";
                                                    $resultTMP = mysql_query($queryTMP);
                                                    $rowTMP = mysql_fetch_array($resultTMP, MYSQL_ASSOC);
                                                    $idfichafinanceira = $rowTMP['idffin'];
                                                    $idcontasbancotitulo = $rowTMP['idcbanco'];

                                                    $query4 = "UPDATE alunos_fichafinanceira SET idfuncionario=$idfuncionario, situacao=6,
                                                                    datarecebimento='$datapagamento', desconto='" . ($descontosTotal + $tarifavalor) . "', juros='$juros',
                                                                    valorrecebido='$valorpago' WHERE situacao=0 AND titulo=$nossonumero  -- AND idcontasbanco IN ($idcontasbanco)";
                                                    $result4 = mysql_query($query4);
                                                    if ($debug) {
                                                        echo $query4 . " " . $result4 . "<br />";
                                                    }


                                                    if (($valor - ((($valor) / 100) * $baixa_min)) <= ($valorpago + $tarifavalor)) {
                                                        if (mysql_affected_rows() > 0) {
                                                            $query5 = "INSERT INTO alunos_fichasrecebidas
                                                                    (idalunos_fichafinanceira, idfuncionario, datarecebido, datacompensado, valorrecebido,
                                                                    formarecebido, idcontasbanco, numeroForma, datavalidadeForma, bancoForma,
                                                                    agenciaForma, contaForma, outroForma)
                                                                  VALUES ($idfichafinanceira, $idfuncionario, '$datapagamento', '$datapagamento', '$valorpago','4',
                                                                          '$idcontasbancotitulo', '', '', '', '', '', '')";

                                                            $result5 = mysql_query($query5);
                                                            if ($debug) {
                                                                echo $query5 . " " . $result5 . "<br />";
                                                            }

                                                            echo "<font color='green'><b>SIM</b></font>";
                                                            $query5 = "INSERT INTO financeiro_retornos_titulos (idfinanceiro_retornos,idfichafinanceira) VALUES
                                                                            ($idfinanceiro_retornos,$idfichafinanceira)";
                                                            $result5 = mysql_query($query5);

                                                            $query2 = "UPDATE
                                                                          contasbanco
                                                                       SET
                                                                          saldoatual = saldoatual+" . $valorpago . "
                                                                       WHERE id = " . $idcontasbanco;
                                                                        mysql_query($query2);
                                                        } else {
                                                            $query6 = "SELECT COUNT(*) as cnt, situacao FROM alunos_fichafinanceira WHERE titulo=$nossonumero";

                                                            $result6 = mysql_query($query6);
                                                            $row6 = mysql_fetch_array($result6, MYSQL_ASSOC);
                                                            echo "<font color='red'><b>NÃO</b></font>";

                                                            if ($row6['cnt'] == 0) {
                                                                echo "<br /><font color='red'><b>Título inexistente</b></font>";
                                                                // $updateFRT = DB::table('financeiro_retornos_titulos')
                                                                //     ->join('alunos_fichafinanceira', 'financeiro_retornos_titulos.idfichafinanceira', '=', 'alunos_fichafinanceira.id')
                                                                //     ->where('financeiro_retornos_titulos.idfinanceiro_retornos', $idfinanceiro_retornos)
                                                                //     ->where('alunos_fichafinanceira.titulo', $nossonumero)
                                                                //     ->where('financeiro_retornos_titulos.resultado', '')
                                                                //     ->update(['financeiro_retornos_titulos.resultado' => 'TITULO_INEXISTENTE']);
                                                            } else if (($row6['situacao'] == 6) || ($row6['situacao'] == 1)) {
                                                                echo "<br /><font color='red'><b>Título já recebido.</b></font>";
                                                                // $updateFRT = DB::table('financeiro_retornos_titulos')
                                                                //     ->join('alunos_fichafinanceira', 'financeiro_retornos_titulos.idfichafinanceira', '=', 'alunos_fichafinanceira.id')
                                                                //     ->where('financeiro_retornos_titulos.idfinanceiro_retornos', $idfinanceiro_retornos)
                                                                //     ->where('alunos_fichafinanceira.titulo', $nossonumero)
                                                                //     ->where('financeiro_retornos_titulos.resultado', '')
                                                                //     ->update(['financeiro_retornos_titulos.resultado' => 'TITULO_JA_RECEBIDO']);
                                                            } else {
                                                                echo "<br /><font color='red'><b>Dados incorretos.</b></font>";
                                                                // $updateFRT = DB::table('financeiro_retornos_titulos')
                                                                //     ->join('alunos_fichafinanceira', 'financeiro_retornos_titulos.idfichafinanceira', '=', 'alunos_fichafinanceira.id')
                                                                //     ->where('financeiro_retornos_titulos.idfinanceiro_retornos', $idfinanceiro_retornos)
                                                                //     ->where('alunos_fichafinanceira.titulo', $nossonumero)
                                                                //     ->where('financeiro_retornos_titulos.resultado', '')
                                                                //     ->update(['financeiro_retornos_titulos.resultado' => 'DADOS_INCORRETOS']);
                                                            }
                                                            //if ($tarifas!=$tarifaboleto) echo "<br /><font color='red'><b>Tarifa cobrada pelo banco diferente da tarifa cobrada pela escola.</b></font>";
                                                        }
                                                    } else {
                                                        echo "<br /><font color='red'><b>Valores incorretos.</b></font>";
                                                        // $updateFRT = DB::table('financeiro_retornos_titulos')
                                                        //     ->join('alunos_fichafinanceira', 'financeiro_retornos_titulos.idfichafinanceira', '=', 'alunos_fichafinanceira.id')
                                                        //     ->where('financeiro_retornos_titulos.idfinanceiro_retornos', $idfinanceiro_retornos)
                                                        //     ->where('alunos_fichafinanceira.titulo', $nossonumero)
                                                        //     ->where('financeiro_retornos_titulos.resultado', '')
                                                        //     ->update(['financeiro_retornos_titulos.resultado' => 'VALORES_INCORRETOS']);
                                                    }
                                                } else {
                                                    echo "<font color='blue'><b>Remessa/Ocorrências - " . $status . "</b></font>";
                                                    // $idAf = DB::table('alunos_fichafinanceira')->where('titulo', $nossonumero)->value('id');
                                                    // $insertFRT = DB::table('financeiro_retornos_titulos')->insert([
                                                    //     'idfinanceiro_retornos' => $idfinanceiro_retornos,
                                                    //     'idfichafinanceira' => $idAf,
                                                    //     'tarifaValor' => $tarifaValor,
                                                    //     'resultado' => 'REMESSA_OCORRENCIAS - ' . $status
                                                    // ]);
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </table>
                        </td>
                        <td id="tbl-border-right-top"></td>
                    </tr>
                    <tr>
                        <th class="sized bottomleft"></th>
                        <td id="tbl-border-bottom-top">&nbsp;</td>
                        <th class="sized bottomright"></th>
                    </tr>
                </table>
            <?php } ?>
        </div>
    </div>
<?php } ?>
