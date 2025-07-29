<?php

declare(strict_types=1);

namespace App\Financeiro\Controller;

use App\Framework\Http\BaseController;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use App\Model\Financeiro\Conta;
use App\Asaas\Models\Asaas;
use App\Model\Core\Configuracao;

class NovoLancamentoController extends BaseController
{
    private function proxDiaUtil(string $date): string
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);

        $feriadosDoDB = DB::select("SELECT data_do_feriado FROM feriados");
        $feriados = array_map(function ($feriado) {
            return $feriado->data_do_feriado;
        }, $feriadosDoDB);

        do {
            $dayOfWeek = $date->dayOfWeek;
            $isWeekend = ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY);
            $isHoliday = in_array($date->format('Y-m-d'), $feriados);

            if (!$isWeekend && !$isHoliday) {
                return $date->format('Y-m-d');
            }

            $date->addDay();
        } while ($isWeekend || $isHoliday);

        return $date->format('Y-m-d');
    }

    public function lancamento(ServerRequestInterface $request)
    {
        $idalunos_fichafinanceira = null;
        $msg_unifica = null;
        $action = 'lancamento';
        $input = $request->getParsedBody();

        $data1parcela = $input['data1parcela'];
        $eventosMatricula = $input['eventosMatricula'];
        $valorparcelas = $input['valorparcelas'];
        $valorfinaldasparcelas = $input['valorfinaldasparcelas'];
        $descontoparcelas = $input['descontoparcelas'];
        $valorbrutoparcelas = $input['valorbrutoparcelas'];
        $idunidade = $input['idunidade'];
        $idcontasbanco = $input['idcontasbanco'];
        $unifica = $input['unifica'];
        $parcelainicial = $input['parcelainicial'];
        $idaluno = $input['idaluno'];
        $nummatricula = $input['nummatricula'];
        $qtdeparcelas = $input['qtdeparcelas'];
        $idfuncionario = $_SESSION['id_funcionario'];
        $matricula = $input['matricula'];
        $hoje = date("Y-m-d");

        $contaModel = Conta::find($idcontasbanco);
        $usaApi = $contaModel->usabancoAPI;
        $pulaFeriados = Configuracao::completa()['vencimento_pula_feriados'];

        $descontoboleto = $input['descontoboleto'] ?? '0';

        $descontoparcelaspercentual = (isset($input['descontoparcelaspercentual']))
            ? str_replace(',', '.', $input['descontoparcelaspercentual'])
            : '0.00';

        $pagamentoCartaoOnline = isset($_REQUEST['pagamento-cartao-online']) ? 1 : 0;
        $dtdata1parcela = explode("/", $data1parcela);
        $data1parcela = $dtdata1parcela[2] . "-" . $dtdata1parcela[1] . "-" . $dtdata1parcela[0];

        $evtfin = explode("@", $eventosMatricula);
        $codigoeventofinanceiro = $evtfin[0];
        $eventofinanceiro = $evtfin[1];

        $valorparcelas = str_replace(",", ".", str_replace(".", "", $valorparcelas));
        $valorfinaldasparcelas = str_replace(",", ".", str_replace(".", "", $valorfinaldasparcelas));
        $descontoparcelas = str_replace(",", ".", str_replace(".", "", $descontoparcelas));
        $valorbrutoparcelas = str_replace(",", ".", str_replace(".", "", $valorbrutoparcelas));

        if ($idunidade > 0) {
            $queryA = "SELECT titulofichafinanceira, empresas.id as eid FROM empresas, contasbanco WHERE contasbanco.idempresa=empresas.id AND contasbanco.id=" . $idcontasbanco;
            $resultA = mysql_query($queryA);
            $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
            $titulofichafinanceira = $rowA['titulofichafinanceira'];
            $idempresa = $rowA['eid'];

            $queryTituloMax = "SELECT
                    MAX(CAST(titulo as UNSIGNED)) as titulomax
                FROM alunos_fichafinanceira
                JOIN contasbanco ON contasbanco.id = alunos_fichafinanceira.idcontasbanco
                JOIN empresas ON empresas.id = contasbanco.idempresa
                WHERE contasbanco.id = '$idcontasbanco'";

            $resultTituloMax = mysql_query($queryTituloMax);
            $rowTituloMax = mysql_fetch_array($resultTituloMax, MYSQL_ASSOC);
            $titulomax = $rowTituloMax['titulomax'];

            $query1 = "UPDATE empresas SET titulofichafinanceira='" . ($titulofichafinanceira + $qtdeparcelas) . "' WHERE id=" . $idempresa;
            $result1 = mysql_query($query1);
            if ($data1parcela == "--") {
                $data1parcela = date("Y-m-d");
            }
            $datavencimento = $data1parcela;

            $d = new DateTime($datavencimento);
            $diainicial = $d->format('d');
            $diaanterior = 0;
            $allok = 0;
            $teveerro = 0;
            $erro_unifica = 0;
            $msg_unifica = '';
            if ($unifica == "sim") { // UNIFICAÇÃO DE TITULOS
                for ($poi = $parcelainicial; $poi <= $qtdeparcelas; $poi++) {
                    $mvencimento = explode("-", $datavencimento);
                    $mesvencimento = $mvencimento[1];
                    $queryA1 = "SELECT id, datavencimento FROM alunos_fichafinanceira WHERE situacao=0 AND datavencimento='" . $datavencimento . "' AND nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno . " AND remessaenviado=1 ORDER BY valor DESC LIMIT 1 ";
                    $resultA1 = mysql_query($queryA1);
                    $rowA1 = mysql_fetch_array($resultA1, MYSQL_ASSOC);
                    $queryA2 = "SELECT idcontasbanco FROM alunos_fichafinanceira WHERE situacao=0 AND datavencimento='" . $datavencimento . "' AND nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno . " ORDER BY valor DESC LIMIT 1 ";
                    $resultA2 = mysql_query($queryA2);
                    $rowA2 = mysql_fetch_array($resultA2, MYSQL_ASSOC);
                    $idContasBancoTituloAnterior = $rowA2['idcontasbanco'];
                    if ($rowA1 && $rowA1['id'] != 0) {
                        $teveerro = 1;
                        $erro_unifica = 1;
                        $status = 1;
                        $msg_unifica .= 'Não é possível unificar a um título que já foi enviado em remessa.';
                    } elseif ($idContasBancoTituloAnterior != $idcontasbanco) {
                        $teveerro = 1;
                        $erro_unifica = 1;
                        $status = 1;
                        $msg_unifica .= 'Não é possível unificar a um título de um Conta diferente.';
                    } else {
                        $queryA = "SELECT id, datavencimento FROM alunos_fichafinanceira WHERE situacao=0 AND datavencimento='" . $datavencimento . "' AND nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno . " AND remessaenviado=0 ORDER BY valor DESC LIMIT 1 ";
                        $resultA = mysql_query($queryA);
                        $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
                        $idalunos_fichafinanceira = $rowA['id'];
                        if (!empty($idalunos_fichafinanceira)) {
                            $nome_do_aluno = DB::table('alunos')
                                ->join('pessoas', 'alunos.idpessoa', '=', 'pessoas.id')
                                ->where('alunos.id', $idaluno)
                                ->value('pessoas.nome');

                            $query = "UPDATE alunos_fichafinanceira SET valor = valor+" . $valorparcelas . " WHERE id=" . $idalunos_fichafinanceira;

                            $insertSuccess = 0;
                            if ($result = mysql_query($query)) {
                                $msg = "Valor da ficha financeira do aluno " . $nome_do_aluno . " atualizada com sucesso. ( Unificado ).
                                Numero do titulo " . $titulofichafinanceira;
                                $status = 0;
                                $allok = $allok + 1;
                                $insertSuccess++;
                            } else {
                                $status = 1;
                                $msg = "Erro ao atualizar valor da ficha financeira. ( Unificado ).";
                                $teveerro = $teveerro + 1;
                            }
                            $parametroscsv = $idalunos_fichafinanceira . "," . $valorparcelas;
                            salvaLog(
                                $idfuncionario,
                                basename(__FILE__),
                                $action,
                                $status,
                                $parametroscsv,
                                $msg
                            );
                            $query1 = "UPDATE empresas SET titulofichafinanceira='" . ($titulofichafinanceira + $qtdeparcelas) . "' WHERE id=" . $idempresa;
                            $result1 = mysql_query($query1);
                            $query = "INSERT INTO alunos_fichaitens (idalunos_fichafinanceira, parcela, totalparcelas, codigo, eventofinanceiro, valor, descontoboleto) VALUES ( $idalunos_fichafinanceira, '$poi', $qtdeparcelas, $codigoeventofinanceiro, '$eventofinanceiro', $valorparcelas, $descontoboleto);";
                            if ($result = mysql_query($query)) {
                                $allok = $allok + 1;
                                $insertSuccess++;
                            } else {
                                $teveerro = $teveerro + 1;
                            }
                        }
                        if ($d->format('d') > 30) {
                            $diaanterior = $d->format('d');
                            $d->modify('last day of next month');
                        } elseif ($d->format('d') > 28) {
                            $diaanterior = $d->format('d');
                            $d->modify('last day of next month');
                        } else {
                            $d->modify('+1 month ');
                        }
                        if (($diaanterior > 0) && ($d->format('m') == 3)) {
                            $d->setDate(
                                (int) $d->format('Y'),
                                (int) $d->format('m'),
                                $diaanterior
                            );
                            $diaanterior = 0;
                        }
                        if ($d->format('d') > $diainicial) {
                            $d->modify($d->format('Y') . '-' . $d->format('m') . '-' . $diainicial);
                        }
                        $datavencimento = $d->format('Y-m-d');

                        $retornoMsg = "Ocorreu um erro ao realizar o lançamento unificado. Por favor, entre em contato.";
                        if ($insertSuccess == 2) {
                            $retornoMsg = "Titulo unificado lançado com sucesso.";

                            if ($usaApi && $contaModel->banconum == 461) {
                                try {
                                    $asaas = new Asaas();
                                    $asaas->alterarCobranca($idalunos_fichafinanceira);
                                } catch (\Exception $e) {
                                    $retornoMsg = json_decode($e->getMessage())->errors[0]->description;
                                    $teveerro++;

                                    // desfaz a unificação se a alteração na API falhar
                                    $query = "UPDATE alunos_fichafinanceira SET valor = valor-" . $valorparcelas . " WHERE id=" . $idalunos_fichafinanceira;
                                    $result = mysql_query($query);
                                    $query = "DELETE FROM alunos_fichaitens WHERE idalunos_fichafinanceira = $idalunos_fichafinanceira AND parcela = '$poi'";
                                    $result = mysql_query($query);
                                    $query1 = "UPDATE empresas SET titulofichafinanceira='" . ($titulofichafinanceira) . "' WHERE id=" . $idempresa;
                                    $result1 = mysql_query($query1);
                                }
                            }
                        }
                    }
                }
            } else {  // NÃO UNIFICA
                for ($poi = $parcelainicial; $poi <= $qtdeparcelas; $poi++) {
                    if ($pulaFeriados) {
                        $datavencimento = $this->proxDiaUtil($datavencimento); // Função que pega o próximo dia útil, caso a data de vencimento não seja um dia útil
                    }

                    if ($matricula == "sim") {
                        $query = "INSERT INTO alunos_fichafinanceira(idfuncionario, idaluno, nummatricula, titulo, data1parcela, dataemissao, datavencimento, valor, bolsa, bolsapercentual, desconto, multa, juros, datarecebimento, idcontasbanco, valorrecebido, situacao, numeroloterps, naturezaoperacao, issretido, matricula, pagamento_cartao_online) VALUES ($idfuncionario,$idaluno, $nummatricula, $titulofichafinanceira, '$data1parcela', '$hoje', '$datavencimento', $valorbrutoparcelas, $descontoparcelas, $descontoparcelaspercentual,0,0,0,'0000-00-00',$idcontasbanco,0, 0, 0, 1, 0, 1, '$pagamentoCartaoOnline');";
                    } else {
                        $query = "INSERT INTO alunos_fichafinanceira(idfuncionario, idaluno, nummatricula, titulo, data1parcela, dataemissao, datavencimento, valor, bolsa, desconto, multa, juros, datarecebimento, idcontasbanco, valorrecebido, situacao, numeroloterps, naturezaoperacao, issretido, matricula, pagamento_cartao_online) VALUES ($idfuncionario,$idaluno, $nummatricula, $titulofichafinanceira, '$data1parcela', '$hoje', '$datavencimento', $valorparcelas, 0,0,0,0,'0000-00-00',$idcontasbanco,0, 0, 0, 1, 0, 0, '$pagamentoCartaoOnline');";
                    }

                    $nome_do_aluno = DB::table('alunos')
                        ->join('pessoas', 'alunos.idpessoa', '=', 'pessoas.id')
                        ->where('alunos.id', $idaluno)
                        ->value('pessoas.nome');

                    $insertSuccess = 0;
                    if ($result = mysql_query($query)) {
                        $msg = "Ficha financeira do aluno " . $nome_do_aluno . " inserida com sucesso. ( Não Unificado ).
                        numero do titulo " . $titulofichafinanceira;
                        $status = 0;
                        $allok = $allok + 1;
                        $idalunos_fichafinanceira = mysql_insert_id();

                        $insertSuccess++;
                    } else {
                        $status = 1;
                        $msg = "Erro ao inserir ficha financeira. ( Não Unificado ).";
                        $teveerro = $teveerro + 1;
                    }
                    $parametroscsv = $idalunos_fichafinanceira . "," . $valorparcelas;
                    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
                    if ($matricula == "sim") {
                        $query = "INSERT INTO alunos_fichaitens (idalunos_fichafinanceira, parcela, totalparcelas, codigo, eventofinanceiro, valor, descontoboleto) VALUES ( $idalunos_fichafinanceira, '$poi', $qtdeparcelas, $codigoeventofinanceiro, '$eventofinanceiro', $valorbrutoparcelas, $descontoboleto);";
                    } else {
                        $query = "INSERT INTO alunos_fichaitens (idalunos_fichafinanceira, parcela, totalparcelas, codigo, eventofinanceiro, valor, descontoboleto) VALUES ( $idalunos_fichafinanceira, '$poi', $qtdeparcelas, $codigoeventofinanceiro, '$eventofinanceiro', $valorparcelas, $descontoboleto);";
                    }
                    if ($result = mysql_query($query)) {
                        $allok = $allok + 1;
                        $insertSuccess++;
                    } else {
                        $teveerro = $teveerro + 1;
                    }
                    $titulofichafinanceira++;
                    if ($d->format('d') > 30) {
                        $diaanterior = $d->format('d');
                        $d->modify('last day of next month');
                    } elseif ($d->format('d') > 28) {
                        $diaanterior = $d->format('d');
                        $d->modify('last day of next month');
                    } else {
                        $d->modify('+1 month ');
                    }
                    if (($diaanterior > 0) && ($d->format('m') == 3)) {
                        $d->setDate(
                            (int) $d->format('Y'),
                            (int) $d->format('m'),
                            $diaanterior
                        );
                        $diaanterior = 0;
                    }
                    if ($d->format('d') > $diainicial) {
                        $d->modify($d->format('Y') . '-' . $d->format('m') . '-' . $diainicial);
                    }
                    $datavencimento = $d->format('Y-m-d');

                    $retornoMsg = "Ocorreu um erro ao realizar o lançamento. Por favor, entre em contato.";
                    if ($insertSuccess == 2) {
                        $retornoMsg = "Titulo lançado com sucesso.";

                        if ($usaApi && $contaModel->banconum == 461) {
                            try {
                                $asaas = new Asaas();
                                $response = $asaas->criarCobranca($idalunos_fichafinanceira);
                            } catch (\Exception $e) {
                                $msg = $e->getMessage();
                                if ($msg == 'No query results for model [App\Model\Core\Pessoa].') {
                                    $retornoMsg = "Erro: Resposável financeiro não encontrado. Verifique se o aluno possui um responsável financeiro cadastrado.";
                                } else {
                                    $retornoMsg = json_decode($msg)->errors[0]->description;
                                    if (empty($retornoMsg)) {
                                        $retornoMsg = $msg;
                                    }
                                }
                                $teveerro++;
                                $titulofichafinanceira--;

                                // desfaz a criação do título se a cobrança da Asaas falhar
                                $query = "DELETE FROM alunos_fichafinanceira WHERE id = $idalunos_fichafinanceira";
                                $result = mysql_query($query);
                                $query = "DELETE FROM alunos_fichaitens WHERE idalunos_fichafinanceira = $idalunos_fichafinanceira";
                                $result = mysql_query($query);
                            }

                            if ($response['id']) {
                                $retornoMsg = "Cobrança Asaas gerada com sucesso!";
                            }
                        }
                    }
                }
            }

            if ($titulomax >= $titulofichafinanceira) {
                $status = 1;
                $msg = "Erro ao realizar novo lançamento.";
                return $this->jsonResponse(["error" => "Ocorreu um erro ao realizar o lançamento. Por favor, entre em contato."]);
            }

            $parametroscsv = $teveerro;
        } else {
            $teveerro = 1;
        }

        if ($teveerro == 0) {
            $status = 0;
            $msg = "Lançamento do titulo " . $titulofichafinanceira . " realizado com sucesso.";

            return $this->jsonResponse(["success" => $retornoMsg]);
        } else {
            $status = 1;
            $msg = "Erro ao realizar novo lançamento. " . $msg_unifica;
            return $this->jsonResponse(["error" => $retornoMsg]);
        }

        $parametroscsv = $teveerro;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
}
