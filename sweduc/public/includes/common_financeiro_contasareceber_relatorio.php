<?php

$hoje = date("Y-m-d");
$busca_params = '';
$params_main = '';

function getTurma($id)
{
    $q = "SELECT id,turma FROM turmas where id=$id";
    $r = mysql_query($q);
    $row = mysql_fetch_array($r, MYSQL_ASSOC);
    return $row['turma'];
}

function getEventos($id)
{
    $q = "SELECT id,eventofinanceiro FROM alunos_fichaitens where idalunos_fichafinanceira=$id";
    $r = mysql_query($q);
    $evt = '';

    while ($row = mysql_fetch_array($r, MYSQL_ASSOC)) {
        $evt .= $row['eventofinanceiro'] . "<br>";
    }

    if (substr($evt, -4) === "<br>") {
        $evt = substr($evt, 0, strrpos($evt, "<br>"));
    }
    return $evt;
}

function valorItens($idficha, $id)
{
    $valor = null;
    $query = "SELECT
            SUM(alunos_fichaitens.valor) AS valor
        FROM
            alunos_fichaitens
        WHERE
            alunos_fichaitens.idalunos_fichafinanceira = " . $idficha . "
            AND alunos_fichaitens.codigo LIKE '1%'
        AND alunos_fichaitens.id <> " . $id;
    $result2 = mysql_query($query);

    while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
        $valor = $row2['valor'] != '' ? $row2['valor'] : 0;
    }

    return $valor;
}

function getEventoValor($id, $evento) {
    $query = "SELECT valor,descontoboleto FROM alunos_fichaitens WHERE idalunos_fichafinanceira=$id AND eventofinanceiro='$evento'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $values = [
        'valor'=>$row['valor'],
        'desconto'=>$row['descontoboleto']
    ];
    return $values;
}

if ((is_countable($situacao) ? count($situacao) : 0) == 0) {
    $qsitu = "SELECT * FROM financeiro_situacaotitulos ORDER BY id ASC ";
    $rsitu = mysql_query($qsitu);
    $situacao = [];
    $situlist = '';
    while ($rows = mysql_fetch_array($rsitu, MYSQL_ASSOC)) {
        $situlist .= $rows['situacaonumero'] . ',';
    }
    $situacao[] = rtrim($situlist, ',');
}

// PARAMETROS DE BUSCA
if ($idempresaBusca > 0) {
    $querybusca = "SELECT id,razaosocial FROM empresas WHERE id=$idempresaBusca";
    $resultbusca = mysql_query($querybusca);
    $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

    $busca_params .= 'Empresa: ' . $rowbusca['razaosocial'] . ' |';
}

$ordenaPorAluno = (isset($radioordena) && $radioordena == 1) ? ' aff.idaluno, ' : '';

if ($idbanco > 0) {
    // O idbanco pode ser um array
    if (is_array($idbanco)) {
        if (count($idbanco) === 1) {
            $idbanco = explode(',', $idbanco[0]);
        }

        if ($idbanco = array_filter($idbanco, 'is_numeric')) {
            $idbanco = "'" . implode("','", $idbanco) . "'";
        }
    }

    $querybusca = "SELECT id,nomeb FROM contasbanco WHERE id IN ($idbanco)";
    $resultbusca = mysql_query($querybusca);
    $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

    $busca_params .= 'Banco: ' . $rowbusca['nomeb'] . ' |';
}

if ($idunidadealuno > 0) {
    $querybusca = "SELECT id,unidade FROM unidades WHERE id=$idunidadealuno";
    $resultbusca = mysql_query($querybusca);
    $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

    $busca_params .= 'Unidade aluno: ' . $rowbusca['unidade'] . ' |';
}

if ($idaluno > 0) {
    $querybusca = "SELECT a.id,p.nome FROM alunos a INNER JOIN pessoas p ON a.idpessoa=p.id WHERE a.id=$idaluno";
    $resultbusca = mysql_query($querybusca);
    $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

    $busca_params .= 'Aluno: ' . $rowbusca['nome'] . ' |';
}

if ($idunidadeturma > 0) {
    $querybusca = "SELECT id,unidade FROM unidades WHERE id=$idunidadeturma";
    $resultbusca = mysql_query($querybusca);
    $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

    $busca_params .= 'Unidade turma: ' . $rowbusca['unidade'] . ' |';
}

if (count((array) array_filter($idturma)) > 0) {
    $arr_turma = explode(',', $idturma[0]);

    $lturma = '';
    for ($i = 0; $i < count(array_filter($arr_turma)); $i++) {
        $querybusca = "SELECT id,turma FROM turmas WHERE id=" . $arr_turma[$i];
        $resultbusca = mysql_query($querybusca);
        $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

        $lturma .= $rowbusca['turma'] . ',';
    }
    $busca_params .= 'Turma(s): ' . rtrim($lturma, ',') . ' |';
}

if (isset($valor) && $valor != '') {
    if ($valormaiormenor == 0) {
        $bsinal = '=';
    }
    if ($valormaiormenor == 1) {
        $bsinal = '<';
    }
    if ($valormaiormenor == 2) {
        $bsinal = '>';
    }

    $busca_params .= 'Valor ' . $bsinal . ' ' . $valor . ' |';
}

if (isset($documento) && $documento != '') {
    $busca_params .= 'Título: ' . $documento . ' |';
}

if (count((array) array_filter($eventofinanceiro)) > 0) {
    $arr_evto = explode(',', $eventofinanceiro[0]);

    $levto = '';
    $idevento = '';
    for ($i = 0; $i < count(array_filter($arr_evto)); $i++) {
        $querybusca = "SELECT id,eventofinanceiro FROM eventosfinanceiros WHERE id=" . $arr_evto[$i];

        $resultbusca = mysql_query($querybusca);
        $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

        $levto .= $rowbusca['eventofinanceiro'] . ',';
        $idevento .= $rowbusca['id'] . ',';
    }
    $idevento = rtrim($idevento, ',');
    $busca_params .= 'Evto.(s) financeiro(s): ' . rtrim($levto, ',') . ' |';
}

if (isset($periodode) && $periodode != '') {
    $busca_params .= 'Período: ' . $periodode . ' a ' . $periodoate . ' |';
}

if ((is_countable($situacao) ? count($situacao) : 0) > 0) {
    $arr_situacao = explode(',', $situacao[0]);

    $lsit = '';
    for ($i = 0; $i < count($arr_situacao); $i++) {
        // 0:aberto/1:recebido/2:cancelado/3:baixado/4:excluido/5:renegociado/6:recebido retorno
        if ($arr_situacao[$i] == 0) {
            $lsit .= ' aberto,';
        }
        if ($arr_situacao[$i] == 1) {
            $lsit .= ' recebido,';
        }
        if ($arr_situacao[$i] == 2) {
            $lsit .= ' cancelado,';
        }
        if ($arr_situacao[$i] == 3) {
            $lsit .= ' baixado,';
        }
        if ($arr_situacao[$i] == 4) {
            $lsit .= ' excluido,';
        }
        if ($arr_situacao[$i] == 5) {
            $lsit .= ' renegociado,';
        }
        if ($arr_situacao[$i] == 6) {
            $lsit .= ' recebido retorno,';
        }
    }
    $busca_params .= 'Situação: ' . rtrim($lsit, ',') . ' |';
    $params_main .= '<strong>Situação:</strong> ' . rtrim($lsit, ',') . ' ';
}

$buscaporformapagto = '';

if (count((array) array_filter($formapagamento)) > 0) {
    $arr_forma = explode(',', $formapagamento[0]);

    $lfp = '';
    $listaidformapagto = '';

    for ($i = 0; $i < count(array_filter($arr_forma)); $i++) {
        $querybusca = "SELECT id,formapagamento FROM formaspagamentos WHERE id=" . $arr_forma[$i];

        $resultbusca = mysql_query($querybusca);
        $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

        $lfp .= $rowbusca['formapagamento'] . ',';
        $listaidformapagto .= $rowbusca['id'] . ',';
    }
    $busca_params .= 'Forma(s) pagto.: ' . rtrim($lfp, ',') . ' |';
    $params_main .= '<strong>Forma(s) pagto.:</strong> ' . rtrim($lfp, ',') . ' ';
    $listaidformapagto .= rtrim($listaidformapagto, ',');

    $buscaporformapagto .= ' and alunos_fichasrecebidas.formarecebido in (' . $listaidformapagto . ') ';
}

$referente_usuario = '';

if (count((array) array_filter($recebidopor)) > 0) {
    $arr_rec = explode(',', $recebidopor[0]);

    $lrec = '';
    for ($i = 0; $i < count(array_filter($arr_rec)); $i++) {
        $querybusca = "SELECT f.id AS fid, p.nome FROM funcionarios f INNER JOIN pessoas p ON f.idpessoa=p.id WHERE f.id=" . $arr_rec[$i];
        $resultbusca = mysql_query($querybusca);
        $rowbusca = mysql_fetch_array($resultbusca, MYSQL_ASSOC);

        $lrec .= $rowbusca['nome'] . ',';
    }
    $busca_params .= 'Recebido por: ' . rtrim($lrec, ',') . ' |';

    $referente_usuario = rtrim($lrec, ',');
}

// PARAMETROS DE BUSCA
if (trim($tipoRelatorio) == "") {
    $tipoRelatorio = "0";
}

$query = "SELECT * FROM funcionarios WHERE funcionarios.idpessoa=$idpessoalogin";
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$idfuncionario = $row['id'];
$idfuncionariounidade = $row['idunidade'];

$sqlfrom = "";
$sqlselect = "";
$sql = "";

$sqlfromaffInicial = " (select * from alunos_fichafinanceira where 1=1 ";
$sqlfromaffFinal = " ) as aff ";
$sqlfromaff = "";
$sqlwhereaff = "";
$sqlfromalmatInicial = " inner join alunos_matriculas almat on ( (almat.nummatricula=aff.nummatricula) ";
$sqlfromalmatFinal = " ) ";
$sqlfromalmat = " AND (almat.idaluno=a.id) ";
$sqlfromfst = " inner join financeiro_situacaotitulos fst on fst.situacaonumero=aff.situacao ";
$sqlfroma = " inner join alunos a on a.id=aff.idaluno ";
$sqlfromp = " inner join pessoas p on p.id = a.idpessoa ";
$sqlfromafr = "";
$sqlfromafi = "";
$sqlfromemp = "";


if ($idanoletivo > 0) {
    $query1a = "SELECT anoletivo FROM anoletivo WHERE id=" . $idanoletivo;
    $result1a = mysql_query($query1a);
    $row1a = mysql_fetch_array($result1a, MYSQL_ASSOC);
    $sqlfromaff .= " AND ( YEAR(alunos_fichafinanceira.datavencimento)='" . $row1a['anoletivo'] . "' ) ";
}

if ($idempresaBusca > 0) {
    $sqlfromemp .= "  inner join unidades_empresas uniemp on ( almat.idunidade=uniemp.idunidade ) ";
    $sql .= " ( uniemp.idempresa=" . $idempresaBusca . ") AND ";
}

if ($idempresaBusca > 0) {
    /**
     * A variável idbanco é tratada acima
     */
    if ($idbanco === 0 || $idbanco === "'0'") {
        $querycb = "SELECT id, nomeb FROM contasbanco WHERE idempresa='$idempresaBusca' ORDER BY nomeb ASC";
        $resultcb = mysql_query($querycb);
        $idcb = '';
        while ($rowcb = mysql_fetch_array($resultcb, MYSQL_ASSOC)) {
            $idcb .= $rowcb['id'] . ",";
        }

        $idbanco = rtrim($idcb, ',');
    }

    $sqlwhereaff .= " AND ( alunos_fichafinanceira.idcontasbanco IN (" . $idbanco . ") ) ";
}

if ($idunidadealuno > 0) {
    $sqlfromemp .= "  AND ( uniemp.idunidade=" . $idunidadealuno . ") ";
}

if ($tipoRelatorio == "1") {
    $sqlbolsista = " AND aff.bolsa>0 ";
}

${$hformarecebido} = '';

if ($tipoRelatorio == "1") {
    $sqlbolsista = " AND aff.bolsa>0 ";
}

$rdiobusca = explode(',', $radiobusca[0]);

foreach ($rdiobusca as $k) {
    switch ($k) {
        case "aluno":
            $sqlfromaff .= " AND ( alunos_fichafinanceira.idaluno=$idaluno ) ";
            break;

        case "turma":
            $idtt = implode(",", $idturma);
            $sqlfromalmat .= " AND ( almat.turmamatricula IN (" . $idtt . " ) ) ";
            if ($sqlselect == "") {
                $sqlselect = " almat.status, almat.turmamatricula, ";
            }
            break;

        case "valor":
            $valor = str_replace(',', '.', str_replace('.', '', $valor));
            if ($valor == "") {
                $valor = 0;
            }
            if ($valormaiormenor == '0') {
                $sqlfromaff .= " AND ( alunos_fichafinanceira.valor=$valor ) ";
            } else if ($valormaiormenor == '1') {
                $sqlfromaff .= " AND ( alunos_fichafinanceira.valor<$valor ) ";
            } else if ($valormaiormenor == '2') {
                $sqlfromaff .= " AND ( alunos_fichafinanceira.valor>$valor ) ";
            }
            break;
        case "situacao":
            $situ = explode(',', $situacao[0]);
            $recebidosSelecionado = 0;
            $sqldatatipo = " ( ";
            $sqlsituacao = " AND ( ";

            foreach ($situ as $sit) {
                $sqlsituacao .= " aff.situacao = '" . $sit . "' OR ";
                $datatipo = "datavencimento";

                switch ($sit) {
                    case "0": //ABERTO
                        $datatipo = "datavencimento";
                        break;
                    case "1": //RECEBIDOS
                        $datatipo = "datarecebimento";
                        if ($recebidosSelecionado == 0) {
                            $recebidosSelecionado = 1;
                            if (count((array) array_filter($formapagamento)) > 0) {
                                $sqlfromafr = " left join alunos_fichasrecebidas afr on afr.idalunos_fichafinanceira=aff.id ";
                                $sqlselect = (strlen($sqlselect) > 0 ? $sqlselect : '') . 'SUM(afr.valorrecebido) as sum_afr_valorrecebido,';
                            }

                            if (count((array) array_filter($recebidopor)) > 0) {
                                $idfun = implode(",", $recebidopor);
                                $sql .= " aff.idfuncionario IN (0, $idfun) AND";
                            }

                            if (count((array) array_filter($formapagamento)) > 0) {
                                $fpsql = " ( ";
                                $hformarecebido .= ' AND (';
                                $formapagto = explode(',', $formapagamento[0]);

                                foreach ($formapagto as $k) {
                                    $fpsql .= " afr.formarecebido = $k OR";
                                    $hformarecebido .= " formarecebido = $k OR";
                                }

                                $fpsql = substr($fpsql, 0, strlen($fpsql) - 2);
                                $hformarecebido = substr($hformarecebido, 0, strlen($hformarecebido) - 2);
                                $fpsql .= ") AND";
                                $hformarecebido .= ")";
                                $sql .= $fpsql;
                            }
                        }
                        break;
                    case "2": //CANCELADOS
                        $datatipo = "datacancelado";
                        break;
                    case "3": //BAIXADOS
                        $datatipo = "databaixado";
                        break;
                    case "4": //EXCLUÍDOS
                        $datatipo = "dataexcluido";
                        break;
                    case "6": //RECEBIDOS RETORNO
                        $datatipo = "datarecebimento";
                        if ($recebidosSelecionado == 0) {
                            $recebidosSelecionado = 1;
                            if (((is_countable($formapagamento) ? count($formapagamento) : 0) > 0)) {
                                $sqlfromafr = " left join alunos_fichasrecebidas afr on afr.idalunos_fichafinanceira=aff.id ";
                            }
                        }
                        break;
                }

                if ($periodode == "") {
                    $perde = date("Y-m-d");
                } else {
                    $vctde = explode("/", $periodode);
                    $perde = $vctde[2] . "-" . $vctde[1] . "-" . $vctde[0];
                }

                if ($periodoate == "") {
                    $periodoate = $periodode;
                }
                $vctate = explode("/", $periodoate);
                $perate = $vctate[2] . "-" . $vctate[1] . "-" . $vctate[0];
                $sqldatatipo .= " ( aff." . $datatipo . "  BETWEEN '" . $perde . "' AND '" . $perate . "' ) OR ";
            }

            if ($sqldatatipo != " ( ") {
                $sqldatatipo = substr($sqldatatipo, 0, -3) . " ) ";
                $sqlsituacao = substr($sqlsituacao, 0, -3) . " ) ";
                $sql .= $sqldatatipo . $sqlsituacao;
            }
            break;
        case "documento":
            $pos = strpos($documento, ',');
            if ($pos === false) {
                $sqlfromaff .= " AND ( alunos_fichafinanceira.titulo='$documento' ) ";
            } else {
                $sqlfromaff .= " AND ( alunos_fichafinanceira.titulo IN ($documento) ) ";
            }
            $sql = "1=1 ";
            break;
        case "eventofinanceiro":
            $evtfinanceiro = implode("','", $eventofinanceiro);
            $sqlfromafi .= " inner join alunos_fichaitens afi on afi.idalunos_fichafinanceira=aff.id ";
            $sql .= " afi.codigo IN ( SELECT codigo FROM eventosfinanceiros WHERE id IN (" . $evtfinanceiro . ")  ) AND ";
            break;
    }
}

$sqlwhereaff .= (isset($vctoperiodo) && $vctoperiodo == 1) ? " AND alunos_fichafinanceira.datavencimento >='" . $perde . "' AND alunos_fichafinanceira.datavencimento<='" . $perate . "'" : '';
$sqlwhereaff .= (isset($vctoperiodo) && $vctoperiodo == 2) ? " AND alunos_fichafinanceira.datavencimento < '" . $perde . "'" : '';
$sqlwhereaff .= (isset($vctoperiodo) && $vctoperiodo == 3) ? " AND alunos_fichafinanceira.datavencimento > '" . $perate . "'" : '';

$sqlfromaff = $sqlfromaffInicial . $sqlfromaff . $sqlwhereaff . $sqlfromaffFinal;

$sqlfromalmatSEMBOLSA = $sqlfromalmatInicial . $sqlfromalmat . $sqlfromalmatFinal;
$sqlfromalmat = $sqlfromalmatInicial . $sqlbolsista . $sqlfromalmat . $sqlfromalmatFinal;

$sqlfromSEMBOLSA = $sqlfrom . $sqlfromaff . $sqlfroma . $sqlfromalmatSEMBOLSA . $sqlfromemp . $sqlfromfst . $sqlfromp . $sqlfromafr . $sqlfromafi;
$sqlfrom = $sqlfrom . $sqlfromaff . $sqlfroma . $sqlfromalmat . $sqlfromemp . $sqlfromfst . $sqlfromp . $sqlfromafr . $sqlfromafi;

$sql = str_ireplace('ANDAND', 'AND', $sql, $count);

if ($idempresaBusca > 0) {
    $q_empresa = "SELECT razaosocial,nomefantasia FROM empresas WHERE id=" . $idempresaBusca;
    $r_empresa = mysql_query($q_empresa);
    $row_empresa = mysql_fetch_array($r_empresa, MYSQL_ASSOC);
    $nome_empresa = $row_empresa['razaosocial'];
    $nome_fantasia = $row_empresa['nomefantasia'];
} else {
    $nome_empresa = '(TODAS)';

    $q_empresa = "SELECT razaosocial,nomefantasia FROM empresas LIMIT 1";
    $r_empresa = mysql_query($q_empresa);
    $row_empresa = mysql_fetch_array($r_empresa, MYSQL_ASSOC);
    $nome_fantasia = $row_empresa['nomefantasia'];
}

if ($idunidadealuno > 0 || $idunidadeturma > 0) {
    $idunidadeselec = ($idunidadealuno > 0) ? $idunidadealuno : $idunidadeturma;

    $q_unidade = "SELECT unidade FROM unidades WHERE id=" . $idunidadeselec;
    $r_unidade = mysql_query($q_unidade);
    $row_unidade = mysql_fetch_array($r_unidade, MYSQL_ASSOC);
    $nome_unidade = $row_unidade['unidade'];
} else {
    $nome_unidade = '(TODAS)';
}

$q_func = "SELECT nome FROM pessoas WHERE id=" . $idpessoalogin;
$r_func = mysql_query($q_func);
$row_func = mysql_fetch_array($r_func, MYSQL_ASSOC);
$nome_func = $row_func['nome'];
