<?php
include('../headers.php');
include('conectar.php');
include_once('logs.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$agora = date("Y-m-d H:i:s");
$hoje = date("Y-m-d");
$hora = date("H:i:s");

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}
include('../permissoes.php');

$valor = str_replace(',', '.', str_replace('.', '', $valor));

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];

function saldoFinal($acao, $idfuncionario, $idcontabanco, $idmovimentacao, $saldo = null, $datasaldo = null)
{
    $q = null;
    if ($acao == 'excluir') {
        $q = "DELETE FROM contasbanco_saldofinal where idmovimentacao = $idmovimentacao AND idcontabanco=$idcontabanco AND idfuncionario=$idfuncionario";
    } elseif ($acao == 'inserir') {
        $q = "INSERT INTO contasbanco_saldofinal (idfuncionario,idcontabanco,saldo,datasaldo,idmovimentacao) VALUES ($idfuncionario,$idcontabanco,'$saldo','$datasaldo',$idmovimentacao)";
    }


    $e = mysql_query($q);

    return (!$e) ? mysql_error() : "Ok";
}

if ($action == "transferir") {
    $tmp = explode("/", $datatrans);
    $datatrans = $tmp[2] . "-" . $tmp[1] . "-" . $tmp[0];

    if ($motivo == -1) {
        $motivo = $motivonovo;
    }

    if ($motivo == 'sangria') {
        $motivostr = "Sangria";
    } elseif ($motivo == 'transferenciasimples') {
        $motivostr = "Transferência Simples";
    } else {
        $motivostr = "Fechamento de Caixa";
    }

    $q_saldoatual = "SELECT saldoinicial, saldoatual, saldoremanescente FROM contasbanco WHERE id = $idbancoorigem ";

    $r_saldoatual = mysql_query($q_saldoatual);
    $row_saldoatual = mysql_fetch_array($r_saldoatual, MYSQL_ASSOC);
    $func_saldoatual = $row_saldoatual['saldoatual'];
    $func_saldoinicial = $row_saldoatual['saldoinicial'];
    $func_saldoremanescente = $row_saldoatual['saldoremanescente'];


  // verifica movimentações de fechamento anteriores
    $q_mov = "SELECT * FROM movimentacoes
                WHERE
                    idcontasorigem = " . $idbancoorigem . "
                    AND motivo LIKE 'Fechamento%'";

    $r_mov = mysql_query($q_mov);

    $fechamentoanterior = (mysql_num_rows($r_mov) < 1) ? false : true;

    if ($valor > ($func_saldoatual + $func_saldoinicial + $func_saldoremanescente + $valortotalcash)) {
        $msg = "O valor fornecido excede o disponível.";
        echo "red|O valor fornecido excede o disponível.";
    } else {
        $valorrestanteinicial = ($fechamentoanterior) ? $func_saldoremanescente : $func_saldoinicial;
        $dif_valor = ($func_saldoatual - $valor);
        $query  = "INSERT INTO movimentacoes(
                                          idcontasorigem,
                                          idcontasdestino,
                                          valorrestante,
                                          valorrestanteinicial,
                                          valor,
                                          idfuncionarioenvio,
                                          dataenvio,
                                          datareferencia,
                                          motivo,
                                          idfuncionariostatus,
                                          datastatus,
                                          status,
                                          recebida,
                                          historioco)
                                        VALUES (
                                          $idbancoorigem,
                                          $idbancodestino,
                                          ($dif_valor+$valorrestanteinicial),
                                          $valorrestanteinicial,
                                          $valor,
                                          $idfuncionario,
                                          '$agora',
                                          '$datatrans',
                                          '$motivostr',
                                          $idfuncionario,
                                          '$agora',
                                          1,
                                          '" . mysql_real_escape_string($fonterecebida) . "',
                                          '" . mysql_real_escape_string($historico) . "')"; // HCN


        $idmovimentacao = 0;
        if ($result = mysql_query($query)) {
            $idmovimentacao = mysql_insert_id();
            $dataatual = date('Y-m-d');

          // contasbanco_saldofinal
            if ($motivo == 'fechamento') {
                $SalvaSaldoFinal = saldoFinal('inserir', $idfuncionario, $idbancoorigem, ($dif_valor + $valorrestanteinicial), $datatrans, $idmovimentacao);
            }

            if ($motivo == 'fechamento') {
                $q_upd_contasbanco = "UPDATE contasbanco SET saldoremanescente = ($valorrestanteinicial+$dif_valor),saldoatual = 0,dataatual='$dataatual' WHERE id = $idbancoorigem";
            } else {
                $q_upd_contasbanco = "UPDATE contasbanco SET saldoatual = $dif_valor,dataatual='$dataatual' WHERE id = $idbancoorigem";
            }
            $queryPegarBancoOrigem = "SELECT banconome  AS origem FROM contasbanco WHERE id=$idbancoorigem";
            $queryPegarBancoDestino = "SELECT banconome AS destino FROM contasbanco WHERE id=$idbancodestino";
            $resultadoBancoOrigem = mysql_query($queryPegarBancoOrigem);
            $resultadoBancoDestino = mysql_query($queryPegarBancoDestino);
            while ($linhaBanOrigem = mysql_fetch_array($resultadoBancoOrigem)) {
                $BanOrigem = $linhaBanOrigem['origem'];
            }
            while ($linhaBanDestino = mysql_fetch_array($resultadoBancoDestino)) {
                $BanDestino = $linhaBanDestino['destino'];
            }
            $r_upd_contasbanco = mysql_query($q_upd_contasbanco);
            $msg = "Transferência de movimentação financeira realizada. De: " . $BanOrigem . " Para: " . $BanDestino;
            echo "blue|Transferência realizada.|" . $idmovimentacao;
        } else {
            $msg = "Erro ao transferir !";
            echo "red|Erro ao transferir !";
        }
        $parametroscsv = $idbancoorigem . ',' . $idbancodestino . ',' . $valor . ',' . $idfuncionario . ',' . $datatrans . ',' . $motivo;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "atualizar") {
    $query  = "UPDATE movimentacoes SET idfuncionariostatus=$idfuncionario,datastatus='$agora',status=" . $status . " WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        $msg = "Atualização com sucesso.";
        echo "blue|Atualização com sucesso.";
    } else {
        $msg = "Erro ao atualizar !";
        echo "red|Erro ao atualizar !";
    }
    $parametroscsv = $idfuncionario . ',' . $hoje . ',' . $status . ',' . $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "listacontas") {
    $query  = "SELECT id FROM funcionarios WHERE funcionarios.idpessoa=" . $idpessoalogin;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $idfuncionario = $row['id'];

    $query = "SELECT movimentacoes.*, p1.nome as p1nome, p2.nome as p2nome, DATE_FORMAT(dataenvio,'%d/%m/%Y') as dtenvio, DATE_FORMAT(datareferencia,'%d/%m/%Y') as dtref, cb1.conta as conta1,cb1.saldoremanescente as saldoremanescente1, cb2.conta as conta2, cb1.agencia as agencia1, cb1.saldoinicial as saldoinicial1, cb2.agencia as agencia2, cb1.nomeb as nomeb1, cb2.nomeb as nomeb2, cb2.idfuncionario as cb2idfuncionario, cb2.tipo as cb2tipo FROM movimentacoes";
    $query .= " INNER JOIN funcionarios as f1 ON f1.id=movimentacoes.idfuncionarioenvio";
    $query .= " INNER JOIN pessoas as p1 ON p1.id=f1.idpessoa";

    $query .= " INNER JOIN contasbanco as cb1 ON movimentacoes.idcontasorigem=cb1.id";
    $query .= " INNER JOIN contasbanco as cb2 ON movimentacoes.idcontasdestino=cb2.id";

    $query .= " LEFT JOIN funcionarios as f2 ON ( f2.id=cb2.idfuncionario ) ";
    $query .= " LEFT JOIN pessoas as p2 ON ( p2.id=f2.idpessoa ) ";

    $query .= " WHERE idfuncionarioenvio = " . $idfuncionario;

    $query .= " ORDER BY dataenvio DESC, valor ASC";

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $vrestante = $row['valorrestante'];

        echo '<tr>';
        echo '<td>' . $row['dtenvio'] . '</td>';
        echo '<td>Banco: ' . $row['nomeb1'];
        echo '<br />Agência: ' . $row['agencia1'] . '<br />Conta Corrente: ' . $row['conta1'] . '<br />' . $row['p1nome'] . '</td>';
        echo '<td>Banco: ' . $row['nomeb2'];
        echo '<br />Agência: ' . $row['agencia2'] . '<br />Conta Corrente: ' . $row['conta2'] . '<br />' . $row['p2nome'] . '</td>';
        echo '<td>' . money_format('%.2n', $row['valor']) . '<br />Restante: ' . money_format('%.2n', $vrestante) . '</td>';
        echo '<td>' . nl2br($row['motivo']) . '<br />Referência: ' . $row['dtref'] . '</td>';
        echo '<td>';
        if ($row['historioco'] != '' && $row['recebida'] != '') {
            echo '<input value="Recibo"  onclick="recibo(' . $row['id'] . ');" class="btn grey-color" type="button">';
        } else {
            echo '';
        }
        echo '</th>';
        echo '</tr>';
    }
} elseif ($action == "listapagtos") {
    $query  = "SELECT id FROM funcionarios WHERE funcionarios.idpessoa=" . $idpessoalogin;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $idfuncionario = $row['id'];

    $q_chk_envios_anteriores = "SELECT COUNT(id) as cnt_ant FROM movimentacoes WHERE idfuncionarioenvio = $idfuncionario";
    $r_chk_envios_anteriores = mysql_query($q_chk_envios_anteriores);
    $row_envios_anteriores = mysql_fetch_array($r_chk_envios_anteriores, MYSQL_ASSOC);
  // se existirem transferencias anteriores
    $query_data = '';
    if ($row_envios_anteriores['cnt_ant'] > 0) {
        $q_ultimadataenvio = "SELECT DATE_FORMAT(dataenvio,'%Y-%m-%d') as ultimadtenvio FROM movimentacoes WHERE idfuncionarioenvio = $idfuncionario ORDER BY id DESC LIMIT 1";
        $r_ultimadata = mysql_query($q_ultimadataenvio);
        $row_ultdata = mysql_fetch_array($r_ultimadata, MYSQL_ASSOC);
        $ultdata = $row_ultdata['ultimadtenvio'];

        $query_data = "AND afr.datarecebido > '$ultdata'";
    }


    $query = "SELECT afr.idfuncionario, p.nome, DATE_FORMAT(afr.datarecebido,'%d/%m/%Y') as datarecebido, afr.valorrecebido,afr.formarecebido,fp.formapagamento,afr.idcontasbanco,cb.nomeb,aff.idaluno,aff.nummatricula,aff.titulo,aff.valorrecebido as fichafin_valorrecebido,afi.eventofinanceiro,afi.valor as itemvalor, afr.numeroForma,afr.bancoForma,afr.agenciaForma,afr.contaForma,afr.pracaForma,DATE_FORMAT(afr.datacompensado,'%d/%m/%Y') as datacompensado
            FROM alunos_fichasrecebidas afr
            INNER JOIN formaspagamentos fp ON afr.formarecebido=fp.id
            INNER JOIN alunos_fichafinanceira aff ON afr.idalunos_fichafinanceira=aff.id
            INNER JOIN alunos al ON aff.idaluno=al.id
            INNER JOIN pessoas p ON al.idpessoa=p.id
            LEFT JOIN alunos_fichaitens afi ON aff.id=afi.idalunos_fichafinanceira
            LEFT JOIN contasbanco cb ON afr.idfuncionario=cb.idfuncionario
            WHERE afr.idfuncionario = $idfuncionario AND afr.formarecebido in (1,2) " . $query_data . "
            GROUP BY afr.idalunos_fichafinanceira,afr.formarecebido HAVING COUNT(*) > 0";

    $result = mysql_query($query);
    $valortotal = 0.00;
    $valortotalcheque = 0.00;

    $numtitulo = 0;

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $exibevalorrecebido = ($numtitulo == $row['titulo']) ? false : true;
        $vrecebido = 'R$ ' . money_format('%!.2n', $row['valorrecebido']);
        $vtitulo   = (!$exibevalorrecebido) ? '&nbsp;&nbsp;&nbsp;\'\'&nbsp;&nbsp;&nbsp;' : $row['titulo'];
        $ffin_recebido   = (!$exibevalorrecebido) ? '&nbsp;' : 'R$ ' . $row['fichafin_valorrecebido'];
        $numtitulo = $row['titulo'];

        echo '<tr>';
        echo '<td>' . ((!$exibevalorrecebido) ? '' : $row['datarecebido']) . '</td>';
        echo '<td>' . ( (!$exibevalorrecebido) ? '' : $row['nome']) . '</td>';
        echo '<td>' . $vtitulo . '</td>';
        echo '<td>' . $ffin_recebido . '</td>';
        echo '<td>';
        echo ($row['formarecebido'] == 1) ? $vrecebido : '';
        echo '</td>';
        echo '<td>';
        echo ($row['formarecebido'] == 2) ? $vrecebido : '';
        echo '</td>';
        echo '<td>' . $row['nomeb'] . '</td>';
        echo '<td>';
        if ($row['formarecebido'] == 2) {
            ?> Bco: <?=$row['bancoForma']?>/<?=$row['pracaForma']?><br />Ag.: <?=$row['agenciaForma']?> - Cc: <?=$row['contaForma']?><br /> N&deg;: <?=$row['numeroForma']?><br />Data comp.: <?=$row['datacompensado']?><?php
        }
        echo '</td>';
        echo '</tr>';
        if ($row['formarecebido'] == 1) {
            $valortotal = $valortotal + $row['valorrecebido'];
        }
        if ($row['formarecebido'] == 2) {
            $valortotalcheque = $valortotalcheque + $row['valorrecebido'];
        }
    }

    echo '<tr>';
    echo '<td colspan="7" style="vertical-align: middle;">Total</td>';

    echo '<td>Dinheiro: R$ ' . money_format('%!.2n', $valortotal) . '<br />Cheque: R$ ' . money_format('%!.2n', $valortotalcheque) . '</td>';
    echo '<input type="hidden" id="valortotalsomado" name="valortotalsomado" value="' . ($valortotal + $valortotalcheque) . '">';
    echo '</tr>';
} elseif ($action == "listapagtostipo") {
    $query  = "SELECT id FROM funcionarios WHERE funcionarios.idpessoa=" . $idpessoalogin;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $idfuncionario = $row['id'];

    $qdata = date("Y-m-d");
    $query_data = "AND afr.datarecebido BETWEEN '" . $qdata . " 00:00:00' AND '" . $qdata . " 23:59:59'";

    $qtipo = "SELECT SUM(afr.valorrecebido) as total,afr.formarecebido,fp.formapagamento,afr.idcontasbanco,aff.idaluno,aff.nummatricula,aff.titulo,afi.eventofinanceiro
            FROM alunos_fichasrecebidas afr
            INNER JOIN formaspagamentos fp ON afr.formarecebido=fp.id
            INNER JOIN alunos_fichafinanceira aff ON afr.idalunos_fichafinanceira=aff.id
            LEFT JOIN alunos_fichaitens afi ON aff.id=afi.idalunos_fichafinanceira
            WHERE afr.idfuncionario = $idfuncionario $query_data
            GROUP BY afr.formarecebido";


    $resulttipo = mysql_query($qtipo);

    $num_rows = mysql_num_rows($resulttipo);

    $valortotal = 0.00;

    if ($num_rows > 0) {
        echo "<h2>Totais por forma de recebimento em " . date('d/m/Y') . ":</h2>";
    }

    while ($rowtipo = mysql_fetch_array($resulttipo, MYSQL_ASSOC)) {
        echo '<span style="margin-left:25px;display:inline-block;width:110px;">' . $rowtipo['formapagamento'] . ': </span>R$ ' . money_format('%!.2n', $rowtipo['total']);
        echo '<br />';

        $valortotal = $valortotal + $rowtipo['total'];
    }

    if ($num_rows > 0) {
        echo '<span style="margin-left:25px;display:inline-block;width:110px;font-weight:bolder;">Total geral: </span><span style="border-top:1px solid #999999;">R$ ' . money_format('%!.2n', $valortotal) . '</span>';
    }
    $valorMonimentacao = 0;
    $queryMovimentacao = "SELECT
                                    sum(valor) valor
                                FROM
                                    movimentacoes m
                                        INNER JOIN
                                    contasbanco c ON m.idcontasorigem = c.id
                                        INNER JOIN
                                    funcionarios f ON c.idfuncionario = f.id
                                WHERE
                                    f.id = " . $idfuncionario . "
                                        AND m.dataenvio BETWEEN '" . $hoje . " 00:00:00' AND '" . $hoje . " 23:59:59'";
    $resulMovimentacao = mysql_query($queryMovimentacao);
    $rowvMovimentacao = mysql_fetch_array($resulMovimentacao, MYSQL_ASSOC);
    $valorMonimentacao = $rowvMovimentacao['valor'];

    if ($valorMonimentacao > 0) {
        echo '<br />';
        echo '<span style="margin-left:25px;display:inline-block;width:110px;">Transferências: </span><span style="">R$ -' . money_format('%!.2n', $valorMonimentacao) . '</span>';

        echo '<br />';
        echo '<span style="margin-left:25px;display:inline-block;width:110px;font-weight:bolder;">Total geral: </span><span style="border-top:1px solid #999999;">R$ ' . money_format('%!.2n', $valortotal - $valorMonimentacao) . '</span>';
    }
} elseif ($action == "atualizasaldoatual") {
    $query  = "SELECT id FROM funcionarios WHERE funcionarios.idpessoa=" . $idpessoalogin;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $idfuncionario = $row['id'];
    $bancofunc = $idbancoorigem;

    $querybanco = "SELECT saldoinicial,saldoremanescente,saldoatual FROM contasbanco WHERE idfuncionario = $idfuncionario AND id = $bancofunc";
    $resultquerybanco = mysql_query($querybanco);
    $rowbanco = mysql_fetch_array($resultquerybanco, MYSQL_ASSOC);

    $q_mov = "SELECT * FROM movimentacoes
                WHERE
                    idcontasorigem = " . $idbancoorigem . "
                    AND motivo LIKE 'Fechamento%'";

    $r_mov = mysql_query($q_mov);
    $fechamentoanterior = (mysql_num_rows($r_mov) < 1) ? false : true;
    $s_atual = (!$fechamentoanterior) ? ($rowbanco['saldoatual'] + $rowbanco['saldoinicial']) : $rowbanco['saldoatual'] + $rowbanco['saldoremanescente'];

    echo ($fechamentocaixa == 1) ? money_format('%!.2n', ($rowbanco['saldoremanescente'])) : money_format('%!.2n', ($s_atual));
} elseif ($action == "reabrir") {
    $query  = "SELECT * FROM movimentacoes   WHERE id IN ($id)";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $valorrestanteMovimentacoes = $row['valorrestante'];
    $valorMovimentacoes = $row['valor'];
    $idcontasorigem = $row['idcontasorigem'];
    $idcontasdestino = $row['idcontasdestino'];
    $tiporecebido = $row['tiporecebido'];

    $query = "DELETE FROM movimentacoes WHERE id IN ($id)";
    if ($result = mysql_query($query)) {
        if ($tiporecebido == 1) {
            $querybanco = "SELECT * FROM contasbanco WHERE id = $idcontasorigem";
            $resultquerybanco = mysql_query($querybanco);
            $rowbanco = mysql_fetch_array($resultquerybanco, MYSQL_ASSOC);

            $q_upd_contasbanco = "UPDATE contasbanco SET saldoatual = (saldoatual+$valorMovimentacoes) WHERE id =" . $idcontasorigem;

            if ($result = mysql_query($q_upd_contasbanco)) {
                $status = 1;
            } else {
                $status = -1;
                $q_upd_contasbanco = "UPDATE contasbanco SET saldoatual = (" . $rowbanco['dataatual'] . ")
                          WHERE id =" . $idcontasorigem;
                mysql_query($q_upd_contasbanco);
                $query  = "INSERT INTO movimentacoes(idcontasorigem, idcontasdestino, valorrestante,valor,
                              idfuncionarioenvio, dataenvio, motivo, idfuncionariostatus, datastatus,
                              status, recebida, historioco) VALUES
                              (" . $row['idcontasorigem'] . ", " . $row['idcontasdestino']  . ", " . $row['valorrestante']  . ", " . $row['valor']  . ", " . $row['idfuncionarioenvio']  . ",
                              '" . $row['dataenvio']  . "', '" . $row['motivo']  . "', " . $row['idfuncionariostatus']  . ", '" . $row['datastatus']  . "', 1,
                              '" . $row['recebida']  . "', '" . $row['historioco']  . "')"; // HCN
                mysql_query($query);
            }
        } else {
            $status = 1;
        }
    } else {
        $status = -1;
    }

// atualiza contasbanco_saldofinal
    $atualizaSaldoFinal = saldoFinal('excluir', $row['idfuncionarioenvio'], $row['idcontasorigem'], null, null, $row['id']);

    if ($status > 0) {
        $q_upd_contasbanco = "UPDATE contasbanco SET saldoatual = (saldoatual-$valorMovimentacoes)
                          WHERE id =" . $idcontasdestino;

        $result = mysql_query($q_upd_contasbanco);

        $msg = "blue|Movimentação deletada com sucesso.";
    } else {
        $msg = "red|Erro ao deletar Movimentação.";
    }
    echo $msg;
}
