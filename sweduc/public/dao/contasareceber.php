<?php

use App\Event\MovimentacaoDeTitulo;
use App\Asaas\Models\Asaas;
use App\Model\Financeiro\AsaasCobranca;
use Illuminate\Database\Capsule\Manager as IDB;
use App\Model\Financeiro\Conta;

use function App\Framework\app;

include '../headers.php';
include 'conectar.php';
include_once 'logs.php';
include '../function/ultilidades.func.php';
require_once 'mysql.class.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php';
require '../function/validar.php';
require '../permissoes.php';

$conn = new db();
$conn->open();

function idcontaboleto($idficha)
{
    $query = "SELECT idcontasbanco FROM alunos_fichafinanceira WHERE id = " . $idficha;
    $result1 = mysql_query($query);
    $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
    return $row1['idcontasbanco'];
}

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

$agora     = date("Y-m-d H:i:s");
$hoje      = date("Y-m-d");
$hojemais2 = date('Y-m-d', strtotime($hoje . ' + 2 days'));
$finaldoc  = "A";

$debug = 0;

$query1  = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $row1['id'];


$dtvencimento = explode("/", $datavencimento);
$datavencimento = $dtvencimento[2] . "-" . $dtvencimento[1] . "-" . $dtvencimento[0];

$date = DateTime::createFromFormat("d/m/Y", $datarecebimento);
if ($date == false) {
    $datarecebimento = $hoje;
} else {
    $dtrecebimento = explode("/", $datarecebimento);
    $datarecebimento = $dtrecebimento[2] . "-" . $dtrecebimento[1] . "-" . $dtrecebimento[0];
}

$dtpagamento = explode("/", $RECdatapagamento);
$datapagamento = $dtpagamento[2] . "-" . $dtpagamento[1] . "-" . $dtpagamento[0];

$bolsa = str_replace(',', '.', str_replace('.', '', $RECbolsa));
$desconto = str_replace(',', '.', str_replace('.', '', $RECdesconto));
$multa = $RECmulta;//str_replace(',', '.',  str_replace('.', '', $RECmulta));
$juros = $RECjuros;//str_replace(',', '.',  str_replace('.', '', $RECjuros));
$abono = isset($RECabono) ? str_replace(',', '.', str_replace('.', '', $RECabono)) : 0;
if ($abono > 0) {
    if ($abono == $multa + $juros) {
        $multa = 0;
        $juros = 0;
    } elseif ($abono == $multa) {
        $multa = 0;
    } elseif ($abono == $juros) {
        $juros = 0;
    }
}

if ($action == "removeFItem") {
    $query  = "DELETE FROM alunos_fichaitens WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        $query  = "UPDATE alunos_fichafinanceira SET valor=valor-$valordoitem WHERE id=" . $idff;
        if ($result = mysql_query($query)) {
            $msg = 'Ficha item removida com sucesso.';
            $status = 0;
            echo "blue|Item removido com sucesso";
        } else {
            $msg = 'Erro ao remover ficha item.';
            $status = 1;
            $erro++;
            echo "red|Ocorreu um erro ao tentar deletar o item";
        }
    } else {
        $msg = 'Erro ao remover ficha item.';
        $status = 1;
        $erro++;
        echo "red|Ocorreu um erro ao tentar deletar o item";
    }
    $parametroscsv = $id . ',' . $query;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "receber") {
    mysql_query("START TRANSACTION;");
    $avencer = IntervalDays($datarecebimento, $datavencimento);

    if (($avencer < 0) && ($perdebolsa == "1")) {
        $bolsa = 0;
    }
    $valoresperado = $ROWvalor - $descdesconto - $bolsa + $multa + $juros;

    if (trim($descontoparcelas) == "") {
        $descontoparcelas = 0;
    }
    $descontoparcelas = str_replace(",", ".", str_replace(".", "", $descontoparcelas));

    $query  = "UPDATE alunos_fichafinanceira SET situacao=1, idfuncionario=$idfuncionario, datarecebimento='$datarecebimento',desconto='$desconto',bolsa='$bolsa', multa='$multa', juros='$juros', valorrecebido='$RECvalorTotalrecebido' WHERE id=" . $idalunos_fichafinanceira;

    if ($result = mysql_query($query)) {
        $titulo = IDB::table('alunos_fichafinanceira')->where('id', $idalunos_fichafinanceira)->value('titulo');
        $msg = 'Titulo : ' . $titulo . ' Ficha financeira recebida com sucesso.';
        $status = 0;
    } else {
        $msg = 'Erro ao receber ficha financeira.';
        $status = 1;
        $erro++;
    }

    $parametroscsv = $idalunos_fichafinanceira . ',' . $datarecebimento . ',' . $desconto . ',' . $bolsa . ',' . $multa . ',' . $juros . ',' . $RECvalorTotalrecebido;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    // INSERIR NAS FICHAS RECEBIDAS OS RECEBIMENTOS
    $indice = 0;
    // Array associativo id => dias para compensação das formas de recebimento
    $compensacao = [];
    $formasRecebimento = "'" . implode("','", $Rformarecebido) . "'";

    $compensacao_res = mysql_query("SELECT id, diascredito FROM formaspagamentos WHERE id IN ($formasRecebimento);");
    while ($crow = mysql_fetch_assoc($compensacao_res)) {
        $compensacao[$crow['id']] = $crow['diascredito'];
    }

    $parametroscsv = $idalunos_fichafinanceira;
    foreach ($Rvalor as $Rval) {
        if ($Rformarecebido[$indice] != 2 && $Rformarecebido[$indice] != 1) {
            $Ridcontasbanco[$indice] = idcontaboleto($idalunos_fichafinanceira);
        }

        $Rdtvalidade = $Rdatavalidade[$indice] ? explode("/", $Rdatavalidade[$indice]) : null;
        if ($Rdtvalidade === null) {
            $Rdataval = 'NULL';
        } elseif (count($Rdtvalidade) == 3) {
            $Rdataval = "'" . $Rdtvalidade[2] . "-" . $Rdtvalidade[1] . "-" . $Rdtvalidade[0] . "'";
        } else {
            $Rdataval = "'" . $Rdtvalidade[1] . "-" . $Rdtvalidade[0] . "-01" . "'";
        }

        if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $Rdatacompensacao[$indice])) {
            $compensado_em = $Rdatacompensacao[$indice];
        } else {
            $dtcompensacao   = explode('/', $Rdatacompensacao[$indice]);
            $compensado_em = $dtcompensacao[2] . "-" . $dtcompensacao[1] . "-" . $dtcompensacao[0];

            if ($compensado_em == "--") {
                $compensacao_incremento = $compensacao[$Rformarecebido[$indice]] ?? 0;
                $compensado_em = date('Y-m-d', strtotime($hoje . " + $compensacao_incremento days"));
            }
        }

        $query  = "INSERT INTO alunos_fichasrecebidas (idalunos_fichafinanceira, idfuncionario, datarecebido, datacompensado, valorrecebido, formarecebido, idcontasbanco, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, pracaForma, outroForma) VALUES ($idalunos_fichafinanceira, $idfuncionario, '$hoje', '$compensado_em', '$Rval', '$Rformarecebido[$indice]', '$Ridcontasbanco[$indice]', '$Rnumero[$indice]', $Rdataval, '$Rbanco[$indice]', '$Ragencia[$indice]', '$Rccorrente[$indice]', '$RpracaForma[$indice]', '$Routro[$indice]')";

        if (!($result = mysql_query($query))) {
            $erro++;
        }

        if ($Rformarecebido[$indice] == 2 || $Rformarecebido[$indice] == 1) {
            $query2 = "UPDATE
                          contasbanco
                       SET
                          saldoatual = saldoatual+" . $Rvalor[$indice] . "
                       WHERE id = " . $Ridcontasbanco[$indice];

            mysql_query($query2);
        } else {
            $Ridcontasbanco[$indice] =  idcontaboleto($idalunos_fichafinanceira);
            $query2 = "UPDATE
                          contasbanco
                       SET
                          saldoatual = saldoatual+" . $Rvalor[$indice] . "
                       WHERE tipo <> 1  and id = " . $Ridcontasbanco[$indice];
            mysql_query($query2);
        }

        $indice++;
        $parametroscsv .= "," . $Rformarecebido[$indice] . ',' . $Ridcontasbanco[$indice] . ',' . $Rnumero[$indice] . ',' . $Rdataval . ',' . $Rbanco[$indice] . ',' . $Ragencia[$indice] . ',' . $Rccorrente[$indice] . ',' . $RpracaForma[$indice] . ',' . $Routro[$indice];
    }

    if (empty($erro)) {
        // Dá baixa na cobrança no Asaas (recebido em dinheiro)
        $idConta = IDB::table('alunos_fichafinanceira')->where('id', $idalunos_fichafinanceira)->value('idcontasbanco');
        $contaModel = Conta::find($idConta);
        $usaApi = $contaModel->usabancoAPI;
        if ($usaApi) {
            switch ($contaModel->banconome) {
                case 'asaas':
                    $cobranca = AsaasCobranca::where('id_alunos_fichafinanceira', $idalunos_fichafinanceira)->first();
                    if ($cobranca) {
                        try {
                            $asaas = new Asaas();
                            $asaas->receberEmDinheiro($cobranca, $RECvalorTotalrecebido);
                        } catch (Exception $e) {
                            $erro++;
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    }

    if (empty($erro)) {
        mysql_query("COMMIT;");
        $msg = 'Recebimento do titulo de numero ' . $titulo . ' efetuado com sucesso.';
        echo "blue|$msg|1";
        $status = 0;

        app()->evento(MovimentacaoDeTitulo::recebimento((int) $idalunos_fichafinanceira, (int) $idfuncionario));
    } else {
        mysql_query("ROLLBACK;");
        $msg = "Erro($erro) ao efetuar o recebimento.";
        echo "red|$msg|0";
        $status = 1;
        $erro++;
    }
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebercaixarapido") {
    $query = "SELECT * FROM contasbanco where idfuncionario = " . $idfuncionario;

    $result = mysql_query($query);
    $financeiro = '';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $conta = $row;
    }

    $valorfinal = 0;
    foreach ($valor as $v) {
        if ($v != '') {
            $valorT = explode('|', $v);

            $dataValForma = (str_contains($valorT[3], '/')) ? implode('-', array_reverse(explode('/', $valorT[3]))) : $valorT[3];

            $query = "INSERT IGNORE INTO alunos_fichasrecebidas
                                  (idalunos_fichafinanceira, idfuncionario, datarecebido, datacompensado, valorrecebido, formarecebido,
                                    idcontasbanco, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, pracaForma, outroForma)
                            VALUES ($idlancamento, $idfuncionario, '$hoje', '$hoje', '" . $valorT[0] . "', '" . $valorT[1] . "',
                                    '"  . $conta['id'] . "', '" . $valorT[2]  . "', '" .  $dataValForma . "', '', '" .  $valorT[4]  . "',
                                    '" .  $valorT[5]  . "', '" .  $valorT[6]  . "', '" .  $valorT[7]  . "')";

            if (!($result = mysql_query($query))) {
                $erro++;
            }

            if ($valorT[1] == 1 || $valorT[1] == 2) {
                $query2 = "UPDATE contasbanco
                           SET saldoatual = saldoatual + {$valorT[0]}
                           WHERE id = {$conta['id']}";

                echo " * query2: " . $query2 . "<br>";
                mysql_query($query2);
            }

            $valorfinal += $valorT[0];
        }
    }

    $desconto = $valortotal - $valorfinal > 0 ? $valortotal - $valorfinal : 0 ;

    $query  = "UPDATE alunos_fichafinanceira SET
                situacao=1, idfuncionario=$idfuncionario, datarecebimento='" . $hoje . "',
                desconto=" . $desconto . ",bolsa=0, multa=0, juros=0,
                valorrecebido='$valorfinal' WHERE id=" . $idlancamento;

    mysql_query($query);


    $parametroscsv .= "," . $Rformarecebido[$indice] . ',' . $Ridcontasbanco[$indice] . ',' . $Rnumero[$indice] . ',' . $Rdataval . ',' . $Rbanco[$indice] . ',' . $Ragencia[$indice] . ',' . $Rccorrente[$indice] . ',' . $RpracaForma[$indice] . ',' . $Routro[$indice];

    $query = "SELECT * FROM alunos_fichafinanceira WHERE id=" . $idlancamento;
    $queryParaAcharOTitulo = "SELECT titulo FROM alunos_fichafinanceira WHERE id =" . $idalunos_fichafinanceira;
    $resultado = mysql_query($queryParaAcharOTitulo);
    while ($rowTitulo = mysql_fetch_assoc($resultado)) {
        $titulo = $rowTitulo['titulo'];
    }

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $fichasR = $row['titulo'];
    }

    if ($erro == 0) {
        app()->evento((MovimentacaoDeTitulo::recebimento((int) $idlancamento, (int) $idfuncionario)));
        echo "blue|Recebimento efetuado com sucesso.|" . $idlancamento;
        $msg = 'Recebimento efetuado com sucesso. Numero do Titulo: ' . $titulo;
        $status = 0;
    } else {
        echo "red|Erro($erro) ao efetuar o recebimento.|0";
        $msg = "Erro($erro) ao efetuar o recebimento.";
        $status = 1;
        $erro++;
    }
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cancelar") {
    $query  = "UPDATE alunos_fichafinanceira SET situacao=2, datacancelado='$hoje' WHERE id in ($id)";
    $queryParaAcharOTitulo = "SELECT titulo FROM alunos_fichafinanceira WHERE id =" . $id;
    $resultado = mysql_query($queryParaAcharOTitulo);
    while ($rowTitulo = mysql_fetch_assoc($resultado)) {
        $titulo = $rowTitulo['titulo'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Lançamento cancelado.";
        $msg = 'Lançamento cancelado. Numero do titulo: ' . $id;
        $status = 0;
    } else {
        echo "red|Erro ao cancelar lançamento.";
        $msg = 'Erro ao cancelar lançamento.';
        $status = 1;
        $erro++;
    }
    $idi = implode(",", $id);
    $parametroscsv = $idi;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "reabre") {
    mysql_query("START TRANSACTION;");
    $hoje = date('Y-m-d');
    $query1  =
       "SELECT
            af.*, ar.idcontasbanco, SUM(ar.valorrecebido) as valordinheiro
        FROM
            alunos_fichafinanceira af
                INNER JOIN
            alunos_fichasrecebidas ar ON af.id = ar.idalunos_fichafinanceira
                INNER JOIN
            contasbanco c ON ar.idcontasbanco = c.id
        WHERE
            af.id = '$id'
        AND
            af.datarecebimento = '$hoje'
        AND
            formarecebido IN (1, 2);
    ";
    $result1 = mysql_query($query1);
    $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);

    $recebidoHoje = date("Y-m-d") == $row1['datarecebimento'];

    if ((is_countable($row1) ? count($row1) : 0) > 0 && $recebidoHoje) {
        $query = "UPDATE contasbanco SET saldoatual = saldoatual - {$row1['valordinheiro']} WHERE id = {$row1['idcontasbanco']}";
        $result = mysql_query($query);
    }

    $query = "UPDATE alunos_fichafinanceira SET situacao=0, valorrecebido=0, multa=0, juros=0, desconto=0, datarecebimento='0000-00-00' WHERE id in ($id)";
    $result = mysql_query($query);
    if ($result) {
        app()->evento(MovimentacaoDeTitulo::reabertura((int) $id, (int) $_SESSION['idfuncionario']));
        $query = "DELETE FROM alunos_fichasrecebidas WHERE idalunos_fichafinanceira in ($id)";
        $result = mysql_query($query);

        $queryBanco = IDB::select("SELECT banconome, usabancoAPI FROM contasbanco cb
                        JOIN alunos_fichafinanceira af ON af.idcontasbanco = cb.id
                        WHERE af.id = ?", [$id])[0];
        $usaApi = $queryBanco->usabancoAPI;
        if ($usaApi) {
            switch ($queryBanco->banconome) {
                case 'asaas':
                    $cobranca = AsaasCobranca::where('id_alunos_fichafinanceira', $id)->first();
                    try {
                        $asaas = new Asaas();
                        $asaas->desfazerRecebimentoEmDinheiro($cobranca);
                    } catch (Exception $e) {
                        $erro++;
                        $result = false;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    if ($result && $recebidoHoje) {
        mysql_query("COMMIT;");
        $tituloNum = IDB::table('alunos_fichafinanceira')->where('id', $id)->value('titulo');
        $msg = 'Lançamento reaberto. Numero do titulo: ' . $tituloNum;
        $status = 0;
        echo json_encode([ 'status' => 'success', 'msg' => $msg ]);
    } elseif ($result) {
        mysql_query("COMMIT;");
        $msg = 'A reabertura de título retroativo não afeta o valor do caixa.';
        $status = 0;
        echo json_encode([ 'status' => 'warning', 'msg' => $msg ]);
    } else {
        mysql_query("ROLLBACK;");
        $msg = 'Erro ao reabrir lançamento(s).';
        $status = 1;
        $erro++;
        echo json_encode([ 'status' => 'error', 'msg' => $msg ]);
    }

    $idi = is_array($id) ? implode(",", $id) : $id;
    $parametroscsv = $idi;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "devolve") {
    $dtdevolucao = explode("/", $datadevolucao);
    $datadevolucao = $dtdevolucao[2] . "-" . $dtdevolucao[1] . "-" . $dtdevolucao[0];
    $ids = explode(",", $id);
    foreach ($ids as $k) {
        $query  = "UPDATE alunos_fichasrecebidas SET datadevolvido='$datadevolucao', datareaberto='$hoje', idfuncionariodevolvido='$idfuncionario', alinea='$alinea' WHERE id=" . $k;
        if ($result = mysql_query($query)) {
            $query  = "UPDATE alunos_fichafinanceira SET situacao=0, valorrecebido=0, datarecebimento='0000-00-00', desconto=0, multa=0, juros=0 WHERE id=(SELECT idalunos_fichafinanceira FROM alunos_fichasrecebidas WHERE id=" . $k . ")";

            if ($result = mysql_query($query)) {
                echo "blue|Lançamento(s) devolvido(s).";
                $msg = 'Lançamento(s) reaberto(s).';
                $status = 0;
            } else {
                echo "red|Erro ao devolver lançamento(s).";
                $msg = 'Erro 1 ao devolver lançamento(s).';
                $status = 1;
                $erro++;
            }
        } else {
            echo "red|Erro ao devolver lançamento(s).";
            $msg = 'Erro 2 ao devolver lançamento(s).';
            $status = 1;
            $erro++;
        }
    }
    $parametroscsv = $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "reapresenta") {
    $ids = explode(",", $id);
    $msg = $ids;
    foreach ($ids as $k) {
        $query  = "UPDATE alunos_fichasrecebidas SET datareapresentado='$hoje', datacompensado='$hojemais2', idfuncionarioreapresentado='$idfuncionario' WHERE id=" . $k;
        $msg .= $query;
        if ($result = mysql_query($query)) {
            $query = "SELECT idalunos_fichafinanceira, valorrecebido FROM alunos_fichasrecebidas WHERE id=" . $k;
            $msg .= $query;
            if ($result = mysql_query($query)) {
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $idalunos_fichafinanceira = $row['idalunos_fichafinanceira'];
                $valorrecebido = $row['valorrecebido'];

                $query = "SELECT empresas.multa, empresas.mora FROM alunos_fichafinanceira, contasbanco, empresas WHERE alunos_fichafinanceira.idcontasbanco=contasbanco.id AND contasbanco.idempresa=empresas.id AND alunos_fichafinanceira.id=" . $idalunos_fichafinanceira;
                $result = mysql_query($query);
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $multaempresa = $row['multa'];
                $moraempresa = $row['mora'];

                $query = "SELECT * FROM alunos_fichafinanceira WHERE alunos_fichafinanceira.id=" . $idalunos_fichafinanceira;
                $result = mysql_query($query);
                $row = mysql_fetch_array($result, MYSQL_ASSOC);

                $multa = 0;
                $juros = 0;
                $bolsa = $row['bolsa'];
                $dtvencimento = $row['datavencimento'];

                $avencer = IntervalDays($hoje, $dtvencimento);
                if ($avencer < 0) {
                    if ($perdebolsa == "1") {
                        $bolsa = 0;
                    }
                    $multa = ( $multaempresa * ( $row['valor'] - $bolsa ) ) / 100;
                    $juros = (-1) * $avencer * ( $moraempresa * ( $row['valor'] - $bolsa ) / 100 );
                }

//$valorfinal=number_format ( ($row['valor']-$bolsa+$juros+$multa) , 2 , ',' , '.' );



                $query = "UPDATE alunos_fichafinanceira SET situacao=1, multa='$multa', juros='$juros', valorrecebido='$valorrecebido', datarecebimento='$hoje' WHERE id=" . $idalunos_fichafinanceira;
                $msg .= $query;
                if ($result = mysql_query($query)) {
                    echo "blue|Cheque(s) reapresentado(s).";
                    $msg .= 'Cheque(s) reapresentado(s).';
                    $status = 0;
                } else {
                    echo "red|Erro 1 ao reapresentar cheque(s).";
                    $msg .= 'Erro 1 ao reapresentar cheque(s).';
                    $status = 1;
                    $erro++;
                }
            } else {
                echo "red|Erro 2 ao reapresentar cheque(s).";
                $msg .= 'Erro 2 ao reapresentar cheque(s).';
                $status = 1;
                $erro++;
            }
        }
    }
    //echo "<br />[".$idpessoalogin."]<br />".$msg ."<br />";
    $parametroscsv = $ids;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "baixar") {
    $query  = "UPDATE alunos_fichafinanceira SET situacao=3, databaixado='$hoje' WHERE id in ($id)";
    $queryParaAcharOTitulo = "SELECT titulo FROM alunos_fichafinanceira WHERE id =" . $id;
    $resultado = mysql_query($queryParaAcharOTitulo);
    while ($rowTitulo = mysql_fetch_assoc($resultado)) {
        $titulo = $rowTitulo['titulo'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Lançamento baixado.";
        $msg = 'Lançamento baixado, numero referente ao titulo: ' . $titulo . '';
        $status = 0;
    } else {
        echo "red|Erro ao baixar lançamento.";
        $msg = 'Erro ao baixar lançamento.';
        $status = 1;
        $erro++;
    }
    $idi = implode(",", $id);
    $parametroscsv = $idi;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagar") {
    $id_funcionario = $_SESSION['id_funcionario'];
    $autorizadoExcluirTitulos = $usuario_permissoes['financeiro']['excluir-titulos'];
    $idsArray = explode(',', $id);

    _validar([
        'Usuário não autorizado' => !$id_funcionario || !$autorizadoExcluirTitulos
    ], 403);

    $query  = "UPDATE alunos_fichafinanceira SET situacao=4, dataexcluido='$hoje', excluido_por_id_funcionario='$id_funcionario' WHERE id in ($id)";
    $titulos = IDB::table('alunos_fichafinanceira')->whereIn('id', $idsArray)->pluck('titulo')->toArray();

    $responseObj = [];
    if ($result = mysql_query($query)) {
        app()->evento(MovimentacaoDeTitulo::exclusao((int) $id, (int) $id_funcionario));
        $variosTitulos = is_array($titulos) && count($titulos) > 1;
        $msg = 'Titulo' . ( $variosTitulos ? 's' : '') . ' ' . implode(', ', (array)$titulos) . ' ' . ($variosTitulos ? 'foram' : 'foi') . ' excluído' . ($variosTitulos ? 's' : '') . '.';
        $status = 0;

        $cobrancasAsaas = AsaasCobranca::whereIn('id_alunos_fichafinanceira', $idsArray)->get();
        if ($cobrancasAsaas->count() > 0) {
            $asaas = new Asaas();
            foreach ($cobrancasAsaas as $cobranca) {
                $titulo = IDB::table('alunos_fichafinanceira')->where('id', $cobranca->id_alunos_fichafinanceira)->value('titulo');
                try {
                    $response = $asaas->excluirCobranca($cobranca->id_asaas);
                } catch (Exception $e) {
                    $responseObj[] = ["error" => "Erro ao excluir titulo " . $titulo . "Erro: " . json_decode($e->getMessage())];
                    // Se não conseguir excluir a cobrança no Asaas, reativa o título
                    $query  = "UPDATE alunos_fichafinanceira SET situacao=0, dataexcluido='0000-00-00', excluido_por_id_funcionario=0 WHERE id in ($id)";
                    $result = mysql_query($query);
                    $msg = 'Erro ao excluir lançamento.';
                }

                if ($response['deleted']) {
                    $responseObj[] = ["success" => "Cobrança Asaas do título " . $titulo . " excluída com sucesso."];
                }
            }
        } else {
            $responseObj[] = ["success" => "Título(s) " .  implode(', ', (array)$titulos) . " excluído(s) com sucesso."];
        }
    } else {
        $responseObj[] = ["error" => "Erro ao excluir lançamento do(s) titulo(s) " .  implode(', ', (array)$titulos)];
        $msg = 'Erro ao excluir lançamento.';
        $status = 1;
        $erro++;
    }
    echo json_encode($responseObj);

    $idi = implode(",", (array) $id);
    $parametroscsv = $idi;

    salvaLog($id_funcionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "mudaBanco") {
    $query  = "UPDATE alunos_fichafinanceira SET idcontasbanco=$novoBanco WHERE id in ($id)";
    $queryParaAcharOTitulo = "SELECT titulo FROM alunos_fichafinanceira WHERE id =" . $id;
    $resultado = mysql_query($queryParaAcharOTitulo);
    while ($rowTitulo = mysql_fetch_assoc($resultado)) {
        $titulo = $rowTitulo['titulo'];
    }
    if ($result = mysql_query($query)) {
        echo "blue|Lançamento(s) atualizados." . $query;
        $msg = 'Lançamento(s) atualizados. Numero referente ao titulo: ' . $titulo . '';
        $status = 0;
    } else {
        echo "red|Erro ao atualizar lançamento(s)." . $query;
        $msg = 'Erro ao atualizar lançamento(s).';
        $status = 1;
        $erro++;
    }
    $idi = implode(",", $id);
    $parametroscsv = $idi;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    echo $query;
} elseif ($action == "mudaSituacao") {
    $query  = "UPDATE alunos_fichafinanceira
        SET situacao=$novaSituacao,
            idfuncionario=$idfuncionario,
            dataoutrasituacao='$hoje',
            dataexcluido='0000-00-00',
            datarenegociado='0000-00-00'
        WHERE id in ($id)";
    if ($result = mysql_query($query)) {
        echo "blue|Lançamento(s) atualizados." . $query;
        $msg = 'Lançamento(s) atualizados.';
        $status = 0;
    } else {
        echo "red|Erro ao atualizar lançamento(s)." . $query;
        $msg = 'Erro ao atualizar lançamento(s).';
        $status = 1;
        $erro++;
    }
    $idi = implode(",", $id);
    $parametroscsv = $idi;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cadastraSituacao") {
    $query  = "SELECT COUNT(*) as cnt FROM financeiro_situacaotitulos WHERE situacaotitulo='$nomeSituacao'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    $cnt = $row['cnt'];
    if ($cnt == 0) {
        $query  = "INSERT INTO financeiro_situacaotitulos (situacaotitulo) VALUES ('$nomeSituacao');";
        $result = mysql_query($query);
        $idCadastrado = mysql_insert_id();
        $query  = "UPDATE financeiro_situacaotitulos SET situacaonumero=id WHERE id=" . mysql_insert_id();
        $result = mysql_query($query);
        echo $idCadastrado . "|" . $nomeSituacao . "|Situação $nomeSituacao cadastrada com sucesso.";
        $msg = "Situação $nomeSituacao cadastrada com sucesso.";
        $status = 0;
    } else {
        echo "0|$nomeSituacao|Situação $nomeSituacao já existe!";
        $msg = "Situação $nomeSituacao já existe!";
        $status = 1;
    }
    $parametroscsv = $cnt . ',' . $idanoletivo . ',' . $nomeTurno;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "updateSituacao") {
    $query  = "UPDATE financeiro_situacaotitulos SET situacaotitulo='$novovalor' WHERE id=$id";
    if ($result = mysql_query($query)) {
        echo "blue|Situação atualizada com sucesso.";
        $msg = "Situação atualizada para " . $novovalor . " com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar situação.";
        $msg = "Erro ao atualizar situação.";
        $status = 1;
    }

    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "recebeSituacao") {
    $query = "SELECT * FROM financeiro_situacaotitulos WHERE id='$idsituacao' and id < 4 ORDER BY id ASC";
    $result = is_numeric($idsituacao) ? mysql_query($query) : false;

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            echo '<option value="' . $row['id'] . '">' . $row['situacaotitulo'] . '</option>';
    }
} elseif ($action == "apagaSituacao") {
    //verifica se existe algum titulo com a situação cadastrada

    $sql = "select * from alunos_fichafinanceira where situacao = '$id' ";
    $rs = new query($conn, $sql);

    if ($rs->getrow()) {
        echo "red|Existem titulos associados a esta situação financeira. Remova os titulos e depois tente excluir novamente.|0";
        $msg = "Erro ao remover situação.";
        $status = 1;
    } else {
        $query  = "DELETE FROM financeiro_situacaotitulos WHERE id=$id";

        if ($result = mysql_query($query)) {
            echo "blue|Situação removida com sucesso.|1";
            $msg = "Situação removida com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao remover situação.|0";
            $msg = "Erro ao remover situação.";
            $status = 1;
        }

        $parametroscsv = $id;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "chequedevolvido") {
    $sqlIdFuncionario = "SELECT * FROM funcionarios where idpessoa = " . $idpessoalogin;
    $resultIdFuncionario = mysql_query($sqlIdFuncionario);
    $rowIdFuncionario = mysql_fetch_array($resultIdFuncionario, MYSQL_ASSOC);
    $d = explode('/', $data_calendario);
    $dataToEn = $d[2] . '-' . $d[1] . '-' . $d[0];


    $query  = "INSERT INTO cheque_devolvido
                            (id_fichafinanceira,
                            data_devolvido,
                            idfuncionario_devolvido)
                            VALUES
                            (" . $idficha . ",
                            '" . $dataToEn . "',
                            " . $rowIdFuncionario['id'] . ");";

    if ($result = mysql_query($query)) {
        echo "blue|Situação atualizada com sucesso.";
        $msg = "Situação atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar situação.";
        $msg = "Erro ao atualizar situação.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "chequeRegularizado") {
    /* $sqlIdFuncionario = "SELECT * FROM funcionarios where idpessoa = " . $idpessoalogin;
     $resultIdFuncionario = mysql_query($sqlIdFuncionario);
     $rowIdFuncionario = mysql_fetch_array($resultIdFuncionario, MYSQL_ASSOC);*/



    $query  = "UPDATE cheque_devolvido
                SET
                    data_resgate = now()
                WHERE
                    id_fichafinanceira = " . $idficha;

    if ($result = mysql_query($query)) {
        echo "blue|Situação atualizada com sucesso.";
        $msg = "Situação atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar situação.";
        $msg = "Erro ao atualizar situação.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $novovalor;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "caixarapido") {
    $desconto = 0;
    $bolsa = 0;

    //

    $fichasR = '';
    foreach ($financeiro as $key => $titulo) {
        $multa =  str_replace(',', '.', $titulo['multa']);
        $juros =  str_replace(',', '.', $titulo['juros']);
        unset($titulo['multa']);
        unset($titulo['juros']);


        $query = "SELECT
                *,(SELECT sum(valorrecebido) FROM alunos_fichasrecebidas where idalunos_fichafinanceira = af.id) totalrecebido
            FROM
                alunos_fichafinanceira af
            WHERE
                af.titulo = '" . $key . "' -- AND af.situacao = 0";




        $result = mysql_query($query);
        $financeiro = '';
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $avencer = IntervalDays($agora, $row['datavencimento']);
            $bolsa = $row['bolsa'];
            if ($avencer < 0) {
                if ($perdebolsa == "1") {
                    $bolsa = 0;
                }
            }
            $ficha = $row;


            $idficha = $row['id'];
            $valorParcial = $row['valor'] - $desconto - $bolsa;
            $valoresperado = $valorParcial + $multa + $juros;
        }

        $somabolsavalor = ($ficha['valor'] - $bolsa);

        $datarecebimento = $hoje;
        $avencer = IntervalDays($datarecebimento, $datavencimento);

        if (($avencer < 0) && ($perdebolsa == "1")) {
            $bolsa = 0;
        }

        $somatitulo = 0;
        foreach ($titulo as $t) {
            $query = "SELECT * FROM contasbanco where idfuncionario = " . $idfuncionario;

            $result = mysql_query($query);
            $financeiro = '';
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $conta = $row;
            }



            $valorrecebido = str_replace(',', '.', str_replace('.', '', $t['valorrecebido']));

            $somatitulo += $valorrecebido;


            $query = "INSERT INTO alunos_fichasrecebidas
                                  (idalunos_fichafinanceira, idfuncionario, datarecebido, datacompensado, valorrecebido, formarecebido,
                                    idcontasbanco, numeroForma, datavalidadeForma, bancoForma, agenciaForma, contaForma, pracaForma, outroForma)
                            VALUES ($idficha, $idfuncionario, '$hoje', '$hojecompensado', '" . $valorrecebido . "', '" . $t['idformasrecebimento'] . "',
                                    '"  . $conta['id'] . "', '" . $t['nforma']  . "', '" .  $t['dforma'] . "', '" .  $t['rforma']  . "', '" .  $t['aforma']  . "', '" .  $t['cforma']  . "',
                                    '" .  $t['pforma']  . "', '" .  $t['oforma']  . "')";




            if (!($result = mysql_query($query))) {
                $erro++;
            }

            if ($t['idformasrecebimento'] == 2 || $t['idformasrecebimento'] == 1) {
                $query2 = "UPDATE
                          contasbanco
                       SET
                          saldoatual = saldoatual+" . (float) $valorrecebido . "
                       WHERE id = " . (int) $conta['id'];
                mysql_query($query2);
            }

            $indice++;
            $parametroscsv .= "," . $Rformarecebido[$indice] . ',' . $Ridcontasbanco[$indice] . ',' . $Rnumero[$indice] . ',' . $Rdataval . ',' . $Rbanco[$indice] . ',' . $Ragencia[$indice] . ',' . $Rccorrente[$indice] . ',' . $RpracaForma[$indice] . ',' . $Routro[$indice];
        }

        $valorrecebido = $somatitulo;

        if ($valoresperado > $valorrecebido) {
            $desconto = $valoresperado - $valorrecebido;
        } else {
            $desconto = 0;
            $multa =  $multa + ($valorrecebido - $valoresperado) ;
        }

        $query = "UPDATE alunos_fichafinanceira SET
                            situacao=1,
                            idfuncionario=$idfuncionario,
                            datarecebimento='$hoje',
                            desconto='$desconto',
                            bolsa='$bolsa',
                            multa='$multa',
                            juros='$juros',
                            valorrecebido='" . ($valorrecebido) . "'
                  WHERE id=" . $idficha;

        if ($result = mysql_query($query)) {
            app()->evento(MovimentacaoDeTitulo::recebimento((int) $idficha, (int) $idfuncionario));
            $msg = 'Ficha financeira recebida com sucesso.';
            $status = 0;
            $fichasR .= "@" . $key;
        } else {
            $msg = 'Erro ao receber ficha financeira.';
            $status = 1;
            $erro++;
        }


        $parametroscsv = $idalunos_fichafinanceira . ',' . $datarecebimento . ',' . $RECvalorTotalrecebido;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }


    if ($erro == 0) {
        echo "blue|Recebimento efetuado com sucesso.|" . $fichasR;
        $msg = 'Recebimento efetuado com sucesso.';
        $status = 0;
    } else {
        echo "red|Erro($erro) ao efetuar o recebimento.|0";
        $msg = "Erro($erro) ao efetuar o recebimento.";
        $status = 1;
        $erro++;
    }

    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
}
