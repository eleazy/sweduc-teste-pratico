<?php

include '../headers.php';
include 'conectar.php';
include_once 'logs.php';
include '../function/ultilidades.func.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/auth/injetaCredenciais.php';
include_once '../lib/QueryBuilder.php';
include '../permissoes.php';

$hoje = date("Y-m-d");
$acoes_get = [
    "agenda",
    "buscaEscolaOrigem",
    "situacoes",
    "todasSituacoes",
    "sumarioDaFicha",
    "buscaAlunoMarketing",
    "receberMeta",
    "deletarMeta",
];
$request = (in_array($_REQUEST['action'], $acoes_get)) ? $_REQUEST : $_POST;

$keys = array_keys($request);
foreach ($keys as $k) {
    ${$k} = $request[$k];
}

$id_funcionario = $_SESSION['id_funcionario'];

/**
 * TODO: Migrar resto do arquivo para
 *
 * src/Marketing/Controller/ProspeccaoController.php
 *
 * Cadastrar movido para
 * ProspeccaoController::cadastrar
 */
if ($action == "apagar") {
    $query = "SELECT COUNT(*) as cnt FROM alunos_prospeccao WHERE idmidia=$id";
    $result = mysql_query($query);
    if ($row['cnt'] == 0) {
        $query = "DELETE FROM midias WHERE id=$id";
        $pegarMidia = "SELECT midia FROM midias WHERE id=$id";
        $resultadoMidia = mysql_query($pegarMidia);
        while ($linhaMidia = mysql_fetch_array($resultadoMidia)) {
            $midiaDeletada = $linhaMidia['midia'];
        }
        $msg = "Mídia: " . $midiaDeletada . " foi removida.";
        $result = mysql_query($query);
        echo "blue|Mídia removida.";

        $status = 0;
    } else {
        echo "red|Erro ao remover mídia. Alguma prospecção faz referência a esta mídia.";
        $msg = "Erro ao remover mídia. Alguma prospecção faz referência a esta mídia.";
        $status = 1;
    }

    $parametroscsv = $id;
    salvaLog($id_funcionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "deletarAluno") {
    $sucesso = $id ? true : false;

    if ($sucesso) {
        $query = "DELETE FROM prospeccao_alunos WHERE id='$id';";
        $queryProspecAluDeletado = "SELECT nome FROM prospeccao_alunos WHERE id=$id";
        $resAlunDel = mysql_query($queryProspecAluDeletado);
        while ($linhaAlun = mysql_fetch_array($resAlunDel)) {
            $AlunDel = $linhaAlun['nome'];
        }
        $sucesso = mysql_query($query) && mysql_affected_rows() > 0 && $sucesso;
    }

    $info = [
        'query' => $query,
        'affected_rows' => mysql_affected_rows(),
        'error' => mysql_error(),
        'errno' => mysql_errno()
    ];

    $msg = ($sucesso) ? "Aluno: " . $AlunDel . " deletado com sucesso" : "Não foi possível deletar o aluno";
    http_response_code($sucesso ? 200 : 403);
    if ($_SESSION['permissao'] == 1) {
        echo json_encode([
            ($sucesso ? 'Sucesso' : 'Erro') => $msg,
            'Info' => $info
        ], JSON_THROW_ON_ERROR);
    } else {
        echo json_encode([($sucesso ? 'Sucesso' : 'Erro') => $msg], JSON_THROW_ON_ERROR);
    }

    // Gerando logs
    salvaLog($id_funcionario, basename(__FILE__), $action, $sucesso, json_encode(['Requisição' => $_REQUEST, 'Info' => $info], JSON_THROW_ON_ERROR), $msg);
} elseif ($action == "cadastrarmidia") {
    $query = "INSERT INTO midias (midia) VALUES  ('$midia');";
    if ($result = mysql_query($query)) {
        echo "blue|Mídia cadastrada.|" . mysql_insert_id();
        $msg = "Mídia $midia cadastrada.";
        $status = 0;
    } else {
        echo "red|Erro ao cadastrar mídia.|0";
        $msg = "Erro ao cadastrar mídia $midia.";
        $status = 1;
    }
    $parametroscsv = $id . ',' . $grupo;
    salvaLog($id_funcionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "apagarmidia") {
    $query = "SELECT COUNT(*) as cnt FROM alunos_prospeccao WHERE idmidia=$id";
    $result = mysql_query($query);
    if ($row['cnt'] == 0) {
        $query = "DELETE FROM midias WHERE id=$id";
        $pegarMidia = "SELECT midia FROM midias WHERE id=$id";
        $resultadoMidia = mysql_query($pegarMidia);
        while ($linhaMidia = mysql_fetch_array($resultadoMidia)) {
            $midiaDeletada = $linhaMidia['midia'];
        }
        $msg = "Mídia: " . $midiaDeletada . " removida.";
        $queryProspecAluDeletado = "SELECT nome FROM prospeccao_alunos WHERE id=$id";
        $resAlunDel = mysql_query($queryProspecAluDeletado);
        while ($linhaAlun = mysql_fetch_array($resAlunDel)) {
            $AlunDel = $linhaAlun['nome'];
        }
        $result = mysql_query($query);
        echo "blue|Mídia removida.";

        $status = 0;
    } else {
        echo "red|Erro ao remover mídia. Alguma prospecção faz referência a esta mídia.";
        $msg = "Erro ao remover mídia. Prospecção: " . $AlunDel . " faz referência a esta mídia.";
        $status = 1;
    }

    $parametroscsv = $id;
    salvaLog($id_funcionario, basename(__FILE__), $action, $status, $parametroscsv, $msg);
} elseif ($action == "salvaCRM") {
    $data = date('Y-m-d H:i', strtotime(dateBrtoEn($data_calendario) . " $hora_calendario"));
    $operação = !$id_crm ? 'cadastrado' : 'atualizado';

    $campos = [
        'id_prospeccao_ficha' => $id_prospeccao_ficha,
        'id_situacao' => $etapa,
        'atendido_por_id_funcionario' => $atendido_por_id_funcionario,
        'agendado_por_id_funcionario' => $id_funcionario,
        'id_mkt_tiporetorno' => $retornotipo,
        'obs' => $observacao_crm,
        'data' => $data,
    ];
    $queryProspecAluDeletado = "SELECT responsavel_nome AS nome FROM prospeccao_fichas WHERE id=$id_prospeccao_ficha ";
    $resAlunDel = mysql_query($queryProspecAluDeletado);
    while ($linhaAlun = mysql_fetch_array($resAlunDel)) {
        $AlunDel = $linhaAlun['nome'];
    }
    if ($id_crm) {
        unset($campos['atendido_por_id_funcionario']);
        unset($campos['agendado_por_id_funcionario']);
        // Se não for admininstrador não pode mudar situação
        if ($_SESSION['permissao'] != 1) {
            unset($campos['id_situacao']);
            unset($campos['data']);
            unset($campos['id_mkt_tiporetorno']);
        }

        $crm_qb = QueryBuilder::on('prospeccao_crm')
            ->update($campos)
            ->where("id='$id_crm'");
    } elseif ($id_prospeccao_ficha) {
        $crm_qb = QueryBuilder::on('prospeccao_crm')->insert($campos);
    }

    if ($crm_qb) {
        $result = $crm_qb->execute();
    } else {
        $result['status'] = false;
    }

    if ($result['status']) {
        $msg = "CRM do responsavel " . $AlunDel . " $operação com sucesso";
        http_response_code(201);
        if ($_SESSION['permissao'] == 1) {
            echo json_encode([
                'Sucesso' => $msg,
                'Info' => $result
            ], JSON_THROW_ON_ERROR);
        } else {
            echo json_encode(['Sucesso' => $msg], JSON_THROW_ON_ERROR);
        }
    } else {
        $msg = "Não foi possível $operação a requisição";
        http_response_code(400);
        if ($_SESSION['permissao'] == 1) {
            echo json_encode([
                'Erro' => $msg,
                'Info' => $result
            ], JSON_THROW_ON_ERROR);
        } else {
            echo json_encode(['Erro' => $msg], JSON_THROW_ON_ERROR);
        }
    }

    salvaLog($id_funcionario, basename(__FILE__), $action, $result['status'], json_encode(['Requisição' => $_REQUEST, 'Query' => $result['query']], JSON_THROW_ON_ERROR), $msg);
} elseif ($action == "removeCRM") {
    $is_admin = $_SESSION['permissao'] == 1;
    $queryProspecAluDeletado = "SELECT responsavel_nome AS nome FROM prospeccao_fichas WHERE id=$id_ficha_prospeccao ";
    $resAlunDel = mysql_query($queryProspecAluDeletado);
    while ($linhaAlun = mysql_fetch_array($resAlunDel)) {
        $AlunDel = $linhaAlun['nome'];
    }
    if ($id_crm && $id_ficha_prospeccao && $is_admin) {
        $subquery = QueryBuilder::on('prospeccao_crm')
            ->select('MIN(id)')
            ->where("id_prospeccao_ficha=$id_ficha_prospeccao")
            ->where("id_situacao=1")
            ->subquery(true);

        $qb = QueryBuilder::on('prospeccao_crm')
            ->where("id=$id_crm")
            ->where("id_prospeccao_ficha=$id_ficha_prospeccao")
            ->where("id != ($subquery)")
            ->delete()->execute();
    }
    $deletado = $qb['result'] && $qb['affected_rows'] > 0;

    $msg_title = $deletado ? 'Sucesso' : 'Erro';
    $response = [];
    $msg = $deletado ? "CRM: Nome do responsavel" . $AlunDel . " removido com sucesso" : "Não foi possível remover CRM";
    $response[$msg_title] = $msg;

    if (!$is_admin) {
        $response_code = 401;
    } elseif (!$deletado) {
        $response_code = 500;
    } else {
        $response_code = 200;
    }

    http_response_code($response_code);

    if ($is_admin) {
        $response['Info'] = $result;
    }
    echo json_encode($response, JSON_THROW_ON_ERROR);

    salvaLog($id_funcionario, basename(__FILE__), $action, $deletado, json_encode(['Requisição' => $_REQUEST, 'Query' => $qb['query']], JSON_THROW_ON_ERROR), $msg);
} elseif ($action == "delProspeccao") {
    $ativo = 0;
    $queryProspecAluDeletado = "SELECT responsavel_nome AS nome FROM prospeccao_fichas WHERE id=$id ";
    $resAlunDel = mysql_query($queryProspecAluDeletado);
    while ($linhaAlun = mysql_fetch_array($resAlunDel)) {
        $AlunDel = $linhaAlun['nome'];
    }
    if ($id) {
        $qb_del_prospeccao = QueryBuilder::on('prospeccao_fichas')
            ->update(compact('ativo'))
            ->where("id='$id'")
            ->execute();
        $sucesso = $qb_del_prospeccao['result'] && $qb_del_prospeccao['affected_rows'] > 0;
        $info = $qb_del_prospeccao;
    }

    $msg = ($sucesso) ? "Ficha de prospecção $id, responsavel " . $AlunDel . " deletada com sucesso" : "Não foi possível deletar a ficha de prospecção $id";
    http_response_code($sucesso ? 200 : 403);
    if ($_SESSION['permissao'] == 1) {
        echo json_encode([
            ($sucesso ? 'Sucesso' : 'Erro') => $msg,
            'Info' => $info
        ], JSON_THROW_ON_ERROR);
    } else {
        echo json_encode([($sucesso ? 'Sucesso' : 'Erro') => $msg], JSON_THROW_ON_ERROR);
    }

    // Gerando logs
    salvaLog($id_funcionario, basename(__FILE__), $action, $sucesso, json_encode(['Requisição' => $_REQUEST, 'Info' => $info], JSON_THROW_ON_ERROR), $msg);
} elseif ($action == "situacoes") {
    $qb_etapas = QueryBuilder::on('prospeccao_situacoes')
        ->left_join('prospeccao_crm', "prospeccao_situacoes.id=prospeccao_crm.id_situacao AND prospeccao_crm.id_prospeccao_ficha = '$id_ficha_prospeccao'")
        ->select(['prospeccao_situacoes.id as sid', 'prospeccao_situacoes.*', 'prospeccao_crm.id as crm_id', 'prospeccao_crm.*'])
        ->order_by(["tipo = 'progresso' DESC", "ordem_logica"])
        ->execute();

    $etapas = [];
    $ultimo_crm_id = null;
    $etapa_atual = null;
    $prox_etap_ordem = null;
    while ($etapa = mysql_fetch_assoc($qb_etapas['result'])) {
        $etapa = array_filter($etapa);
        if ($etapa['sid'] == 1) {
            $etapa['status'] = 'proxima';
        }

        $etapas[] = $etapa;

        if ($id_crm && $id_crm === $etapa['crm_id'] || !$id_crm && $ultimo_crm_id < $etapa['crm_id']) {
            $ultimo_crm_id = $etapa['crm_id'];
            $etapa_atual = $etapa;
            $prox_etap_ordem = $etapa_atual['proxima_situacao_ordem'];
        }
    }

    if (!$prox_etap_ordem && !$ultimo_crm_id) {
        $prox_etap_ordem = 1;
    }

    $proximas_etapas = [];
    array_walk($etapas, function (&$etapa) use ($etapa_atual, $prox_etap_ordem) {
        global $proximas_etapas;
        $menor_que_prox_etapa = $prox_etap_ordem &&
                                $etapa['ordem_logica'] < $prox_etap_ordem;
        $menor_que_etapa_atual = $etapa_atual['ordem_logica']
                              && $etapa['ordem_logica'] < $etapa_atual['ordem_logica'];
        $igual_etapa_atual = $etapa_atual['crm_id'] &&
                             $etapa_atual['crm_id'] == $etapa['crm_id'];

        if ($menor_que_prox_etapa || $menor_que_etapa_atual || $igual_etapa_atual) {
            $etapa['status'] = $etapa['crm_id'] ? 'completo' : 'pulado';
        } elseif ($etapa['ordem_logica'] == $prox_etap_ordem || $etapa['ordem_logica'] == $etapa_atual['ordem_logica'] + 1) {
            $proximas_etapas[] = $etapa;
            $etapa['status'] = 'proxima';
        }
    });

    echo json_encode([
        'etapas' => $etapas,
        'etapaAtual' => $etapa_atual,
        'proximasEtapas' => $proximas_etapas
    ], JSON_THROW_ON_ERROR);
} elseif ($action == "buscaEscolaOrigem") {
    $qb_escolas = QueryBuilder::on('prospeccao_alunos')
        ->select('escola_origem')
        ->where_if($term, "escola_origem LIKE '%$term%'")
        ->group_by('escola_origem')
        ->execute();

    $escolas = [];
    while ($row = mysql_fetch_array($qb_escolas['result'])) {
        $escolas[] = $row['escola_origem'];
    }

    echo json_encode($escolas, JSON_THROW_ON_ERROR);
} elseif ($action == "todasSituacoes") {
    $qb_etapas = QueryBuilder::on('prospeccao_situacoes')->select()->execute();

    $etapas = [];
    while ($etapa = mysql_fetch_assoc($qb_etapas['result'])) {
        $etapas[] = $etapa;
    }

    echo json_encode($etapas, JSON_THROW_ON_ERROR);
} elseif ($action == "agenda") {
    $unidades_permitidas = join(',', $usuario_permissoes['unidades']);
    $visualizar_todos = !!$usuario_permissoes['marketing']['visualizar-todas-prospeccoes'];

    $eventos_qb = QueryBuilder::on('prospeccao_crm')
        ->left_join('prospeccao_fichas', "prospeccao_crm.id_prospeccao_ficha=prospeccao_fichas.id")
        ->left_join('funcionarios', "funcionarios.id=prospeccao_crm.atendido_por_id_funcionario")
        ->select([
            'DATE(data) as data',
            'responsavel_nome AS nome_resp',
            'prospeccao_crm.id as id_prospeccao_crm',
            'prospeccao_fichas.id as id_prospeccao_ficha',
        ])
        ->where("prospeccao_crm.id_situacao='$situacao'")
        ->where_if($start, "data > '$start'")
        ->where_if($end, "data < '$end'")
        ->where("funcionarios.idunidade IN ($unidades_permitidas)")
        ->where_if(!$visualizar_todos, "prospeccao_crm.atendido_por_id_funcionario={$_SESSION['id_funcionario']}")
        ->where_if($unidade, "funcionarios.idunidade = $unidade")
        ->order_by('prospeccao_crm.id DESC')
        ->execute();

    $eventos = [];
    while ($row = mysql_fetch_array($eventos_qb['result'], MYSQL_ASSOC)) {
        $eventos[] = [
            'title' => $row['nome_resp'],
            'start' => $row['data'],
            'ficha' => $row['id_prospeccao_ficha'],
            'crm' => $row['id_prospeccao_crm']
        ];
    }

    echo json_encode($eventos, JSON_THROW_ON_ERROR);
} elseif ($action == "sumarioDaFicha") {
    $fichas_qb = QueryBuilder::on('prospeccao_fichas')->select([
        'prospeccao_fichas.id',
        'prospeccao_fichas.responsavel_nome',
        'prospeccao_fichas.responsavel_telefone',
        'prospeccao_fichas.responsavel_celular',
        'prospeccao_fichas.responsavel_email',
        'prospeccao_fichas.obs_atendimento as observacao',
        'prospeccao_crm.id_situacao',
        'prospeccao_situacoes.nome as situacao',
        'prospeccao_crm.criado_em as situacao_desde',
        'prospeccao_fichas.data_proximo_contato as proximo_contato',
        'p_atendentes.nome as atendente_nome',
        'sem_interesse'
    ])
    ->left_join('prospeccao_alunos', 'prospeccao_fichas.id=prospeccao_alunos.id_prospeccao_ficha')
    ->left_join('prospeccao_crm', 'prospeccao_crm.id_prospeccao_ficha=prospeccao_fichas.id
    AND prospeccao_crm.id=(SELECT pc.id FROM prospeccao_crm as pc WHERE pc.id_prospeccao_ficha=prospeccao_fichas.id ORDER BY pc.id DESC LIMIT 1)')
    ->left_join('funcionarios atendentes', 'atendentes.id=prospeccao_crm.atendido_por_id_funcionario')
    ->left_join('pessoas p_atendentes', 'p_atendentes.id=atendentes.idpessoa')
    ->left_join('prospeccao_situacoes', 'prospeccao_situacoes.id=prospeccao_crm.id_situacao')
    ->where("prospeccao_fichas.id=$id")
    ->limit(1)
    ->execute();

    $alunos_qb = QueryBuilder::on('prospeccao_alunos')->select([
        'prospeccao_alunos.id',
        'prospeccao_alunos.nome',
        'unidade',
        'curso',
        'serie',
        'turno',
        'bolsa',
        'ano_interesse',
        'escola_origem',
        'horarios_altenativos'
    ])
    ->left_join('series', 'series.id=id_serie')
    ->left_join('cursos', 'cursos.id=series.idcurso')
    ->left_join('unidades', 'unidades.id=cursos.idunidade')
    ->left_join('turnos', 'turnos.id=id_turno')
    ->where("id_prospeccao_ficha=$id")
    ->execute();

    $alunos = [];
    while ($row = mysql_fetch_assoc($alunos_qb['result'])) {
        $alunos[] = $row;
    }

    $ficha = mysql_fetch_assoc($fichas_qb['result']);
    $ficha['alunos'] = $alunos;
    echo json_encode($ficha, JSON_THROW_ON_ERROR);
} elseif ($action == 'buscaAlunoMarketing') {
    $buscarAlunos = QueryBuilder::on('prospeccao_alunos')->select()->where("nome='$nome'")->get(null);

    echo $buscarAlunos ? json_encode([
        'id' => $buscarAlunos['id'],
        'nome' => $buscarAlunos['nome'],
        'dt_nasc' => $buscarAlunos['nascido_em'],
        'unidade' => $buscarAlunos['id_unidade'],
        'curso' => $buscarAlunos['id_curso'],
        'serie' => $buscarAlunos['id_serie'],
        'turno' => $buscarAlunos['id_turno'],
        'ano_interesse' => $buscarAlunos['ano_interesse']
    ], JSON_THROW_ON_ERROR) : null;
} elseif ($action == 'receberMeta') {
    $id = $_REQUEST['id'];

    $data = explode('/', $_REQUEST['data']);
    $mes = $data[0];
    $ano = $data[1];
    $unidade_id = $_REQUEST['unidade'];

    $qb = QueryBuilder::on('prospeccao_metas')->select("*, DATE_FORMAT(periodo, '%Y') as ano, DATE_FORMAT(periodo, '%m') as mes");
    if ($id) {
        $qb->where("id=$id");
    } elseif ($ano && $mes && $unidade_id) {
        $qb->where("periodo = '$ano-$mes-01'")->where("unidade_id='$unidade_id'");
    } else {
        echo json_encode(['msg' => 'Não existem parametros suficientes para identificar a meta.']);
        http_response_code(400);
        return;
    }

    $res = $qb->get(null)[0];
    $res['mes'] = str_pad($res['mes'], 2, '0', STR_PAD_LEFT);

    echo json_encode($res, JSON_THROW_ON_ERROR);
} elseif ($action == 'salvarMeta') {
    $data = explode('/', $_REQUEST['data']);
    $mes = $data[0];
    $ano = $data[1];

    $unidade_id = $_REQUEST['unidade'];
    $metaMatriculas = $_REQUEST['metaMatriculas'];
    $metaFaturamento = str_replace(',', '.', str_replace('.', '', $_REQUEST['metaFaturamento']));

    $res = QueryBuilder::on('prospeccao_metas')->insert_or_update([
        'periodo' => "$ano-$mes-01",
        'unidade_id' => $unidade_id,
        'quantidade' => $metaMatriculas,
        'valor' => $metaFaturamento
    ])->execute();

    $response['msg'] = $res['status'] ? "Meta de prospecção salva com sucesso, Meta:" . $metaMatriculas : "Falha ao salvar meta de prospecção";
    if ($_SESSION['login'] == 'suporte') {
        $response['debug'] = $res['query'];
    }
    echo json_encode($response, JSON_THROW_ON_ERROR);
    $res['status'] && http_response_code(201) || http_response_code(400);

    salvaLog($_SESSION['funcionario_id'], basename(__FILE__), $acao, !!$res['status'], json_encode($_REQUEST, JSON_THROW_ON_ERROR), $msg);
} elseif ($action == 'deletarMeta') {
    $id = $_REQUEST['id'];
    $queryProspecAluDeletado = "SELECT quantidade  FROM quantidade WHERE id=$id ";
    $resAlunDel = mysql_query($queryProspecAluDeletado);
    while ($linhaAlun = mysql_fetch_array($resAlunDel)) {
        $AlunDel = $linhaAlun['quantidade'];
    }
     $msg = 'Meta: ' . $AlunDel . ' de prospecção removida com sucesso!';
    if ($id) {
        $res = QueryBuilder::on('prospeccao_metas')->where("id='$id'")->delete()->execute();
        $debug = $res;

        if ($res['status'] && $res['affected_rows'] == 1) {
        } else {
            $msg = 'Falha ao remover meta de prospecção.';
            http_response_code(404);
        }
    } else {
        $msg = "Identificador do recurso não encontrado";
        http_response_code(400);
    }

    $response['msg'] = $msg;

    if ($_SESSION['login'] == 'suporte') {
        $response['debug'] = $debug;
    }

    echo json_encode($response, JSON_THROW_ON_ERROR);
    salvaLog($_SESSION['funcionario_id'], basename(__FILE__), $acao, !!$res['status'], json_encode($_REQUEST, JSON_THROW_ON_ERROR), $msg);
}
