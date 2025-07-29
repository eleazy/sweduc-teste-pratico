<?php

use App\Academico\Model\Aluno;
use App\Academico\AlunoService;
use App\Academico\ResponsavelService;
use App\Financeiro\Controller\NovoLancamentoController;
use Carbon\Carbon;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Illuminate\Database\Capsule\Manager as DB2;
use App\Asaas\Models\Asaas;
use App\Model\Financeiro\Conta;
use App\Model\Financeiro\Titulo;
use App\Model\Core\Configuracao;

include('../headers.php');
include('conectar.php');
include_once('logs.php');
include('../function/ultilidades.func.php');

require_once('trocasenha.php');
require_once("mysql.class.php");
require_once($_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php');

$conn = new db();
$conn->open();

$query = "SELECT alunos FROM permissoes WHERE id= " . $idpermissoes;
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$permissoes_alunos = $row['alunos'];

$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}

function proxDiaUtil(string $date): string
{
    $date = Carbon::createFromFormat('Y-m-d', $date);

    $feriadosDoDB = DB2::select("SELECT data_do_feriado FROM feriados");
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
// detalhes do desconto fornecido
function infoDesconto($id)
{
    $stmt = "SELECT descricao FROM financeiro_tabeladescontos where id=" . $id;

    $result = mysql_query($stmt);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    return $row['descricao'];
}

// tabeladesconto
if (isset($tabeladesconto) && $tabeladesconto != 0) {
    $v = explode("_", $tabeladesconto);
    $bolsa_motivo_id = $v[0];
    $bolsa_motivo = infoDesconto($v[0]);
} else {
    $bolsa_motivo_id = '';
    $bolsa_motivo = '';
}

$debug = 0;
$agora = date("Y-m-d h:i");
$hoje = date("Y-m-d");
$msg = "";

$query1 = "SELECT id FROM funcionarios WHERE idpessoa=" . $idpessoalogin;
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
$idfuncionario = $_SESSION['id_funcionario'];

$descontoparcelaspercentual = (isset($descontoparcelaspercentual)) ? str_replace(',', '.', $descontoparcelaspercentual) : '0.00';

$seguroescolar = (isset($_REQUEST['nova-matricula-seguroescolar'])) ? $seguroescolar1 : '0';

$descontoboleto ??= '0';

if ($action == "setEmitenf") {
    $query = "UPDATE alunos SET nfe=$valor WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        echo "green|Atualizado com sucesso";
        $msg = "Situação de emissão de NFe atualizada com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao atualizar o aluno";
        $msg = "Erro ao atualizar a situação de emissão de NFe aluno.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $valor;
    salvaLog($idfuncionario, __FILE__, $action, $status, $parametroscsv, $msg);
} elseif ($action == "salvanovotel") {
    if (trim($novotel) <> "") {
        $query = "INSERT INTO telefones (idpessoa, idtipotel, telefone) VALUES ($idpessoalogin,1,'$novotel')";
        if ($result = mysql_query($query)) {
            echo mysql_insert_id();
        } else {
            echo "0";
        }
    } else {
        echo "0";
    }
} elseif ($action == "salvanovotelresp") {
    if (trim($novotel) <> "") {
        $query = "INSERT INTO telefones (idpessoa, idtipotel, telefone) VALUES ($idpr,1,'$novotel')";
        if ($result = mysql_query($query)) {
            echo mysql_insert_id();
        } else {
            echo "0";
        }
    } else {
        echo "0";
    }
} elseif ($action == "apagatel") {
    $query = "DELETE FROM telefones WHERE id=" . $idtel;
    if ($result = mysql_query($query)) {
        echo "1";
    } else {
        echo "0";
    }
} elseif ($action == "apagaemail") {
    $query = "DELETE FROM emails WHERE id=" . $idemail;
    if ($result = mysql_query($query)) {
        echo "1";
    } else {
        echo "0";
    }
} elseif ($action == "salvanovoemail") {
    if (trim($novoemail) <> "") {
        $query = "INSERT INTO emails (idpessoa, email) VALUES ($idpessoalogin,'$novoemail')";
        if ($result = mysql_query($query)) {
            echo mysql_insert_id();
        } else {
            echo "0";
        }
    } else {
        echo "0";
    }
} elseif ($action == "salvanovoemailresp") {
    if (trim($novoemail) <> "") {
        $query = "INSERT INTO emails (idpessoa, email) VALUES ($idpr,'$novoemail')";
        if ($result = mysql_query($query)) {
            echo mysql_insert_id();
        } else {
            echo "0";
        }
    } else {
        echo "0";
    }
    //echo "|".$novoemail."|".$query;
} elseif ($action == "atualizaEndereco") {
    $result = AlunoService::atualizaEndereco($idpessoa, $_REQUEST);

    if ($result) {
        echo "1";
    } else {
        echo "Erro ao salvar";
    }

    //HCN
} elseif ($action == "mudaRespFin") {
    // limpa os respfin, se houver
    $querya = "SELECT responsaveis.id, responsaveis.idaluno FROM responsaveis WHERE idaluno=" . $aluno_id;
    $resulta = mysql_query($querya);
    while ($rowa = mysql_fetch_array($resulta, MYSQL_ASSOC)) {
        $upd_resp = "UPDATE responsaveis SET respfin=0 WHERE id=" . $rowa['id'] . " AND idaluno=" . $aluno_id;
        $exec = mysql_query($upd_resp);
    }

    // atualiza com o novo respfin
    // $query1 = "UPDATE responsaveis SET respfin=0 WHERE idaluno=".$alunoid;
    $query2 = "UPDATE responsaveis SET respfin=1 WHERE id=" . $row_id . " AND idaluno=" . $aluno_id;
    // $result1 = mysql_query($query1);
    if ($result2 = mysql_query($query2)) {
        echo "1";
    } else {
        echo $query;
    }
} elseif ($action == "buscaPessoa") {
    $query = "SELECT * FROM pessoas WHERE id =" . $pessoaid;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo json_encode($row, JSON_THROW_ON_ERROR);
} elseif ($action == "rfin_atualizaEndereco") {
    $query = "UPDATE pessoas SET logradouro='" . $cad_logradouro . "', numero='" . $cad_numero . "', bairro='" . $cad_bairro . "', cep='" . $cad_cep . "', complemento='" . $cad_complemento . "',idestado='" . $idestado . "', idcidade='" . $idcidade . "', rg='" . $cad_rg . "', orgaoexp='" . $cad_orgaoexp . "', cpf='" . $cad_cpf . "', profissao='" . $cad_profissao . "' WHERE id=" . $respfin;
    if ($result = mysql_query($query)) {
        echo "1";
    } else {
        echo $query;
    }

    // /HCN
} elseif ($action == "getEmails") {
    unset($emails);
    if ($alunoMail > 0) {
        $query = "SELECT email FROM emails WHERE idpessoa IN (" . $idpessoa . ") AND email<>'' AND email<>'0' GROUP BY email";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $emails[] = $row['email'];
        }
    }

    $query = "SELECT email FROM emails, responsaveis WHERE responsaveis.idaluno IN ( $idaluno ) AND emails.idpessoa=responsaveis.idpessoa AND ( responsaveis.idparentesco=$paiMail OR responsaveis.idparentesco=$maeMail ";

    if ($respfinMail > 0) {
        $query .= " OR responsaveis.respfin=1 ";
    }

    if ($resppedagMail > 0) {
        $query .= " OR responsaveis.resppedag=1 ";
    }

    $query .= "  ) AND email<>'' AND email<>'0'  GROUP BY email";

    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $emails[] = $row['email'];
    }

    echo implode(",", $emails);
} elseif ($action == "buscaNome") {
    $query = "SELECT COUNT(*) as cnt FROM alunos, pessoas WHERE alunos.idpessoa=pessoas.id AND pessoas.nome='" . $nome . "'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo $row['cnt'];
} elseif ($action == "buscaCPF") {
    $query = "SELECT COUNT(*) as cnt FROM alunos, pessoas WHERE alunos.idpessoa=pessoas.id AND pessoas.cpf='" . $cpf . "'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    echo $row['cnt'];
} elseif ($action == "buscaCPFResp") {
    if ($cpf == '000.000.000-00') {
        echo "0";
    } else {
        $query = "SELECT COUNT(*) as cnt FROM responsaveis, pessoas WHERE responsaveis.idpessoa=pessoas.id AND pessoas.cpf='" . $cpf . "'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        echo $row['cnt'];
    }
} elseif ($action == "desfazMatricula") {
    $query = "SELECT COUNT(*) as cnt FROM alunos_matriculas WHERE idaluno=" . $idaluno;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    if ($row['cnt'] > 1) {
        $query = "DELETE FROM alunos_matriculas WHERE nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno;

        if ($result = mysql_query($query)) {
            echo "green|Matrícula " . $nummatricula . " apagada com sucesso.";
            $msg = "Matrícula " . $nummatricula . " apagada com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao apagar a matrícula";
            $msg = "Erro ao apagar a matrícula " . $nummatricula;
            $status = 1;
        }

        $parametroscsv = $nummatricula . ',' . $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else {
        $query = "UPDATE alunos_matriculas SET turmamatricula=-1, anoletivomatricula=0, datamatricula='0000-00-00', status=0, datastatus='" . $hoje . "', nummatricula=0, qtdparcelas=0, valorAnuidade=0, bolsa=0 WHERE nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno;

        if ($result = mysql_query($query)) {
            echo "green|Matrícula " . $nummatricula . " desfeita com sucesso.";
            $msg = "Matrícula " . $nummatricula . " desfeita com sucesso.";
            $status = 0;
        } else {
            echo "red|Erro ao desfazer a matrícula";
            $msg = "Erro ao desfazer a matrícula " . $nummatricula;
            $status = 1;
        }

        $parametroscsv = $nummatricula . ',' . $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM alunos_documentos WHERE idaluno=" . $idaluno;
        $queryPegarAluno = "SELECT nome FROM pessoas JOIN alunos ON pessoas.id =" . $idaluno . ";";
        while ($resultado = mysql_query($queryPegarAluno)) {
            $nomeAluno = $resultado['nome'];
        }

        if ($result = mysql_query($query)) {
            $msg = "Documentos do aluno " . $nomeAluno . " removidos com sucesso.";
            $status = 0;
        } else {
            $msg = "Erro ao remover documentos do aluno";
            $status = 1;
        }

        $parametroscsv = $nummatricula . ',' . $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }

    $query = "UPDATE alunos_fichafinanceira SET situacao=4, dataexcluido='$hoje' WHERE situacao<>1 AND situacao<>6 AND nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno;

    if ($result = mysql_query($query)) {
        $msg = "Fichas financeiras do aluno removidas com sucesso.";
        $status = 0;
    } else {
        $msg = "Erro ao remover fichas financeiras do aluno";
        $status = 1;
    }

    $parametroscsv = $nummatricula . ',' . $idaluno;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "reabreMatricula") {
    $qdata = "SELECT IF(date_format(datastatus,'%Y-%m-%d')=datamatricula,1,0) as datechanged FROM alunos_matriculas WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    $rdata = mysql_query($qdata);
    $rd = mysql_fetch_array($rdata, MYSQL_ASSOC);

    $updtdata = ($rd['datechanged'] == 1) ? " datastatus='$hoje', " : "";

    $query = "UPDATE alunos_matriculas SET
                status=1,
                " . $updtdata . "
                escoladestino = '',
                obsSituacao = '" . $obsSituacao . "',
                motivoSituacao = ''
                WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;



    if ($result = mysql_query($query)) {
        echo "green|Matrícula reaberta com sucesso";
        $msg = "Matrícula reaberta com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao reabrir matrícula do aluno";
        $msg = "Erro ao reabrir matrícula do aluno";
        $status = 1;
    }

    $parametroscsv = $nummatricula;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "mudaSituacao") {
    $alunoId = $_REQUEST['idaluno'];
    $matriculaId = $_REQUEST['matriculaId'];
    $escolaDestino = $_REQUEST['escoladestino'];
    $situacao = $_REQUEST['situacao'];
    $obsSituacao = $_REQUEST['obsSituacao'];
    $motivoSituacao = $_REQUEST['motivoSituacao'];

    try {
        $matricula = Aluno::findOrFail($alunoId)
            ->matriculas()
            ->withoutGlobalScopes()
            ->findOrFail($matriculaId);

        $matricula->status = $situacao;
        $matricula->datastatus = Carbon::now();
        $matricula->escoladestino = $escolaDestino;
        $matricula->obsSituacao = $obsSituacao;
        $matricula->motivoSituacao = $motivoSituacao;
        $matricula->saveOrFail();

        $msg = "Situação $situacao da matrícula atualizada com sucesso";
        echo "green|$msg";
        $status = 0;
    } catch (\Throwable $th) {
        $msg = "Erro ao atualizar situação $situacao da matrícula do aluno";
        echo "red|$msg";
        $status = 1;
    }

    $parametroscsv = $nummatricula;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "trancaMatricula") {
    $qdata = "SELECT IF(date_format(datastatus,'%Y-%m-%d')=datamatricula,1,0) as datechanged FROM alunos_matriculas WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    $rdata = mysql_query($qdata);
    $rd = mysql_fetch_array($rdata, MYSQL_ASSOC);

    $updtdata = ($rd['datechanged'] == 1) ? " datastatus='$hoje', " : "";

    $query = "UPDATE alunos_matriculas SET
                status=2,
                " . $updtdata . "
                escoladestino = '" . $escoladestino . "',
                obsSituacao = '" . $obsSituacao . "',
                motivoSituacao = " . $motivoSituacao . "
                WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;



    if ($result = mysql_query($query)) {
        echo "green|Matrícula trancada com sucesso";
        $msg = "Matrícula trancada com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao trancar matrícula do aluno";
        $msg = "Erro ao trancar matrícula do aluno";
        $status = 1;
    }

    $parametroscsv = $nummatricula;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "desistenciaMatricula") {
    $qdata = "SELECT IF(date_format(datastatus,'%Y-%m-%d')=datamatricula,1,0) as datechanged FROM alunos_matriculas WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    $rdata = mysql_query($qdata);
    $rd = mysql_fetch_array($rdata, MYSQL_ASSOC);

    $updtdata = ($rd['datechanged'] == 1) ? " datastatus='$hoje', " : "";

    $query = "UPDATE alunos_matriculas SET
                status=5,
                " . $updtdata . "
                escoladestino = '" . $escoladestino . "',
                obsSituacao = '" . $obsSituacao . "',
                motivoSituacao = " . $motivoSituacao . "
                WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;

    if ($result = mysql_query($query)) {
        echo "green|Matrícula marcada como DESISTÊNCIA com sucesso";
        $msg = "Matrícula marcada como DESISTÊNCIA com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao marcar como DESISTÊNCIA a matrícula do aluno";
        $msg = "Erro ao marcar como DESISTÊNCIA a matrícula do aluno";
        $status = 1;
    }

    $parametroscsv = $nummatricula;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "transferenciaMatricula") {
    $qdata = "SELECT IF(date_format(datastatus,'%Y-%m-%d')=datamatricula,1,0) as datechanged FROM alunos_matriculas WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    $rdata = mysql_query($qdata);
    $rd = mysql_fetch_array($rdata, MYSQL_ASSOC);

    $updtdata = ($rd['datechanged'] == 1) ? " datastatus='$hoje', " : "";

    $query = "UPDATE alunos_matriculas SET
                status=6,
                " . $updtdata . "
                escoladestino = '" . $escoladestino . "',
                obsSituacao = '" . $obsSituacao . "',
                motivoSituacao = " . $motivoSituacao . "
                WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    if ($result = mysql_query($query)) {
        echo "green|Matrícula transferida com sucesso";
        $msg = "Matrícula transferida com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao transfer a matrícula do aluno";
        $msg = "Erro ao transfer a matrícula do aluno";
        $status = 1;
    }

    $parametroscsv = $nummatricula;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "cancelaMatricula") {
    $qdata = "SELECT IF(date_format(datastatus,'%Y-%m-%d')=datamatricula,1,0) as datechanged FROM alunos_matriculas WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    $rdata = mysql_query($qdata);
    $rd = mysql_fetch_array($rdata, MYSQL_ASSOC);

    $updtdata = ($rd['datechanged'] == 1) ? " datastatus='$hoje', " : "";

    $query = "UPDATE alunos_matriculas SET
                status=3,
                " . $updtdata . "
                escoladestino = '" . $escoladestino . "',
                obsSituacao = '" . $obsSituacao . "',
                motivoSituacao = " . $motivoSituacao . "
                WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    if ($result = mysql_query($query)) {
        echo "green|Matrícula cancelada com sucesso";
        $msg = "Matrícula cancelada com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao cancelar matrícula do aluno";
        $msg = "Erro ao cancelar matrícula do aluno";
        $status = 1;
    }

    $parametroscsv = $nummatricula;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "reabrirMatricula") {
    $qdata = "SELECT IF(date_format(datastatus,'%Y-%m-%d')=datamatricula,1,0) as datechanged FROM alunos_matriculas WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    $rdata = mysql_query($qdata);
    $rd = mysql_fetch_array($rdata, MYSQL_ASSOC);

    $updtdata = ($rd['datechanged'] == 1) ? " datastatus='$hoje', " : "";

    $query = "UPDATE alunos_matriculas SET
                status=1,
                " . $updtdata . "
                escoladestino = '',
                obsSituacao = '',
                motivoSituacao = ''
                WHERE
                nummatricula=" . $nummatricula . " AND
                idaluno=" . $idaluno;
    if ($result = mysql_query($query)) {
        echo "green|Matrícula reaberta com sucesso";
        $msg = "Matrícula reaberta com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao reabrir matrícula do aluno";
        $msg = "Erro ao reabrir matrícula do aluno";
        $status = 1;
    }

    $parametroscsv = $nummatricula;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "trocaPHorarios") {
    $query = "UPDATE alunos_matriculas SET idplanohorario='" . $planohorariosmatriculado . "' WHERE nummatricula='" . $nummatricula . "' AND idaluno=" . $idaluno;

    if ($result = mysql_query($query)) {
        echo "green|Plano de horários trocado com sucesso.";
        $msg = "Plano de horários trocado com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao trocar plano de horários. " . $query;
        $msg = "Erro ao trocar plano de horários";
        $status = 1;
    }

    $parametroscsv = $idaluno . ',' . $nummatricula . ',' . $turmamatriculado;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "addentrevista") {
    $data = $data ?: "0000-00-00";
    $hora = $hora ?: "00:00";
    $datarealizada = $datarealizada ?: "0000-00-00";
    $horarealizada = $horarealizada ?: "00:00";
    $datahora = "$data $hora";
    $datahorarealizada = "$datarealizada $horarealizada";

    $assunto = mysql_real_escape_string($assunto);
    $outro = mysql_real_escape_string($outro);
    $resumo = mysql_real_escape_string($resumo);

    if ($identrevista) {
        $query = "UPDATE alunos_entrevistas
        SET
            assunto='$assunto',
            datahora='$datahora',
            datahorarealizada='$datahorarealizada',
            iddepartamento='$iddepartamento',
            idfuncionario='$idpessoafuncionario',
            idresponsavel='$idpessoaresponsavel',
            outro='$outro',
            resumo='$resumo'
        WHERE id='$identrevista'";
        $msg1 = "atualizada";
    } else {
        $query = "INSERT INTO alunos_entrevistas(
            assunto,
            datahora,
            datahorarealizada,
            idaluno,
            iddepartamento,
            idfuncionario,
            idresponsavel,
            outro,
            resumo
        ) VALUES (
            '$assunto',
            '$datahora',
            '$datahorarealizada',
            '$idaluno',
            '$iddepartamento',
            '$idpessoafuncionario',
            '$idpessoaresponsavel',
            '$outro',
            '$resumo'
        );";
        $msg1 = "cadastrada";
    }
    if ($result = mysql_query($query)) {
        echo "green|Entrevista " . $msg1 . " com sucesso";
        $msg = "Entrevista " . $msg1 . " com sucesso";
        $status = 0;
    } else {
        echo "red|Erro ao " . $msg1 . " entrevista do aluno";
        $msg = "Erro ao " . $msg1 . " entrevista do aluno";
        $status = 1;
    }

    $parametroscsv = $identrevista . "," . $idpessoaresponsavel . "," . $iddepartamento . "," . $outro . "," . $assunto . "," . $datahora . "," . $datahorarealizada . "," . $resumo;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    echo mysql_insert_id() . "|" . $query;
} elseif ($action == "addocorrencia") {
    $inputData = $_REQUEST['data'];

    if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $inputData)) {
        $data = implode('-', array_reverse(explode('/', $inputData)));
    }

    if (preg_match('/\d{4}\-\d{2}\-\d{2}/', $inputData)) {
        $data = $inputData;
    }

    $datahora = $data . " " . $hora;
    $assunto = addslashes($assunto);
    if ($idalunoocorrencia > 0) {
        $query = "UPDATE alunos_ocorrencias SET idaluno=$idaluno, iddisciplina=$iddisciplina, idfuncionario=$idprofessor, idocorrencia=$idocorrencia, iddepartamento=$iddepartamento, nummatricula=$nummatricula, datahora='$datahora', assunto='$assunto' WHERE id=" . $idalunoocorrencia;
        if ($result = mysql_query($query)) {
            $msg = 'Ocorrência atualizada com sucesso';
            $status = 0;
            echo $idalunoocorrencia;
        } else {
            $msg = 'Erro ao atualizar ocorrência';
            $status = 1;
            echo '0';
            http_response_code(500);
        }
    } else {
        $query = "INSERT INTO alunos_ocorrencias(idunidade, idaluno, idocorrencia, iddepartamento, iddisciplina, idfuncionario, nummatricula, datahora, assunto) VALUES ($idunidade, $idaluno, $idocorrencia, $iddepartamento, $iddisciplina, $idprofessor, $nummatricula, '$datahora', '" . nl2br($assunto) . "');";
        if ($result = mysql_query($query)) {
            echo mysql_insert_id();
            $msg = 'Ocorrência inserida com sucesso';
            $status = 0;
        } else {
            $msg = 'Erro ao inserir ocorrência';
            $status = 1;
            echo '0';
            http_response_code(500);
        }
    }
    $parametroscsv = $idunidade . ',' . $idaluno . ',' . $idocorrencia . ',' . $iddepartamento . ',' . $assunto;
    salvaLog($idfuncionario, __FILE__, $action, $status, $parametroscsv, $msg);
} elseif ($action == "removeLigacao") {
    if ($tabela == 'alunos_documentos') {
        $query = "SELECT * FROM alunos_documentos WHERE id=$id";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $idaluno = $row['idaluno'];
    }

    $query = "DELETE FROM $tabela WHERE id=$id";
    if ($result = mysql_query($query)) {
        $msg = "Item removido com sucesso.";
        $status = 0;
    } else {
        $msg = "Erro ao remover item.";
        $status = 1;
    }

    if ($tabela == 'alunos_documentos') {
        $query1 = "SELECT * FROM documentos WHERE idserie=0 AND documentos.id NOT IN ( SELECT iddocumento FROM alunos_documentos WHERE idaluno=$idaluno ) ORDER BY documento ASC";
        $result1 = mysql_query($query1);
        while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
            echo '<option value="' . $row1['id'] . '">' . $row1['documento'] . '</option>';
        }
    }

    $parametroscsv = $tabela . "," . $id;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagar") {
    $erro = 0;
    $msg1 = "";

    $query = "DELETE FROM alunos_matriculas WHERE idaluno IN ( $idaluno ) AND turmamatricula<1"; //
    if ($result = mysql_query($query)) {
        $msg = "Aluno sem matrícula removido com sucesso.";
        $status = 0;
    } else {
        $status = 1;
        $msg = "Erro ao remover aluno sem matrícula.";
    }
    $parametroscsv = $idaluno;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

    if ($debug) {
        echo $msg;
    }

    $idalunoArr = explode(",", $idaluno);
    if ($debug) {
        $x = "1. ( " . print_r($idalunoArr, 1) . ")<br />";
    }


    unset($novoidaluno);
    $query = "SELECT idaluno FROM alunos_matriculas WHERE idaluno IN ( $idaluno ) AND turmamatricula>0 GROUP BY idaluno "; //
    $result = mysql_query($query);
    if ($debug) {
        $x .= $query . "<br />";
    }

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $novoidaluno[] = $row['idaluno'];
    }
    $idalunoArr = array_diff($idalunoArr, $novoidaluno);

    if ($debug) {
        $x .= "2. " . print_r($novoidaluno, 1) . "<br />";
    }
    if ($debug) {
        $x .= "3. " . print_r($idalunoArr, 1) . "<br />";
    }

    $idaluno = implode(",", $idalunoArr);
    unset($novoidaluno);
    $query = "SELECT idaluno FROM alunos_fichafinanceira WHERE idaluno IN ( $idaluno ) AND situacao!=4 GROUP BY idaluno ";
    $result = mysql_query($query);
    if ($debug) {
        $x .= $query . "<br />";
    }
    salvaLog($idfuncionario, __FILE__, $action, $status, $parametroscsv, $query);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $novoidaluno[] = $row['idaluno'];
    }
    if ((is_countable($novoidaluno) ? count($novoidaluno) : 0) > 0) {
        $idalunoArr = array_diff($idalunoArr, $novoidaluno);
    }

    if ($debug) {
        $x .= "4. " . print_r($novoidaluno, 1) . "<br />";
    }
    if ($debug) {
        $x .= "5. " . print_r($idalunoArr, 1) . "<br />";
    }

    $idaluno = implode(",", $idalunoArr);

    if ($debug) {
        echo $x . "<br />=>>" . $idaluno;
    }
    if (strlen($idaluno) > 0) {
        $query = "DELETE FROM alunos WHERE id IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Aluno removido com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover aluno.";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM alunos_matriculas WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Matrícula do Aluno removida com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover matrícula do aluno.";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);


        $query = "DELETE FROM alunos_autorizacoes WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Responsáveis autorizados removidos com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover responsáveis autorizados do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM alunos_documentos WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Documentos do aluno removidos com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover documentos do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM alunos_entrevistas WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Entrevistas do aluno removidas com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover entrevistas do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM alunos_ocorrencias WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Ocorrências do aluno removidas com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover ocorrencias do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM alunos_faltas_dia WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Faltas do aluno removidas com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover faltas do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM alunos_fichafinanceira WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Fichas financeiras do aluno removidas com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover fichas financeiras do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM responsaveis WHERE idaluno IN ( $idaluno )";
        if ($result = mysql_query($query)) {
            $msg = "Responsáveis do aluno removidos com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover responsáveis do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM emails WHERE idpessoa IN ( SELECT idpessoa FROM alunos WHERE id IN ( $idaluno ) )";
        if ($result = mysql_query($query)) {
            $msg = "Emails do aluno removidos com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover emails do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM usuarios WHERE idpessoa IN ( SELECT idpessoa FROM alunos WHERE id IN ( $idaluno ) )";
        if ($result = mysql_query($query)) {
            $msg = "Usuário do aluno removido com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover usuário do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM telefones WHERE idpessoa IN ( SELECT idpessoa FROM alunos WHERE id IN ( $idaluno ) )";
        if ($result = mysql_query($query)) {
            $msg = "Telefones do aluno removidos com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover telefones do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        $query = "DELETE FROM pessoas WHERE id IN ( SELECT idpessoa FROM alunos WHERE id IN ( $idaluno ) )";
        if ($result = mysql_query($query)) {
            $msg = "Dados pessoais do aluno  removidos com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao remover dados pessoais do aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

        if ($erro == 0) {
            echo "green|Aluno(s) removido(s) com sucesso|" . $idaluno;
            $msg = "Aluno removido com sucesso.";
            $status = 0;
        } else {
            $status = 1;
            echo "red|Erro $erro ao remover o(s) aluno(s)|" . $idaluno;
            $msg = "Erro $erro ao remover aluno";
        }
        $parametroscsv = $idaluno;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }
} elseif ($action == "apagaResponsavel") {
    $query = "DELETE FROM responsaveis WHERE id=" . $id;
    if ($result = mysql_query($query)) {
        $msg = "Responsável do aluno removidos com sucesso.";
        $status = 0;
    } else {
        $status = 1;
        $msg = "Erro ao remover responsável do aluno";
    }
} elseif ($action == "associaResponsavel") {
    if ($idparentesco == '0') {
        $query = "SELECT idpessoa FROM alunos WHERE id=" . $idaluno;
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $idpessoa = $row['idpessoa'];
    }

    $query = "INSERT INTO responsaveis(idaluno, idpessoa, idparentesco, respfin, respfin2, resppedag, autorizado, visualiza_financeiro, visualiza_pedagogico) VALUES ('$idaluno', '$idpessoa', '$idparentesco', '$respfinanceiro', '$respfinanceiro2', '$resppedagogico', '$autorizado', '$visualiza_financeiro', '$visualiza_pedagogico')";
    if ($result = mysql_query($query)) {
        $msg = "Responsável associado ao aluno com sucesso.";
        $status = 0;
    } else {
        $status = 1;
        $msg = "Erro ao associar responsável ao aluno";
    }
    $parametroscsv = $idaluno . "," . $idpessoa;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    if ($debug == 1) {
        echo "<br>=> " . $query . " " . $result . "<br><br>";
    }
} elseif ($action == "atualizarResponsavel") {
    $responsavelId = filter_var($_REQUEST['idresp'], FILTER_VALIDATE_INT);
    try {
        ResponsavelService::atualizar($responsavelId, $_REQUEST);
    } catch (PDOException $th) {
        http_response_code(422);
        echo "Erro durante a operação.";
    } catch (Throwable $th) {
        http_response_code(422);
        echo $th->getMessage();
    }
} elseif ($action == "cadastrarResponsavel") {
    try {
        ResponsavelService::cadastrar($_REQUEST);
    } catch (PDOException $th) {
        http_response_code(422);
        echo "Erro durante a operação.";
    } catch (Throwable $th) {
        http_response_code(422);
        echo $th->getMessage();
    }
} elseif ($action == "atualizaLancamento") {
    //mysql_query("START TRANSACTION");

    $ddatavencimento = explode("/", $datavencimento);
    $datavencimento = $ddatavencimento[2] . "-" . $ddatavencimento[1] . "-" . $ddatavencimento[0];
    //$valorparcelas = trim(str_replace(",", ".", str_replace(".", "", str_replace("R$", "",  $valorparcelas ) ) ) );

    $erro = 0;
    $valorparcelas = 0;

    $valor_a_calcular_bolsa = 0;

    foreach ($eventovalor as $key => $evalor) {
        $idfichaitens = $ideventovalor[$key];
        $evalor = trim(str_replace(",", ".", str_replace(".", "", $evalor)));
        // alteração amauri
        $qfi = "SELECT * FROM alunos_fichaitens WHERE id=" . $idfichaitens;
        $rfi = mysql_query($qfi);
        $row_fi = mysql_fetch_array($rfi, MYSQL_ASSOC);

        // if(($row_fi['descontoboleto']==1) && $usarbolsabruto==0) {
        if (($row_fi['descontoboleto'] == 1)) {
            $valor_a_calcular_bolsa += $evalor;
        }
        // /alteração amauri

        $query = "UPDATE alunos_fichaitens SET valor=$evalor WHERE id=" . $idfichaitens;
        if (!($result = mysql_query($query))) {
            $erro++;
            exit();
        }
        $valorparcelas = $valorparcelas + $evalor;
    }

    if ($erro == 0) {
        $q_percent = "SELECT bolsa,bolsapercentual FROM alunos_fichafinanceira WHERE id=" . $idfichafinanceira;
        $r_percent = mysql_query($q_percent);
        $row_percent = mysql_fetch_array($r_percent, MYSQL_ASSOC);

        $updt_bolsapercentual = '';

        if ($usarbolsabruto == 1) {
            $novabolsa = trim(str_replace(",", ".", str_replace(".", "", $bolsa))) ?: 0;

            $novopercentual = $valor_a_calcular_bolsa ? (($novabolsa * 100) / $valor_a_calcular_bolsa) : 0;
            $updt_bolsapercentual .= " bolsapercentual='" . number_format($novopercentual, 2) . "', ";
        } else {
            $bolsa_percentual = trim(str_replace(",", ".", str_replace(".", "", $bolsapercentual)));
            $novabolsa = ($valor_a_calcular_bolsa * $bolsa_percentual) / 100;

            $updt_bolsapercentual .= " bolsapercentual='" . $bolsa_percentual . "', ";
        }

        $query = "UPDATE alunos_fichafinanceira SET valor=$valorparcelas, bolsa=$novabolsa, " . $updt_bolsapercentual . " datavencimento='$datavencimento' WHERE id=" . $idfichafinanceira;
        if ($debug == 1) {
            echo "<br>=> " . $query . " " . $result . "<br><br>";
        }
        if ($result = mysql_query($query)) {
            $msg = "Lançamento atualizado com sucesso.";
            $status = 0;
            $retornoObj = [
                "success" => "Titulo alterado com sucesso.",
            ];

            $conta = Titulo::find($idfichafinanceira)->conta;
            $usaApi = $conta->usabancoAPI;
            if ($usaApi && $conta->banconum == 461) {
                try {
                    $asaas = new Asaas();
                    // $updateObj = [
                    //     "value" => $valorparcelas - $novabolsa,
                    //     "dueDate" => $datavencimento
                    // ];
                    $response = $asaas->alterarCobranca($idfichafinanceira);
                } catch (Exception $e) {
                    $retornoObj = [
                        "error" => json_decode($e->getMessage())->errors[0]->description,
                    ];
                    $status = 1;
                    $msg = "Erro ao atualizar lançamento do aluno.";
                    //mysql_query("ROLLBACK");
                }

                if ($response['id']) {
                    $retornoObj = [
                        "success" => "Cobrança Asaas alterada com sucesso.",
                    ];
                    //mysql_query("COMMIT");
                }
            } else {
                //mysql_query("COMMIT");
            }
        } else {
            $status = 1;
            $msg = "Erro ao atualizar lançamento do aluno.";
            $retornoObj = [
                "error" => "Erro ao atualizar lançamento do aluno.",
            ];
            //mysql_query("ROLLBACK");
        }
    } else {
        $status = 1;
        $msg = "Erro ao atualizar lançamento do aluno.";
        $retornoObj = [
            "error" => "Erro ao atualizar lançamento do aluno.",
        ];
        //mysql_query("ROLLBACK");
    }
    echo json_encode($retornoObj);

    $parametroscsv = $idfichafinanceira . "," . $valorparcelas . "," . $datavencimento;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "lancamentoVendaEstoque") {
    mysql_query("START TRANSACTION");

    $erro = false;
    $idam = explode("@", $idaluno);
    $idaluno = $idam[0];
    $nummatricula = $idam[1];

    $queryA = "SELECT turmamatricula, nummatricula, idunidade FROM alunos_matriculas WHERE nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno;
    $resultA = mysql_query($queryA);
    $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
    $turmamatricula = $rowA['turmamatricula'];
    $nummatricula = $rowA['nummatricula'];
    $idunidadematricula = $rowA['idunidade'];

    $query = "SELECT * FROM contasbanco where idfuncionario = " . $idfuncionario;
    $result = mysql_query($query);
    $financeiro = '';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $conta = $row['id'];
    }

    $idcontasbanco ??= $conta;

    unset(
        $idek,
        $idgrupo,
        $codigo,
        $descricao,
        $materialValor,
        $qtde,
        $codigoeventofinanceiro,
        $eventofinanceiro
    );

    $itens = is_countable($produto) ? count($produto) : 0;
    foreach ($produto as $key => $prod) {
        [$idek[$key], $idgrupo[$key], $codigo[$key], $descricao[$key], $materialValor[$key], $qtde[$key]] = explode('#@#', $prod);
        $codigoeventofinanceiro[$key] = "";
        $eventofinanceiro[$key] = "";

        if ($idgrupo[$key] == "kit") {
            $queryA = "SELECT estoque.id, idgrupo, estoque.codigo, descricao, valorvenda, quantidade, eventosfinanceiros.codigo as codigoeventofinanceiro, eventofinanceiro FROM estoque, estoque_kit_mat, estoque_grupos, eventosfinanceiros WHERE  eventosfinanceiros.id=estoque_grupos.ideventofinanceiro AND estoque_grupos.id=estoque.idgrupo AND estoque_kit_mat.idkit=" . $idek[$key] . " AND estoque.id=estoque_kit_mat.idestoque";
            $resultA = mysql_query($queryA);
            while ($rowA = mysql_fetch_array($resultA, MYSQL_ASSOC)) {
                $idek[$itens] = $rowA['id'];
                $idgrupo[$itens] = $rowA['idgrupo'];
                $codigo[$itens] = $rowA['codigo'];
                $descricao[$itens] = $rowA['descricao'];
                $materialValor[$itens] = $rowA['valorvenda'];
                $qtde[$itens] = $rowA['quantidade'];
                $codigoeventofinanceiro[$itens] = $rowA['codigoeventofinanceiro'];
                $eventofinanceiro[$itens] = $rowA['eventofinanceiro'];

                $valoroperacao = $rowA['quantidade'] * $rowA['valorvenda'];
                $queryBaixaEstoque = "INSERT INTO
                                            estoque_movimentacao
                                            (idestoque, idunidadedestino, idterceiro,
                                            quantidade, dataoperacao, idfuncionario,
                                            datamovimentacao, valormovimentacao,
                                            tempoentrega, motivo, idaluno)
                                        VALUES
                                        ( " . $rowA['id'] . ",
                                            '-1','-1'," . $rowA['quantidade'] . ",
                                                '$hoje','$idfuncionario','$hoje',
                                            $valoroperacao,'0','', " . $idaluno . ");";
                $resultBaixaEstoque = mysql_query($queryBaixaEstoque);
                $itens++;

                unset($idek[$key]);
                unset($idgrupo[$key]);
                unset($codigo[$key]);
                unset($descricao[$key]);
                unset($materialValor[$key]);
                unset($qtde[$key]);
                unset($codigoeventofinanceiro[$key]);
                unset($eventofinanceiro[$key]);
            }
        } else {
            $queryA = "SELECT eventosfinanceiros.codigo as codigoeventofinanceiro, eventofinanceiro FROM estoque, estoque_grupos, eventosfinanceiros WHERE eventosfinanceiros.id=estoque_grupos.ideventofinanceiro AND estoque_grupos.id=estoque.idgrupo AND estoque.id=" . $idek[$key];
            $resultA = mysql_query($queryA);
            $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
            $codigoeventofinanceiro[$key] = $rowA['codigoeventofinanceiro'];
            $eventofinanceiro[$key] = $rowA['eventofinanceiro'];
            $valoroperacao = $qtde[$key] * $materialValor[$key];

            // manipula estoque
            $getEstoque = "SELECT * FROM estoque_movimentacao WHERE idestoque=" . $idek[$key] . " AND estoque_unidade=" . $idunidadebaixa . " AND movimentacao=1";
            $resEstoque = mysql_query($getEstoque);
            $estoqueAtual = mysql_fetch_array($resEstoque, MYSQL_ASSOC);
            $novaquant = $estoqueAtual['quantidade'] - $qtde[$key];
            $estoqueNegativo = $novaquant < 0;
            $erro = $erro || !mysql_num_rows($resEstoque) || $estoqueNegativo;

            if (!$erro) {
                $updEstoque = "UPDATE estoque_movimentacao SET quantidade = '$novaquant' WHERE id={$estoqueAtual['id']}";
                $result = mysql_query($updEstoque);
            }

            $atualizado = mysql_affected_rows() == 1;
            $erro = $erro || !$result || !$atualizado;

            $queryBaixaEstoque = "INSERT INTO estoque_movimentacao(idestoque, idunidadedestino, idterceiro, quantidade, dataoperacao, idfuncionario, datamovimentacao, valormovimentacao, tempoentrega, motivo, idaluno, estoque_unidade) VALUES ( " . $idek[$key] . ", '-1','-1'," . $qtde[$key] . ",'$hoje','$idfuncionario','$hoje',$valoroperacao,'0','', " . $idaluno . ", " . $idunidadematricula . ");";
            $resultBaixaEstoque = mysql_query($queryBaixaEstoque);
        }

        if ($erro) {
            $msg = "Erro ao dar baixa em material.";

            if ($estoqueNegativo) {
                $msg .= "Verifique a quantidade do produto!";
            }

            $status = 1;

            echo "red|$msg";
            mysql_query("ROLLBACK");
            return false;
        }
    }

    $codigoeventofinanceiroFicha = array_unique($codigoeventofinanceiro);
    $eventofinanceiroFicha = array_unique($eventofinanceiro);

    unset($valorFicha);
    unset($qtdeFicha);

    foreach ($eventofinanceiroFicha as $key => $eff) {
        foreach ($eventofinanceiro as $key1 => $ef) {
            if ($eff == $ef) {
                $valorFicha[$key] += $qtde[$key1] * $materialValor[$key1];
                $qtdeFicha[$key] += $materialValor[$key1];
            }
        }
    }

    if ($qdorecebe == 0) { //Recebe na Venda
        $qtdeparcelas = 1;
        $data1parcela = date("Y-m-d");
    } elseif ($qdorecebe == 1) { //Gera Títulos
        $dtdata1parcela = explode("/", $data1parcela);
        $data1parcela = $dtdata1parcela[2] . "-" . $dtdata1parcela[1] . "-" . $dtdata1parcela[0];
        if ($data1parcela == "--") {
            $data1parcela = date("Y-m-d");
        }
    }

    $queryA = "SELECT titulofichafinanceira, empresas.id as eid FROM empresas, contasbanco
 WHERE contasbanco.idempresa=empresas.id AND contasbanco.id=" . $idcontasbanco;
    $resultA = mysql_query($queryA);
    $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
    $titulofichafinanceira = $rowA['titulofichafinanceira'];
    $idempresa = $rowA['eid'];

    $queryTest = "SELECT MAX( CAST(titulo as UNSIGNED)) as titulomax FROM alunos_fichafinanceira, contasbanco, empresas
 WHERE alunos_fichafinanceira.idcontasbanco=contasbanco.id AND contasbanco.idempresa=empresas.id AND empresas.id='$idempresa';";
    $resultTest = mysql_query($queryTest);
    $rowTest = mysql_fetch_array($resultTest, MYSQL_ASSOC);
    $titulomax = $rowTest['titulomax'];
    $teveerro = !($titulomax < $titulofichafinanceira);

    if (!$teveerro) {
        $query1 = "UPDATE empresas SET titulofichafinanceira='" . ($titulofichafinanceira + $qtdeparcelas) . "' WHERE id=" . $idempresa;
        $result1 = mysql_query($query1);

        $valorparcelas = $carrinhoTotalReal / $qtdeparcelas;
        $valorfinaldasparcelas = $valorparcelas;
        $valorbrutoparcelas = $valorparcelas;
        $descontoparcelas = 0;
        $datavencimento = $data1parcela;
        $d = new DateTime($datavencimento);
        $diainicial = $d->format('d');
        $diaanterior = 0;

        $allok = 0;
        $teveerro = 0;

        for ($poi = 1; $poi <= $qtdeparcelas; $poi++) {
            $queryFicha = "INSERT IGNORE INTO alunos_fichafinanceira(idfuncionario, idaluno, nummatricula, titulo, data1parcela,
dataemissao, datavencimento, valor, bolsa, desconto, multa, juros, datarecebimento, idcontasbanco, valorrecebido,
situacao, numeroloterps, naturezaoperacao, issretido, matricula)

VALUES ($idfuncionario, $idaluno, $nummatricula, $titulofichafinanceira, '$data1parcela',
 '$hoje', '$datavencimento', $valorparcelas, 0,0,0,0,'0000-00-00',$idcontasbanco, 0, 0, 0, 1, 0, 0);";

            if ($resultFicha = mysql_query($queryFicha)) {
                $msg = "Ficha financeira inserida com sucesso.";
                $status = 0;
                $allok = $allok + 1;
                $idalunos_fichafinanceira = mysql_insert_id();
            } else {
                $status = 1;
                $msg = "Erro ao inserir ficha financeira.";
                $teveerro = $teveerro + 1;

                echo "red|$msg";
                mysql_query("ROLLBACK");
                return;
            }
            $parametroscsv = $idalunos_fichafinanceira . "," . $valorparcelas;
            salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

            foreach ($eventofinanceiroFicha as $key => $eff) {
                $query = "INSERT INTO alunos_fichaitens (idalunos_fichafinanceira, parcela, totalparcelas, codigo, eventofinanceiro, valor) VALUES ( $idalunos_fichafinanceira, '$poi', $qtdeparcelas, '$codigoeventofinanceiroFicha[$key]', '" . $eff . "', $valorFicha[$key]);";

                if ($result = mysql_query($query)) {
                    $allok = $allok + 1;
                } else {
                    $teveerro = $teveerro + 1;
                }
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
                $d->setDate($d->format('Y'), $d->format('m'), $diaanterior);
                $diaanterior = 0;
            }

            if ($d->format('d') > $diainicial) {
                $d->modify($d->format('Y') . '-' . $d->format('m') . '-' . $diainicial);
            }

            $datavencimento = $d->format('Y-m-d');
        }
    }

    if ($teveerro == 0) {
        echo "green|Lançamento realizado com sucesso.|" . $idaluno . "|" . $turmamatricula . "|" . $idalunos_fichafinanceira . "|" . $qdorecebe . "|" . $idunidadematricula . "|" . $nummatricula;
        $msg = "Lançamento realizado com sucesso.";
        $status = 0;
        mysql_query("COMMIT");
    } else {
        $status = 1;
        $msg = "Erro ao realizar novo lançamento.";
        echo "red|$msg";
        mysql_query("ROLLBACK");
    }

    $parametroscsv = $teveerro;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "novoLancamento") {
    // Instancia dependencias
    $request = ServerRequestFactory::fromGlobals();
    $responseFactory = new ResponseFactory();

    // Invoca controller e recebe resposta
    $lancamento = new NovoLancamentoController($responseFactory);
    $response = $lancamento->lancamento($request);

    // Emite resposta
    http_response_code($response->getStatusCode());
    echo $response->getBody();
} elseif ($action == "novoLancamentoMultiplo") {
    $pagamentoCartaoOnline = filter_var($_REQUEST['pagamento-cartao-online'], FILTER_VALIDATE_BOOL) ? 1 : 0;
    $dadosalunosMult = explode(",", $dadosalunos);

    $dtdata1parcela = explode("/", $data1parcela);
    $data1parcela = $dtdata1parcela[2] . "-" . $dtdata1parcela[1] . "-" . $dtdata1parcela[0];

    if ($data1parcela == "--") {
        $data1parcela = date("Y-m-d");
    }
    $datavencimento = $data1parcela;


    if ($debug == 1) {
        echo $diainicial . "<br>";
    }

    $evtfin = explode("@", $eventosMatricula);
    $codigoeventofinanceiro = $evtfin[0];
    $eventofinanceiro = $evtfin[1];

    $valorparcelas = str_replace(",", ".", str_replace(".", "", $valorparcelas));
    $valorfinaldasparcelas = str_replace(",", ".", str_replace(".", "", $valorfinaldasparcelas));
    $descontoparcelas = str_replace(",", ".", str_replace(".", "", $descontoparcelas));
    $valorbrutoparcelas = str_replace(",", ".", str_replace(".", "", $valorbrutoparcelas));

    $retornoObj = [];
    $contaModel = Conta::find($idcontasbanco);
    $usaApi = $contaModel->usabancoAPI;
    $pulaFeriados = Configuracao::completa()['vencimento_pula_feriados'];

    foreach ($dadosalunosMult as $dadosalunosM) {
        $diaanterior = 0;
        $datavencimento = $data1parcela;
        $d = new DateTime($data1parcela);
        $diainicial = $d->format('d');

        $ddalunos = explode("@", $dadosalunosM);
        $idunidade = $ddalunos[0];
        $idaluno = $ddalunos[1];
        $nummatricula = $ddalunos[2];
        if ($debug == 1) {
            echo $idunidade . " " . $idaluno . " " . $nummatricula . "<br>";
        }

        if ($idunidade > 0) {
            $queryA = "SELECT titulofichafinanceira, empresas.id as eid FROM empresas, contasbanco WHERE contasbanco.idempresa=empresas.id AND contasbanco.id=" . $idcontasbanco;
            $resultA = mysql_query($queryA);
            $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
            $titulofichafinanceira = $rowA['titulofichafinanceira'];
            $idempresa = $rowA['eid'];
            if ($debug == 1) {
                echo $queryA . " " . $resultA . " " . $titulofichafinanceira . " " . $idempresa . "<br>";
            }

            $query1 = "UPDATE empresas SET titulofichafinanceira='" . ($titulofichafinanceira + $qtdeparcelas) . "' WHERE id=" . $idempresa;
            $result1 = mysql_query($query1);
            if ($debug == 1) {
                echo $query1 . " " . $result1 . "<br>";
            }

            $allok = 0;
            $teveerro = 0;
            $erro_unifica = 0;
            $msg_unifica = '';

            if ($unifica == "sim") { // UNIFICAÇÃO DE TITULOS
                if ($debug == 1) {
                    echo " UNIFICA<br /><br />";
                }

                for ($poi = $parcelainicial; $poi <= $qtdeparcelas; $poi++) {
                    $mvencimento = explode("-", $datavencimento);
                    $mesvencimento = $mvencimento[1];
                    $anovencimento = $mvencimento[0];

                    $queryA1 = "SELECT id, datavencimento FROM alunos_fichafinanceira WHERE situacao=0 AND datavencimento='" . $datavencimento . "' AND nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno . " AND remessaenviado=1 ORDER BY valor DESC LIMIT 1 ";

                    $resultA1 = mysql_query($queryA1);
                    $rowA1 = mysql_fetch_array($resultA1, MYSQL_ASSOC);

                    if ($debug == 1) {
                        echo $queryA1 . " " . $resultA1 . "<br>";
                    }

                    if ($rowA1['id'] != 0) {
                        $teveerro = 1;
                        $erro_unifica = 1;
                        $msg_unifica = 'Não é possível unificar a um título que já foi enviado em remessa.';
                    } else {
                        $queryA = "SELECT id, datavencimento FROM alunos_fichafinanceira WHERE situacao=0 AND datavencimento='" . $datavencimento . "' AND nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno . " AND remessaenviado=0 ORDER BY valor DESC LIMIT 1 ";

                        $resultA = mysql_query($queryA);
                        $rowA = mysql_fetch_array($resultA, MYSQL_ASSOC);
                        $idalunos_fichafinanceira = $rowA['id'];
                        $query = "UPDATE alunos_fichafinanceira SET valor = valor+" . $valorparcelas . " WHERE id=" . $idalunos_fichafinanceira;

                        if ($debug == 1) {
                            echo $query . " " . $result . "<br>";
                        }

                        $insertSuccess = 0;
                        if ($result = mysql_query($query)) {
                            $msg = "Valor da ficha financeira atualizada com sucesso. ( Unificado ).";
                            $status = 0;
                            $allok = $allok + 1;
                            $insertSuccess++;
                        } else {
                            $status = 1;
                            $msg = "Erro ao atualizar valor da ficha financeira. ( Unificado ).";
                            $teveerro = $teveerro + 1;
                        }
                        $parametroscsv = $idalunos_fichafinanceira . "," . $valorparcelas;
                        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);


                        if ($debug == 1) {
                            echo $query . " " . $result . "<br>";
                        }

                        $query1 = "UPDATE empresas SET titulofichafinanceira='" . ($titulofichafinanceira + $qtdeparcelas) . "' WHERE id=" . $idempresa;
                        $result1 = mysql_query($query1);
                        if ($debug == 1) {
                            echo $query1 . " " . $result1 . "<br>";
                        }

                        $query = "INSERT INTO alunos_fichaitens (idalunos_fichafinanceira, parcela, totalparcelas, codigo, eventofinanceiro, valor, descontoboleto) VALUES ( $idalunos_fichafinanceira, '$poi', $qtdeparcelas, $codigoeventofinanceiro, '$eventofinanceiro', $valorparcelas, $descontoboleto);";

                        if ($result = mysql_query($query)) {
                            $allok = $allok + 1;
                            $insertSuccess++;
                        } else {
                            $teveerro = $teveerro + 1;
                        }
                        if ($debug == 1) {
                            echo $query . " " . $result . "<br>";
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
                            $d->setDate($d->format('Y'), $d->format('m'), $diaanterior);
                            $diaanterior = 0;
                        }

                        if ($d->format('d') > $diainicial) {
                            $d->modify($d->format('Y') . '-' . $d->format('m') . '-' . $diainicial);
                        }

                        $datavencimento = $d->format('Y-m-d');

                        if ($insertSuccess == 2) {
                            if ($usaApi && $contaModel->banconum == 461) {
                                try {
                                    $asaas = new Asaas();
                                    $response = $asaas->alterarCobranca($idalunos_fichafinanceira);
                                } catch (Exception $e) {
                                    $retornoObj[] = [
                                        "error" => "Titulo: " . $titulofichafinanceira - 1 . "Erro: " .  json_decode($e->getMessage())->errors[0]->description,
                                    ];
                                    $teveerro = 1;

                                    // desfaz a unificação do título se a cobrança da Asaas falhar
                                    $query = "UPDATE alunos_fichafinanceira SET valor = valor-" . $valorparcelas . " WHERE id=" . $idalunos_fichafinanceira;
                                    mysql_query($query);
                                    $query = "DELETE FROM alunos_fichaitens WHERE idalunos_fichafinanceira=" . $idalunos_fichafinanceira;
                                    mysql_query($query);
                                }

                                if ($response['id']) {
                                    $retornoObj[] = [
                                        "success" => "Cobrança Asaas unificada com sucesso para o titulo " . ($titulofichafinanceira - 1),
                                    ];
                                }
                            } else {
                                $retornoObj[] = ["success" => "Titulo unificado com sucesso para o titulo " . $titulofichafinanceira - 1];
                            }
                        } else {
                            $retornoObj[] = ["error" => "Ocorreu um erro ao realizar o lançamento unificado para o titulo " . $titulofichafinanceira - 1];
                        }
                    }
                }
            } else {  // NÃO UNIFICA
                if ($debug == 1) {
                    echo "NAO UNIFICA<br /><br />";
                }

                if ($valorparcelas > 0 || $valorbrutoparcelas > 0) {
                    for ($poi = $parcelainicial; $poi <= $qtdeparcelas; $poi++) {
                        if ($pulaFeriados) {
                            $datavencimento = proxDiaUtil($datavencimento); // Função que pega o próximo dia útil, caso a data de vencimento não seja um dia útil
                        }

                        if ($matricula == "sim") {
                            $query = "INSERT INTO alunos_fichafinanceira(idfuncionario, idaluno, nummatricula, titulo, data1parcela, dataemissao, datavencimento, valor, bolsa, bolsapercentual, desconto, multa, juros, datarecebimento, idcontasbanco, valorrecebido, situacao, numeroloterps, naturezaoperacao, issretido, matricula, pagamento_cartao_online) VALUES ($idfuncionario,$idaluno, $nummatricula, $titulofichafinanceira, '$data1parcela', '$hoje', '$datavencimento', $valorbrutoparcelas, $descontoparcelas, $descontoparcelaspercentual,0,0,0,'0000-00-00',$idcontasbanco,0, 0, 0, 1, 0, 1, '$pagamentoCartaoOnline');";
                        } else {
                            $query = "INSERT INTO alunos_fichafinanceira(idfuncionario,idaluno, nummatricula, titulo, data1parcela, dataemissao, datavencimento, valor, bolsa, desconto, multa, juros, datarecebimento, idcontasbanco, valorrecebido, situacao, numeroloterps, naturezaoperacao, issretido, matricula, pagamento_cartao_online) VALUES ($idfuncionario,$idaluno, $nummatricula, $titulofichafinanceira, '$data1parcela', '$hoje', '$datavencimento', $valorparcelas, 0,0,0,0,'0000-00-00',$idcontasbanco,0, 0, 0, 1, 0, 0, '$pagamentoCartaoOnline');";
                        }

                        $insertSuccess = 0;
                        if ($result = mysql_query($query)) {
                            $msg = "Ficha financeira inserida com sucesso. ( Não Unificado ).";
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

                        if ($debug == 1) {
                            echo $query . " " . $result . "<br>";
                        }

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

                        if ($debug == 1) {
                            echo $query . " " . $result . "<br>";
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
                            $d->setDate($d->format('Y'), $d->format('m'), $diaanterior);
                            $diaanterior = 0;
                        }

                        if ($d->format('d') > $diainicial) {
                            $d->modify($d->format('Y') . '-' . $d->format('m') . '-' . $diainicial);
                        }

                        $datavencimento = $d->format('Y-m-d');

                        if ($insertSuccess == 2) {
                            if ($usaApi && $contaModel->banconum == 461) {
                                try {
                                    $asaas = new Asaas();
                                    $response = $asaas->criarCobranca($idalunos_fichafinanceira);
                                } catch (Exception $e) {
                                    $retornoObj[] = [
                                        "error" => "Titulo: " . $titulofichafinanceira - 1 . "Erro: " .  json_decode($e->getMessage())->errors[0]->description,
                                    ];
                                    $teveerro = 1;
                                    $titulofichafinanceira--;

                                    // desfaz a criação do título se a cobrança da Asaas falhar
                                    $query = "DELETE FROM alunos_fichafinanceira WHERE id=" . $idalunos_fichafinanceira;
                                    mysql_query($query);
                                    $query = "DELETE FROM alunos_fichaitens WHERE idalunos_fichafinanceira=" . $idalunos_fichafinanceira;
                                    mysql_query($query);
                                }

                                if ($response['id']) {
                                    $retornoObj[] = [
                                        "success" => "Cobrança Asaas criada com sucesso para o titulo " . ($titulofichafinanceira - 1),
                                    ];
                                }
                            } else {
                                $retornoObj[] = ["success" => "Titulo " . $titulofichafinanceira - 1 . "lançado com sucesso!"];
                            }
                        } else {
                            $retornoObj[] = ["error" => "Ocorreu um erro ao realizar o lançamento para o titulo " . $titulofichafinanceira - 1];
                        }
                    }
                }
            }
        } else {
            $teveerro = 1;
        }

        if ($teveerro == 0) {
            $msg = "Lançamento realizado com sucesso. ";
            $status = 0;
        } else {
            $status = 1;
            $msg = "Erro ao realizar novo lançamento. " . $msg_unifica;
        }
        $parametroscsv = $teveerro;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    }

    echo json_encode($retornoObj);  // fora do loop de alunos
} elseif ($action == "atualizar") {
    AlunoService::atualizar($_REQUEST, $pessoa);
} elseif ($action == "cadastrar") {
    AlunoService::cadastrar($_REQUEST);
} elseif ($action == "listaPorEmpresa") {
    $query = "SELECT *, alunos.id as as_id, pessoas.id as pid, DATE_FORMAT(datanascimento,'%d/%m/%Y') as dtnasc FROM alunos, alunos_matriculas, pessoas, unidades_empresas WHERE alunos.idpessoa=pessoas.id AND alunos_matriculas.idaluno=alunos.id AND unidades_empresas.idempresa=$idempresa AND unidades_empresas.idunidade=alunos_matriculas.idunidade AND alunos_matriculas.anoletivomatricula=$idanoletivo GROUP BY alunos.id ORDER BY pessoas.nome ASC";
    $result = mysql_query($query);
    //echo $query;

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['as_id'] . '" >';
        echo $row['nome'];
        if ($row['status'] == 2) {
            echo " ( TRANCADO )";
        } elseif ($row['status'] == 3) {
            echo " ( CANCELADO )";
        }
        echo '</option>';
    }
} elseif ($action == "listaIdMatriculaNome") {
    $query = "SELECT alunos.id as as_id, alunos_matriculas.nummatricula, nome FROM alunos, alunos_matriculas, pessoas WHERE  alunos_matriculas.status = 1 and alunos.idpessoa=pessoas.id AND alunos_matriculas.idaluno=alunos.id AND alunos_matriculas.anoletivomatricula=$idanoletivo AND alunos_matriculas.turmamatricula=$idturma " . $sql . " ORDER BY pessoas.nome ASC";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo '<option value="' . $row['as_id'] . '@' . $row['nummatricula'] . '" >' . $row['nome'] . '</option>';
    }
} elseif ($action == "recebeAlunosUnidade") {
    $query = "SELECT alunos.id as idaluno, nome, status, nummatricula FROM alunos, pessoas, alunos_matriculas WHERE alunos_matriculas.idunidade=$idunidadealuno AND alunos_matriculas.idaluno=alunos.id AND alunos.idpessoa=pessoas.id ORDER BY nome;";
    $result = mysql_query($query);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        ?>
        <option value="<?= $row['idaluno'] ?>">
            <?php
            echo $row['nome'] . " ( mat: " . $row['nummatricula'] . ")";
            if ($row['status'] == 2) {
                echo " ( TRANCADO )";
            } elseif ($row['status'] == 3) {
                echo " ( CANCELADO )";
            }
            ?>
        </option>
        <?php
    }
} elseif ($action == "recebeAlunosUnidadeTurma") {
    $turmas = '';
    for ($i = 0; $i < (is_countable($idturmaaluno) ? count($idturmaaluno) : 0); $i++) {
        $turmas .= $idturmaaluno[$i] . ',';
    }

    $query = "SELECT alunos.id as idaluno, nome, status, nummatricula,turmamatricula FROM alunos, pessoas, alunos_matriculas WHERE alunos_matriculas.idunidade=$idunidadeturma AND alunos_matriculas.idaluno=alunos.id AND alunos.idpessoa=pessoas.id AND alunos_matriculas.turmamatricula IN (" . rtrim($turmas, ',') . ") ORDER BY nome;";
    $result = mysql_query($query);
    //echo '<input type="hidden" value="'.$query.'">';
    echo "<option value='0'> - Selecione - </option>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        ?>
        <option data="<?= $query ?>" value="<?= $row['idaluno'] ?>">
            <?php
            echo $row['nome'] . " ( mat: " . $row['nummatricula'] . ")";
            if ($row['status'] == 2) {
                echo " ( TRANCADO )";
            } elseif ($row['status'] == 3) {
                echo " ( CANCELADO )";
            }
            ?>
        </option>
        <?php
    }
} elseif ($action == "recebeAlunosIdNome") {    // EM TESTES
    // PEGA O ID DA TURMA
    if (!$idturma = filter_var($_REQUEST['idturma'] ?? '', FILTER_VALIDATE_INT)) {
        return;
    }
    // SE PEGAR O ID DA TURMA MOSTRA ALERTA
    echo "<script>
    window.alert('ID da turma pego! " . $idturma . "');
    </script>";

    // PEGA O ID DA unidade
    if (!$idunidade = filter_var($_REQUEST['idunidade'] ?? '', FILTER_VALIDATE_INT)) {
        return;
    }
    // SE PEGAR O ID DA TURMA MOSTRA ALERTA
    echo "<script>
    window.alert('ID da unidade pego! " . $idunidade . "');
    </script>";

    /*
    $turmas = '';
    for ($i = 0; $i < count($idturmaaluno); $i++) {
        $turmas .= $idturmaaluno[$i] . ',';
    }
    */
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $query = "SELECT alunos.id as idaluno, nome FROM alunos, pessoas, alunos_matriculas WHERE alunos_matriculas.idunidade=" . $idunidade . " AND alunos_matriculas.idaluno=alunos.id AND alunos.idpessoa=pessoas.id AND alunos_matriculas.turmamatricula IN " . $idturma . " ORDER BY nome";
    $result = mysql_query($query);

    // SE RODAR A QUERY MOSTRA ALERTA DO ESTADO DO MYSQL
    //TESTE DE CÓDIGO RODANDO
    echo "<script>
    window.alert('Query rodando! " . $mysqli->sqlstate . "');
    </script>";
    //echo '<input type="hidden" value="'.$query.'">';
    //echo "<option value='0'> - Selecione - </option>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "
        <option data='' value=" . $row['idaluno'] . " >" .
            $row['nome'] . "
        </option>";
    }
} elseif ($action == "obsfinanceiro") {
    $query = "UPDATE alunos SET obs_financeiro='" . $observacao . "' WHERE id=" . $idaluno;

    if ($result = mysql_query($query)) {
        $msg = "Observação atualizada.";
        $status = 0;
    } else {
        $msg = "Erro ao atualizar observação.";
        $status = 1;
    }
} elseif ($action == "descontocomercial") {
    $backup = DB2::selectOne("SELECT desconto_comercial, desconto_comercial_msg FROM alunos WHERE id = ?", [$idaluno]);

    $query = "UPDATE alunos SET desconto_comercial='" . $descontocomercial . "', desconto_comercial_msg='" . $descontocomercialmsg . "'  WHERE id=" . $idaluno;

    if ($result = mysql_query($query)) {
        $msg = "Desconto atualizado.";
        $status = 0;

        // ATUALIZA O DESCONTO NO ASAAS
        $titulosDoAluno = DB2::select("
            SELECT af.id
            FROM alunos_fichafinanceira af
            JOIN asaas_cobrancas ac ON af.id = ac.id_alunos_fichafinanceira
            JOIN alunos a ON af.idaluno = a.id
            WHERE a.id = $idaluno
            AND af.id_desconto_comercial IS NULL -- Titulo não tem desconto fixado
            AND af.situacao = 0
        ");

        foreach ($titulosDoAluno as $titulo) {
            try {
                $asaas = new Asaas();
                $asaas->alterarCobranca($titulo->id);
            } catch (Exception $e) {
                DB2::update("
                    UPDATE alunos
                    SET desconto_comercial = ?,
                        desconto_comercial_msg = ?
                    WHERE id = ?
                ", [$backup->desconto_comercial, $backup->desconto_comercial_msg, $idaluno]);

                $msg = "Erro ao atualizar desconto no Asaas: " . json_decode($e->getMessage())->errors[0]->description;
                $status = 1;
                break;
            }
        }
    } else {
        $msg = "Erro ao atualizar desconto.";
        $status = 1;
    }
    echo json_encode(['success' => false, 'msg' => $msg]);
} elseif ($action == "buscarAlunosTranferencias") {
    $query = "SELECT alunos.id as idaluno, nome, status, nummatricula,turmamatricula FROM
                             alunos, pessoas, alunos_matriculas WHERE
                             alunos_matriculas.anoletivomatricula=$idanoletivo AND
                             alunos_matriculas.idaluno=alunos.id AND alunos.idpessoa=pessoas.id
                             AND alunos_matriculas.turmamatricula IN (" . rtrim($idturma, ',') . ")
                             ORDER BY nome;";


    $result = mysql_query($query);
    //echo '<input type="hidden" value="'.$query.'">';
    echo "<option value='0'> - Selecione - </option>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        ?>
        <option value="<?= $row['idaluno'] ?>">
            <?php
            echo $row['nome'];
            ?>
        </option>
        <?php
    }
} elseif ($action == "buscaPessoaAluno") {
    $query = "SELECT pessoas.*, alunos.profissao AS aprofissao, date_format(datanascimento,'%d/%m/%Y') as dtnasc FROM pessoas INNER JOIN alunos ON alunos.idpessoa=pessoas.id WHERE alunos.id =" . $alunoid . " LIMIT 1";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);

    // Se a profissão estiver em branco na tabela pessoa usar a profissao da tabela aluno
    if (empty($row['profissao'])) {
        // $query = "SELECT profissao FROM alunos where idpessoa = ".$row['id'];
        // $result = mysql_query($query);
        // $profrow = mysql_fetch_array($result, MYSQL_ASSOC);
        // $row['profissao'] = $profrow['profissao'];
        $row['profissao'] = $row['aprofissao'];
    }

    $queryEmails = "SELECT * FROM emails WHERE idpessoa = " . $row['id'];
    $resultEmails = mysql_query($queryEmails);
    $row['email'] = [];
    while ($rowEmails = mysql_fetch_array($resultEmails, MYSQL_ASSOC)) {
        array_push($row['email'], $rowEmails['email']);
    }

    $queryTels = "SELECT * FROM telefones WHERE idpessoa = " . $row['id'];
    $resultTels = mysql_query($queryTels);
    $row['telefone'] = [];
    while ($rowTels = mysql_fetch_array($resultTels, MYSQL_ASSOC)) {
        array_push($row['telefone'], ['telefone' => $rowTels['telefone'], 'tipo' => $rowTels['idtipotel']]);
    }

    echo json_encode($row, JSON_THROW_ON_ERROR);
} elseif ($action == "atualizarAnuidade") {
    $usuario_suporte = $usuario_admin && $_SESSION['login'] == 'suporte';
    $sucesso = true;
    $bolsapercentual = $bolsa;

    $validaPermissão = $permissoes_alunos[7] & 0x1 || $usuario_suporte;
    $validaCampos = is_numeric($mensalidade) &&
        is_numeric($parcelas)    &&
        is_numeric($bolsapercentual);

    if (!$sucesso = $validaPermissão && $validaCampos) {
        $error_msg = !$validaPermissão ?
            "O usuário não possui permissão." :
            "Os valores de entrada não são numéricos.";
        $error_code = !$validaPermissão ? 403 : 400;
    }

    if ($sucesso) {
        $anuidade = $mensalidade * $parcelas;
        $bolsa = $anuidade * ($bolsapercentual / 100);
        $sucesso = is_numeric($anuidade);

        $query = "UPDATE alunos_matriculas SET qtdparcelas='$parcelas', valorAnuidade='$anuidade', bolsa='$bolsa', bolsapercentual='$bolsapercentual' WHERE id='$matricula_id' AND idaluno='$aluno_id';";
        $sucesso = !!mysql_query($query);
        $error_msg = "Erro ao registrar valor no banco de dados.";
        $debug_msg = "\n[Somente suporte]\nQuery: $query\nErro: " . mysql_error();
        $error_msg .= $debug_msg;
    }

    $msg = $sucesso ? "Anuidade atualizada com sucesso" : "Falha ao atualizar anuidade: $error_msg";
    if ($usuario_suporte) {
        $msg .= $debug_msg;
    }

    $data = compact('anuidade', 'bolsa');
    $response = $sucesso ? compact('msg', 'data') : compact('msg');
    echo json_encode($response, JSON_THROW_ON_ERROR);
    http_response_code($error_code ?: 200);

    salvaLog($_SESSION['id_funcionario'], $action, $sucesso, $parametroscsv, ($usuario_suporte && !$sucesso ? $msg : $msg . $debug_msg), null);
} elseif ($action == "novoLancamentoCaixaRapido") {
    $queryAluno = "SELECT turmamatricula, nummatricula, idunidade FROM alunos_matriculas WHERE nummatricula=" . $nummatricula . " AND idaluno=" . $idaluno;

    $resultAluno = mysql_query($queryAluno);
    $rowAluno = mysql_fetch_array($resultAluno, MYSQL_ASSOC);
    $turmamatricula = $rowAluno['turmamatricula'];
    $nummatricula = $rowAluno['nummatricula'];
    $idunidadematricula = $rowAluno['idunidade'];

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

        if ($debug == 1) {
            echo $queryA . " " . $resultA . " " . $titulofichafinanceira . " " . $idempresa . "<br>";
        }

        $queryTest = "SELECT MAX( CAST(titulo as UNSIGNED)) as titulomax FROM alunos_fichafinanceira, contasbanco, empresas WHERE alunos_fichafinanceira.idcontasbanco=contasbanco.id AND contasbanco.idempresa=empresas.id";
        $resultTest = mysql_query($queryTest);
        $rowTest = mysql_fetch_array($resultTest, MYSQL_ASSOC);
        $titulomax = $rowA['titulomax'];

        if ($titulomax < $titulofichafinanceira) {
            $query1 = "UPDATE empresas SET titulofichafinanceira='" . ($titulofichafinanceira + $qtdeparcelas) . "' WHERE id=" . $idempresa;
            $result1 = mysql_query($query1);

            if ($debug == 1) {
                echo $query1 . " " . $result1 . "<br>";
            }

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


            for ($poi = $parcelainicial; $poi <= $qtdeparcelas; $poi++) {
                if ($matricula == "sim") {
                    $query = "INSERT IGNORE INTO alunos_fichafinanceira(idfuncionario, idaluno, nummatricula, titulo, data1parcela, dataemissao, datavencimento, valor, bolsa, bolsapercentual, desconto, multa, juros, datarecebimento, idcontasbanco, valorrecebido, situacao, numeroloterps, naturezaoperacao, issretido, matricula) VALUES ($idfuncionario,$idaluno, $nummatricula, $titulofichafinanceira, '$data1parcela', '$hoje', '$datavencimento', $valorbrutoparcelas, $descontoparcelas, $descontoparcelaspercentual,0,0,0,'0000-00-00',$idcontasbanco,0, 0, 0, 1, 0, 1);";
                } else {
                    $query = "INSERT IGNORE INTO alunos_fichafinanceira(idfuncionario, idaluno, nummatricula, titulo, data1parcela, dataemissao, datavencimento, valor, bolsa, desconto, multa, juros, datarecebimento, idcontasbanco, valorrecebido, situacao, numeroloterps, naturezaoperacao, issretido, matricula) VALUES ($idfuncionario,$idaluno, $nummatricula, $titulofichafinanceira, '$data1parcela', '$hoje', '$datavencimento', $valorparcelas, 0,0,0,0,'0000-00-00',$idcontasbanco,0, 0, 0, 1, 0, 0);";
                }

                if ($result = mysql_query($query)) {
                    $msg = "Ficha financeira inserida com sucesso. ( Não Unificado ).";
                    $status = 0;
                    $allok = $allok + 1;
                    $idalunos_fichafinanceira = mysql_insert_id();
                } else {
                    $status = 1;
                    $msg = "Erro ao inserir ficha financeira. ( Não Unificado ).";
                    $teveerro = $teveerro + 1;
                }

                $parametroscsv = $idalunos_fichafinanceira . "," . $valorparcelas;
                salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);

                if ($debug == 1) {
                    echo $query . " " . $result . "<br>";
                }

                if ($matricula == "sim") {
                    $query = "INSERT IGNORE INTO alunos_fichaitens (idalunos_fichafinanceira, parcela, totalparcelas, codigo, eventofinanceiro, valor, descontoboleto) VALUES ( $idalunos_fichafinanceira, '$poi', $qtdeparcelas, $codigoeventofinanceiro, '$eventofinanceiro', $valorbrutoparcelas, $descontoboleto);";
                } else {
                    $query = "INSERT IGNORE INTO alunos_fichaitens (idalunos_fichafinanceira, parcela, totalparcelas, codigo, eventofinanceiro, valor, descontoboleto) VALUES ( $idalunos_fichafinanceira, '$poi', $qtdeparcelas, $codigoeventofinanceiro, '$eventofinanceiro', $valorparcelas, $descontoboleto);";
                }

                if ($result = mysql_query($query)) {
                    $allok = $allok + 1;
                } else {
                    $teveerro = $teveerro + 1;
                }

                if ($debug == 1) {
                    echo $query . " " . $result . "<br>";
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
                    $d->setDate($d->format('Y'), $d->format('m'), $diaanterior);
                    $diaanterior = 0;
                }

                if ($d->format('d') > $diainicial) {
                    $d->modify($d->format('Y') . '-' . $d->format('m') . '-' . $diainicial);
                }

                $datavencimento = $d->format('Y-m-d');
            }
        } else {
            echo "red|Erro ao realizar novo lançamento. ";
            $status = 1;
            $msg = "Erro ao realizar novo lançamento.";
        }

        $parametroscsv = $teveerro;
        salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
    } else {
        $teveerro = 1;
    }

    if ($teveerro == 0) {
        echo "green|Lançamento realizado com sucesso.|" . $idaluno . "|" . $turmamatricula . "|" . $idalunos_fichafinanceira . "|" . $qdorecebe . "|" . $idunidadematricula . "|" . $nummatricula;
        $msg = "Lançamento realizado com sucesso.";
        $status = 0;
    } else {
        echo "red|Erro ao realizar novo lançamento.|" . $erro_unifica;
        $status = 1;
        $msg = "Erro ao realizar novo lançamento. " . $msg_unifica;
    }

    $parametroscsv = $teveerro;
    salvaLog($idfuncionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == 'progredirDeTurma') {
    $turmaDestinoId = $_POST['turmaDestino'];
    if (!$turmaDestinoId > 0 || !is_array($alunos)) {
        echo json_encode(["msg" => "A turma ou alunos não podem estar vazios!"]);
        http_response_code(400);
        return;
    }

    $sucesso = true;
    $alunos_lista = "'" . join("','", $alunos) . "'";
    $alunos_dados = [];

    $query = "SELECT idfuncionario, idunidade, idempresa, anoletivomatricula, idaluno, status, datastatus, turmamatricula, nummatricula, datamatricula, qtdparcelas, valorAnuidade, bolsa, bolsapercentual, bolsa_motivo, bolsa_motivo_id, idplanohorario, seguroescolar, recebereajuste, reajustado, datareajuste, escoladestino, obsSituacao, motivoSituacao
    FROM alunos_matriculas WHERE nummatricula IN ($alunos_lista)";
    $res = mysql_query($query);
    while ($row = mysql_fetch_assoc($res)) {
        $alunos_dados[$row['nummatricula']] = $row;
    }

    mysql_query("UPDATE alunos_matriculas SET status = '8' WHERE nummatricula IN ($alunos_lista)");

    foreach ($alunos_dados as $matricula => $aluno) {
        $res = mysql_query("SELECT numerodamatricula FROM unidades WHERE unidades.id='{$aluno['idunidade']}'");
        $row = mysql_fetch_array($res, MYSQL_ASSOC);
        $nummatricula = $row['numerodamatricula'];
        mysql_query("UPDATE unidades SET numerodamatricula=numerodamatricula+1 WHERE unidades.id='{$aluno['idunidade']}'");

        $aluno['nummatricula']   = $nummatricula;
        $aluno['turmamatricula'] = $turmaDestinoId;
        $aluno_campos = "'" . join("','", $aluno) . "'";
        $query =
            "INSERT INTO alunos_matriculas (idfuncionario, idunidade, idempresa, anoletivomatricula, idaluno, status, datastatus, turmamatricula, nummatricula, datamatricula, qtdparcelas, valorAnuidade, bolsa, bolsapercentual, bolsa_motivo, bolsa_motivo_id, idplanohorario, seguroescolar, recebereajuste, reajustado, datareajuste, escoladestino, obsSituacao, motivoSituacao) VALUES ($aluno_campos)";
        mysql_query($query);

        $sucesso = $sucesso && !mysql_error();
    }

    if ($sucesso) {
        echo json_encode(["msg" => count($alunos) . " alunos trocados de turma com sucesso!"], JSON_THROW_ON_ERROR);
        http_response_code(200);
    } else {
        $msg["msg"] = "Erro ao executar o processo!";

        // if($_SESSION['permissao'] == 1) {
        //     $msg["debug"] = "Query: {$res['query']}\nErro: {$res['error']}";
        // }

        echo json_encode($msg, JSON_THROW_ON_ERROR);
        http_response_code(400);
    }
}
