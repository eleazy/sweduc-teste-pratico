<?php

use App\Financeiro\Retorno\RetornoItau402;

use function App\Framework\resolve;

include('headers.php');
include_once('function/ultilidades.func.php');

$retorno = resolve(RetornoItau402::class);

$debug = 0;

$agora = date("Y-m-d H:i");
$agora1 = date("d/m/Y H:i");

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

$trailer_arq = $blocos[$cnt];
$numLotes = substr($trailer_arq, 17, 8) + 0;

$header_arq = $blocos[0];
$numerobanco = substr($header_arq, 76, 3);
if ($numerobanco != "341") { ?>
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div id="page-heading">
            <h3>O ARQUIVO RETORNO NÃO É DO BANCO ITAÚ (341)</h3>
        </div>
    </div>
    <?php
} else {
    $razaosocial = substr($header_arq, 46, 30);
    $agencia = substr($header_arq, 26, 4);
    $contacorrente = substr($header_arq, 32, 5) . "-" . substr($header_arq, 37, 1);

    $header_arq1 = $blocos[1];
    $cnpj = substr($header_arq1, 3, 14);
    $cnpj = format_string("##.###.###/####-##", $cnpj);
    ?>

    <br />
    <!-- start content -->
    <div id="content">
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
                    $query2 = "INSERT INTO financeiro_retornos (agencia, conta, banco, razaosocial, cnpj, nomearquivo, dataupload, arquivoupload, bancocod) VALUES ('$agencia', '$contacorrente', 'ITAU', '$razaosocial', '$cnpj', '$arquivo', '$agora', '" . $arquivoUpload . "', 'itau-402')";
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

                        $status = (substr($detalhe_arq, 108, 2));

                        if ($status != '02' && $status != '09' && $status != '29') {
                            $nossonumero = substr($detalhe_arq, 85, 8) + 0;
                            //$seunumero = substr($detalhe_arq,54,15);
                            $vencimento = substr($detalhe_arq, 146, 2) . '/' . substr($detalhe_arq, 148, 2) . '/20' . substr($detalhe_arq, 150, 2);

                            $datavencimento =  '20' . substr($detalhe_arq, 150, 2) . '-' . substr($detalhe_arq, 148, 2) . '-' . substr($detalhe_arq, 146, 2);

                            $valortitulo = (substr($detalhe_arq, 152, 11) + 0) . ',' . (substr($detalhe_arq, 163, 2));
                            $valor = (substr($detalhe_arq, 152, 11) + 0) . '.' . (substr($detalhe_arq, 163, 2));

                            $tarifas = (substr($detalhe_arq, 175, 11) + 0) . ',' . (substr($detalhe_arq, 186, 2));
                            $tarifavalor = str_replace(",", ".", $tarifas);

                            $valorpago = intval(substr($detalhe_arq, 253, 13)); //.','.(substr($detalhe_arq,261,2));
                            $valorpago = $valorpago / 100;

                            $valorpago += $tarifavalor; // adicionando a tarifa do itau ao valor pago

                            $juross = (substr($detalhe_arq, 266, 11) + 0) . ',' . (substr($detalhe_arq, 277, 2));
                            $juros = (substr($detalhe_arq, 266, 11) + 0) . '.' . (substr($detalhe_arq, 277, 2));

                            $abatimentos = ((substr($detalhe_arq, 227, 11) + 0) . '.' . (substr($detalhe_arq, 238, 2)));
                            $descontos = ((substr($detalhe_arq, 240, 11) + 0) . '.' . (substr($detalhe_arq, 251, 2)));
                            $descontosTotal = $descontos + $abatimentos + 0;

                            $tarifavalor = floatval($tarifavalor);
                            $juros = floatval($juros);
                            $valor = floatval($valor);


                            if (($valor - $valorpago) > 0) {
                                $descontosTotal = ($valor - $valorpago) - $tarifavalor - $juros;
                            }

                            ?>
                            <tr>
                                <?php
                                $query3 = "SELECT P.nome, A.id as aid
                                           FROM alunos_fichafinanceira AF
                                           LEFT JOIN alunos A ON (AF.idaluno = A.id)
                                           LEFT JOIN pessoas P ON (A.idpessoa = P.id)
                                           WHERE AF.titulo='$nossonumero'";
                                $result3 = mysql_query($query3);
                                $row3 = mysql_fetch_array($result3, MYSQL_ASSOC);
                                $idaluno = $row3['aid'];

                                $queryTMP1 = "SELECT
                                        cb.id AS cbid,
                                        cb.valor_baixa_min,
                                        cb.tarifaboleto,
                                        cb.retorno_data_baixa
                                    FROM
                                        empresas e
                                            INNER JOIN
                                        contasbanco cb ON e.id = cb.idempresa
                                            INNER JOIN
                                        alunos_fichafinanceira af ON cb.id = af.idcontasbanco
                                    WHERE
                                        e.cnpj = '$cnpj' AND
                                        cb.agencia = '$agencia' AND
                                        cb.conta LIKE '%$contacorrente%' AND
                                        cb.banconum = '$numerobanco' AND
                                        af.titulo = '$nossonumero'";

                                $resultTMP1 = mysql_query($queryTMP1);
                                $idcontasbanco = "";

                                while ($rowTMP1 = mysql_fetch_array($resultTMP1, MYSQL_ASSOC)) {
                                    $idcontasbanco .= $rowTMP1['cbid'] . ",";
                                    $baixa_min = $rowTMP1['valor_baixa_min'];
                                    $descontosTotal = $descontosTotal - $rowTMP1['tarifaboleto'];
                                    $retorno_data_baixa = $rowTMP1['retorno_data_baixa'];
                                }

                                $idcontasbanco = substr_replace($idcontasbanco, ' ', -1);

                                if ($retorno_data_baixa == 1) { // data de crédito
                                    $dtpagamento = (substr($detalhe_arq, 295, 2) != '  ') ? substr($detalhe_arq, 295, 2) . '/' . substr($detalhe_arq, 297, 2) . '/20' . substr($detalhe_arq, 299, 2) : '----------';
                                    $datapagamento =   (substr($detalhe_arq, 299, 2) != '  ') ? '20' . substr($detalhe_arq, 299, 2) . '-' . substr($detalhe_arq, 297, 2) . '-' . substr($detalhe_arq, 295, 2) : '----------';
                                } else { // padrão geral: data liquidação
                                    $dtpagamento = substr($detalhe_arq, 110, 2) . '/' . substr($detalhe_arq, 112, 2) . '/20' . substr($detalhe_arq, 114, 2);
                                    $datapagamento = '20' . substr($detalhe_arq, 114, 2) . '-' . substr($detalhe_arq, 112, 2) . '-' . substr($detalhe_arq, 110, 2);
                                }
                                ?>
                                <td><?=$cnt_rows?><?php $cnt_rows++;?></td>
                                <td><?=$row3['nome']?><br /><?=$nossonumero?></td>
                                <td>R$ <?=$valortitulo?></td>
                                <td><?=dateEntoBr($datavencimento)?></td>
                                <td><?=$dtpagamento?></td>
                                <td>R$ <?=str_replace(".", ",", $descontosTotal)?></td>
                                <td>R$ <?=$tarifas?></td>
                                <td>R$ <?=$juross?></td>
                                <td>R$ <?=str_replace(".", ",", $valorpago)?></td>
                                <td>
                                    <?php
                                    $resultado = $retorno->processar(
                                        $baixa_min,
                                        $datapagamento,
                                        $descontosTotal,
                                        $idcontasbanco,
                                        $idfinanceiro_retornos,
                                        $idfuncionario,
                                        $juros,
                                        $nossonumero,
                                        $status,
                                        $tarifavalor,
                                        $valor,
                                        $valorpago,
                                    );
                                    ?>

                                    <?php if ($resultado == RetornoItau402::SITUACAO_OK) : ?>
                                        <font color='green'><b>SIM</b></font>
                                    <?php else : ?>
                                        <font color='red'><b>NÃO</b></font>
                                    <?php endif ?>

                                    <?php if ($resultado == RetornoItau402::ERRO_CONTA_BANCARIA) : ?>
                                        <br /><font color='red'><b>Conta bancária incorreta (Favor editar conta do título)</b></font>
                                    <?php endif ?>

                                    <?php if ($resultado == RetornoItau402::TITULO_INEXISTENTE) : ?>
                                        <br /><font color='red'><b>Título inexistente</b></font>
                                    <?php endif ?>

                                    <?php if ($resultado == RetornoItau402::TITULO_JA_RECEBIDO) : ?>
                                        <br /><font color='red'><b>Título já recebido.</b></font>
                                    <?php endif ?>

                                    <?php if ($resultado == RetornoItau402::DADOS_INCORRETOS) : ?>
                                        <br /><font color='red'><b>Dados incorretos.</b></font>
                                    <?php endif ?>

                                    <?php if ($resultado == RetornoItau402::VALORES_INCORRETOS) : ?>
                                        <br /><font color='red'><b>Valores incorretos.</b></font>
                                    <?php endif ?>

                                    <?php if ($resultado == RetornoItau402::VALOR_RECEBIDO_IGUAL_A_ZERO) : ?>
                                        <br /><font color='red'><b>Valor recebido igual a zero.</b></font>
                                    <?php endif ?>

                                    <?php if ($resultado == RetornoItau402::STATUS_DESCONHECIDO) : ?>
                                        <font color='blue'><b>Remessa/Ocorrências - <?=$status?></b></font>
                                    <?php endif ?>
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
