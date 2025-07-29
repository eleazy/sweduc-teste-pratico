<?php

declare(strict_types=1);

namespace App\Academico\Controller;

use App\Academico\BoletimService;
use App\Controller\Controller;
use App\Academico\Model\Media;
use App\Academico\Model\Periodo;
use App\Model\Core\Configuracao;
use App\Model\Periodos;
use App\Academico\CalculoMediaService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '../../../../public/helper_notas.php';

class MapaoController extends Controller
{
    protected LoggerInterface $logger;

    public function __construct(ResponseFactoryInterface $responseFactory, LoggerInterface $logger)
    {
        parent::__construct($responseFactory);
        $this->logger = $logger;
    }

    public function notas()
    {
        return $this->platesView('Academico/Mapao/Notas', get_defined_vars());
    }

    public function notasPorDisciplina(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody();

        $avaliacao = $input['avaliacao'];
        $disciplina = $input['disciplina'];
        $idanoletivo = $input['idanoletivo'];
        $idturma = $input['idturma'];
        $parcial = $input['parcial'];
        $periodo = explode("@", $input['periodo']);
        $idperiodo = $periodo[0];
        $nomeperiodo = $periodo[1];
        $configuracoes = $this->getConfiguracoes($idanoletivo, $idturma);
        $notasdecimais = $configuracoes['notasDecimais'];
        $coluna = 0;
        $avaliacoes = explode(',', $avaliacao);
        $disciplinas = explode(',', $disciplina);
        $medias = [];
        $alunos = [];
        $nalunosnotas = [];
        $nalunosacima = [];
        $nalunosabaixo = [];
        $nalunosmenosum = [];
        $nalunos3149 = [];
        $notaAlunos = [];
        $rowdisciplina = [];
        $arrdisc = [];
        $arrdiscid = [];

        // ranges somas de notas
        $somaTotal_0 = 0;
        $somaTotal_1 = 0;
        $somaTotal_2 = 0;
        $somaTotal_3 = 0;
        $quant_alunos = 0;
        $notaSomaTotalGeral = 0;

        $linhas = 0;

        $query  = "SELECT anoletivo FROM anoletivo WHERE id='$idanoletivo'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $anoletivo = $row['anoletivo'];

        $query  = "SELECT
                rodape_aprovacao,
                rodape_recuperacao,
                rodape_intervalo_um,
                rodape_intervalo_dois,
                pontosaprovacao,
                curso,
                serie,
                series.id as serieid,
                turma,
                turno,
                unidade
            FROM
                unidades,
                cursos,
                series,
                turmas,
                turnos
            WHERE turmas.id='$idturma'
            AND cursos.idunidade=unidades.id
            AND turmas.idserie=series.id
            AND series.idcurso=cursos.id
            AND turmas.idturno=turnos.id";

        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        if ($parcial == 1) {
            $rodape_aprovacao = round(($row['rodape_aprovacao'] / 2) - 1);
            $rodape_recuperacao = round(($row['rodape_recuperacao'] / 2) - 1);
            $rodape_intervalo_um = round(($row['rodape_intervalo_um'] / 2) - 1);
            $rodape_intervalo_dois = round(($row['rodape_intervalo_dois'] / 2) - 1);
        } else {
            $rodape_aprovacao = intval($row['rodape_aprovacao']);
            $rodape_recuperacao = intval($row['rodape_recuperacao']);
            $rodape_intervalo_um = intval($row['rodape_intervalo_um']);
            $rodape_intervalo_dois = intval($row['rodape_intervalo_dois']);
        }

        $pontosaprovacao = $row['pontosaprovacao'];
        $razao = $pontosaprovacao / (($rodape_aprovacao > 0) ? $rodape_aprovacao : 1);

        $disciplina_id = explode('@', $disciplinas[0]);
        $queryAval = "SELECT
                an.idmedia,
                an.idavaliacao,
                dc.id,
                dc.disciplina,
                GROUP_CONCAT(DISTINCT concat(av.id,'***',av.avaliacao) SEPARATOR '##') as itemavaliacao
            FROM alunos_notas an
            INNER JOIN medias md ON an.idmedia=md.id
            INNER JOIN avaliacoes av ON an.idavaliacao=av.id
            INNER JOIN grade gd ON md.idgrade=gd.id
            INNER JOIN disciplinas dc ON gd.iddisciplina=dc.id
            INNER JOIN anoletivo a ON a.id = gd.idanoletivo
            WHERE dc.id='{$disciplina_id[0]}'
            AND YEAR(an.datahora) = a.anoletivo
            AND gd.idanoletivo='$idanoletivo'
            AND md.idperiodo='$idperiodo'
            AND gd.idserie='{$row['serieid']}'
            AND gd.idturma = '$idturma'
            GROUP BY dc.disciplina";

        $resultAval = mysql_query($queryAval);

        while ($rowAval = mysql_fetch_array($resultAval, MYSQL_ASSOC)) {
            $listatd = explode('##', $rowAval['itemavaliacao']);
            for ($i = 0; $i < count($listatd); $i++) {
                $aval = explode('***', $listatd[$i]);
                $arrdisc[] = $aval[1];
                $arrdiscid[] = $aval[0];
            }
        }

        foreach ($arrdiscid as $disciplinaId) {
            $nalunosnotas[$disciplinaId] = 0;
            $nalunosacima[$disciplinaId] = 0;
            $nalunosabaixo[$disciplinaId] = 0;
            $nalunosmenosum[$disciplinaId] = 0;
            $nalunos3149[$disciplinaId] = 0;
            $notaAlunos[$disciplinaId] = 0;
        }

        $header = '';
        for ($vd = 0; $vd < count($disciplinas); $vd++) {
            $disciplina = explode("@", $disciplinas[$vd]);
            $iddisciplina = $disciplina[0];
            $nomedisciplina = $disciplina[1];

            $exibe_aval = '';
            for ($va = 0; $va < count($avaliacoes); $va++) {
                $m_avaliacao = explode("@", $avaliacoes[$va]);
                if (!empty($m_avaliacao[2]) && $m_avaliacao[2] == $iddisciplina) {
                    $exibe_aval .= $m_avaliacao[1] . '/';
                }
            }

            $exibe_aval = rtrim($exibe_aval, '/');

            $header .= "<th style='font-size:12px;padding:2px;white-space: nowrap;' colspan='" . count($arrdisc) . "'>";
            $header .= $nomedisciplina . '<br />';
            $header .= "</th>";
            $rowdisciplina[] = $iddisciplina;
        }

        $query  = "SELECT
                pessoas.nome,
                alunos.id
            FROM
                pessoas,
                alunos,
                alunos_matriculas
            WHERE alunos_matriculas.status IN (1,4)
            AND alunos_matriculas.anoletivomatricula = $idanoletivo
            AND alunos_matriculas.turmamatricula = '$idturma'
            AND alunos_matriculas.idaluno=alunos.id
            AND alunos.idpessoa=pessoas.id
            ORDER BY nome ASC";

        $result = mysql_query($query);
        while ($aluno = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $linhas++;
            $notaSomaTotal = 0;
            $aluno['atendimento'] = false;
            $aluno['notas'] = [];
            $coluna = 0;
            $quant_alunos++;

            foreach ($rowdisciplina as $iddisciplina) {
                foreach ($arrdiscid as $k => $disciplinaId) {
                    $output = '';
                    $coluna++;
                    $queryNota = "SELECT
                            group_concat(nota separator ' / ') as nota
                        FROM alunos_notas an
                        INNER JOIN medias md ON an.idmedia=md.id
                        INNER JOIN avaliacoes av ON an.idavaliacao=av.id
                        INNER JOIN grade gd ON md.idgrade=gd.id
                        INNER JOIN disciplinas dc ON gd.iddisciplina=dc.id
                        WHERE dc.id = '$iddisciplina'
                        AND gd.idanoletivo = '$idanoletivo'
                        AND md.idperiodo = '$idperiodo'
                        AND an.idavaliacao = '$disciplinaId'
                        AND an.idaluno = '{$aluno['id']}'";

                    $resultNota = mysql_query($queryNota);
                    $rowNota = mysql_fetch_array($resultNota, MYSQL_ASSOC);
                    $exibenota = ($rowNota['nota'] == '') ? '-' : str_replace('.', ',', $rowNota['nota']);

                    if (!empty($rowNota['nota'])) {
                        if ($rowNota['nota'] < $rodape_recuperacao) {
                            $nalunosmenosum[$disciplinaId]++;
                        }

                        if ($rowNota['nota'] >= $rodape_intervalo_um && $rowNota['nota'] < $rodape_intervalo_dois) {
                            $nalunosabaixo[$disciplinaId]++;
                        }


                        if ($rowNota['nota'] >= $rodape_aprovacao) {
                            $nalunosacima[$disciplinaId]++;
                        }
                    }

                    $notaSomaTotal = $notaSomaTotal + $rowNota['nota'];
                    $notaAlunos[$arrdiscid[$k]] = $notaAlunos[$arrdiscid[$k]] + $rowNota['nota'];
                    $aluno['notas'][] = $exibenota;
                }
            }

            $notaSomaTotalGeral = $notaSomaTotalGeral + $notaSomaTotal;

            if ($notaSomaTotal < $rodape_recuperacao) {
                $aluno['extrastyle'] = 'color:red !important; font-weight:bold;background-color:#F4F4F4 !important;';
                $aluno['atendimento'] = true;
            } elseif ($notaSomaTotal >= $rodape_intervalo_um && $notaSomaTotal < $rodape_intervalo_dois) {
                $aluno['extrastyle'] = 'color:#ff7e00 !important; font-weight:bold;background-color:#F4F4F4 !important;';
            } else {
                $aluno['extrastyle'] = '';
            }

            $aluno['total'] = number_format($notaSomaTotal, $notasdecimais, ',', '');

            if ($notaSomaTotal < $rodape_recuperacao) {
                $somaTotal_0++;
            }

            if ($notaSomaTotal >= $rodape_intervalo_um && $notaSomaTotal < $rodape_intervalo_dois) {
                $somaTotal_1++;
            }

            $alunos[] = $aluno;
        }

        foreach ($arrdiscid as $disciplinaId) {
            try {
                $medias[$disciplinaId] = number_format(($notaAlunos[$disciplinaId] / $linhas), $notasdecimais);
            } catch (\Throwable $th) {
                $medias[$disciplinaId] = 0;
            }
        }

        try {
            $notasTotal = number_format(($notaSomaTotalGeral / $quant_alunos), $notasdecimais, ',', '');
        } catch (\Throwable $th) {
            $notasTotal = 0;
        }

        return $this->platesView('Academico/Mapao/NotasPorDisciplina', [
            'header' => $header,
            'row' => $row,
            'arrdisc' => $arrdisc,
            'arrdiscid' => $arrdiscid,
            'mapaAlunos' => $alunos,
            'coluna' => $coluna,
            'linhas' => $linhas,
            'medias' => $medias,
            'notasTotal' => $notasTotal,
            'nomeperiodo' => $nomeperiodo,
            'anoletivo' => $anoletivo,
            'rodape_aprovacao' => $rodape_aprovacao,
            'rodape_recuperacao' => $rodape_recuperacao,
            'rodape_intervalo_um' => $rodape_intervalo_um,
            'rodape_intervalo_dois' => $rodape_intervalo_dois,
            'somaTotal_0' => $somaTotal_0,
            'somaTotal_1' => $somaTotal_1,
            'somaTotal_2' => $somaTotal_2,
            'somaTotal_3' => $somaTotal_3,
            'nalunosacima' => $nalunosacima,
            'nalunosabaixo' => $nalunosabaixo,
            'nalunos3149' => $nalunos3149,
            'nalunosmenosum' => $nalunosmenosum,
        ]);
    }

    public function notasPorPeriodo(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody();

        // Requisição
        $idturma = $input['idturma'];
        $idanoletivo = $input['idanoletivo'];
        $periodo = $input['periodo'];
        $parcial = $input['parcial'];
        $exibe_medias = filter_var($input['medias'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $exibe_avaliacoes = filter_var($input['avaliacoes'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $disciplina = explode("@", $input['disciplinas'][0])[1];
        $id_disciplina = explode("@", $input['disciplinas'][0])[0];
        $id_periodos = array_map(function ($p) {
            return explode("@", $p)[0];
        }, $periodo);

        $notasdecimais = Configuracao::chave('notasdecimais');
        $calculoMediaService = new CalculoMediaService($notasdecimais, $this->logger);

        $query  = "SELECT
                rodape_aprovacao,
                rodape_recuperacao,
                rodape_intervalo_um,
                rodape_intervalo_dois,
                pontosaprovacao,
                curso,
                serie,
                series.id as serieid,
                turma,
                turno,
                unidade,
                anoletivo
            FROM
                unidades,
                cursos,
                series,
                turmas,
                turnos,
                anoletivo
            WHERE
                turmas.id='$idturma' AND
                cursos.idunidade = unidades.id AND
                turmas.idserie = series.id AND
                series.idcurso = cursos.id AND
                turmas.idturno = turnos.id AND
                anoletivo.id = '$idanoletivo'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        $unidade = $row['unidade'];
        $anoletivo = $row['anoletivo'];
        $curso = $row['curso'];
        $serie = $row['serie'];
        $id_serie = $row['serieid'];
        $turma = $row['turma'];
        $turno = $row['turno'];

        $nome_rel = "";
        if ($parcial == 1) {
            $rodape_aprovacao = round(($row['rodape_aprovacao'] / 2) - 1);
            $rodape_recuperacao = round(($row['rodape_recuperacao'] / 2) - 1);
            $rodape_intervalo_um = round(($row['rodape_intervalo_um'] / 2) - 1);
            $rodape_intervalo_dois = round(($row['rodape_intervalo_dois'] / 2) - 1);
            $nome_rel = "(PARCIAL)";
        } else {
            $rodape_aprovacao = $row['rodape_aprovacao'];
            $rodape_recuperacao = $row['rodape_recuperacao'];
            $rodape_intervalo_um = intval($row['rodape_intervalo_um']);
            $rodape_intervalo_dois = intval($row['rodape_intervalo_dois']);
        }

        $pontosaprovacao = $row['pontosaprovacao'];
        $razao = $pontosaprovacao / (($rodape_aprovacao > 0) ? $rodape_aprovacao : 1);

        $periodos = implode(',', $id_periodos);
        $query_notas = "SELECT
            pessoas.nome,
            alunos.id as idaluno,
            disciplinas.id as iddisciplina,
            disciplinas.abreviacao as disciplina,
            medias.id as idmedia,
            periodos.id as idperiodo,
            periodos.periodo,
            avaliacoes.id as idavaliacao,
            avaliacoes.avaliacao,
            alunos_notas.nota
        FROM
            turmas
            JOIN grade ON (grade.idturma=turmas.id AND grade.idserie=turmas.idserie)
            JOIN anoletivo ON grade.idanoletivo=anoletivo.id
            JOIN disciplinas ON grade.iddisciplina=disciplinas.id
            LEFT JOIN alunos_matriculas ON alunos_matriculas.turmamatricula=turmas.id AND alunos_matriculas.anoletivomatricula=anoletivo.id
            JOIN alunos ON alunos.id=alunos_matriculas.idaluno
            JOIN pessoas ON alunos.idpessoa=pessoas.id

            JOIN periodos ON periodos.id IN ($periodos)
            LEFT JOIN disciplinas_avaliacoes ON grade.id=disciplinas_avaliacoes.idgrade AND periodos.id = disciplinas_avaliacoes.idperiodo
            LEFT JOIN avaliacoes ON avaliacoes.id=disciplinas_avaliacoes.idavaliacao

            JOIN medias ON grade.id=medias.idgrade AND periodos.id=medias.idperiodo
            LEFT JOIN alunos_notas ON medias.id=alunos_notas.idmedia AND
                (alunos.id=alunos_notas.idaluno) AND
                avaliacoes.id=alunos_notas.idavaliacao
        WHERE
            -- Constantes
            turmas.id=$idturma AND
            anoletivo.id=$idanoletivo AND
            disciplinas.id=$id_disciplina AND
            alunos_matriculas.status IN (1,4) AND
            periodos.id IN ($periodos)
        ORDER BY periodos.colunaboletim, disciplinas_avaliacoes.id, pessoas.nome";
        $result = mysql_query($query_notas);

        $disciplinas = [];
        $notas = [];
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            // Pessoa -> Disciplina -> Período -> Média & Avaliações
            if ($row['nome'] !== null) {
                $notas[$row['nome']]['id'] = $row['idaluno'];

                if ($row['idmedia'] != null) {
                    $notas[$row['nome']][$row['disciplina']][$row['periodo']]['media'] = $row['idmedia'];
                    $notas[$row['nome']][$row['disciplina']][$row['periodo']]['mediaDisplay'] =
                        $calculoMediaService->calcularMedia((int) $row['idaluno'], Media::find($row['idmedia']));
                }

                $notas[$row['nome']][$row['disciplina']][$row['periodo']]['avaliacoes'][$row['avaliacao']]['nota'] = $row['nota'];
            }

            if ($row['avaliacao'] !== null) {
                $disciplinas[$row['disciplina']][$row['periodo']]['avaliacoes'][$row['avaliacao']] = $row['avaliacao'];
            } else {
                $disciplinas[$row['disciplina']][$row['periodo']]['avaliacoes'] = null;
            }
        }

        return $this->platesView('Academico/Mapao/NotasPorPeriodo', compact(
            'disciplinas',
            'idturma',
            'idanoletivo',
            'anoletivo',
            'turma',
            'exibe_medias',
            'exibe_avaliacoes',
            'unidade',
            'curso',
            'serie',
            'turno',
            'disciplina',
            'notas',
            'notasdecimais'
        ));
    }

    public function notasPorDisciplinaV2(ServerRequestInterface $request): ResponseInterface
    {
        return $this->platesView('Academico/Mapao/NotasPorDisciplina2', get_defined_vars());
    }

    public function notasV2(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody();

        // Requisição
        $idturma = $input['idturma'];
        $idanoletivo = $input['idanoletivo'];
        $parcial = $input['parcial'];
        $exibe_medias = filter_var($input['medias'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $exibe_avaliacoes = filter_var($input['avaliacoes'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $periodo = explode("@", $input['periodo'][0])[1];

        $notasdecimais = Configuracao::chave('notasdecimais');
        $calculoMediaService = new CalculoMediaService($notasdecimais, $this->logger);

        $query  = "SELECT
                rodape_aprovacao,
                rodape_recuperacao,
                rodape_intervalo_um,
                rodape_intervalo_dois,
                pontosaprovacao,
                curso,
                serie,
                series.id as serieid,
                turma,
                turno,
                unidade,
                anoletivo
            FROM
                unidades,
                cursos,
                series,
                turmas,
                turnos,
                anoletivo
            WHERE
                turmas.id='$idturma' AND
                cursos.idunidade = unidades.id AND
                turmas.idserie = series.id AND
                series.idcurso = cursos.id AND
                turmas.idturno = turnos.id AND
                anoletivo.id = '$idanoletivo'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);

        $unidade = $row['unidade'];
        $anoletivo = $row['anoletivo'];
        $curso = $row['curso'];
        $serie = $row['serie'];
        $id_serie = $row['serieid'];
        $turma = $row['turma'];
        $turno = $row['turno'];

        $nome_rel = "";
        if ($parcial == 1) {
            $rodape_aprovacao = round(($row['rodape_aprovacao'] / 2) - 1);
            $rodape_recuperacao = round(($row['rodape_recuperacao'] / 2) - 1);
            $rodape_intervalo_um = round(($row['rodape_intervalo_um'] / 2) - 1);
            $rodape_intervalo_dois = round(($row['rodape_intervalo_dois'] / 2) - 1);
            $nome_rel = "(PARCIAL)";
        } else {
            $rodape_aprovacao = $row['rodape_aprovacao'];
            $rodape_recuperacao = $row['rodape_recuperacao'];
            $rodape_intervalo_um = intval($row['rodape_intervalo_um']);
            $rodape_intervalo_dois = intval($row['rodape_intervalo_dois']);
        }

        $pontosaprovacao = $row['pontosaprovacao'];
        $razao = $pontosaprovacao / (($rodape_aprovacao > 0) ? $rodape_aprovacao : 1);

        $periodosIds = implode(',', array_map(function ($p) {
            return explode("@", $p)[0];
        }, $input['periodo']));

        $disciplinasIds = implode(',', array_map(function ($p) {
            return explode("@", $p)[0];
        }, $input['disciplinas']));

        $query_notas = "SELECT
            pessoas.nome,
            alunos.id as idaluno,
            disciplinas.id as iddisciplina,
            disciplinas.abreviacao as disciplina,
            medias.id as idmedia,
            periodos.id as idperiodo,
            periodos.periodo,
            avaliacoes.id as idavaliacao,
            avaliacoes.avaliacao,
            alunos_notas.nota
        FROM
            turmas
            JOIN grade ON (grade.idturma=turmas.id AND grade.idserie=turmas.idserie)
            JOIN anoletivo ON grade.idanoletivo=anoletivo.id
            JOIN disciplinas ON grade.iddisciplina=disciplinas.id
            LEFT JOIN alunos_matriculas ON alunos_matriculas.turmamatricula=turmas.id AND alunos_matriculas.anoletivomatricula=anoletivo.id
            JOIN alunos ON alunos.id=alunos_matriculas.idaluno
            JOIN pessoas ON alunos.idpessoa=pessoas.id

            JOIN periodos ON periodos.id IN ($periodosIds)
            LEFT JOIN disciplinas_avaliacoes ON grade.id=disciplinas_avaliacoes.idgrade AND periodos.id = disciplinas_avaliacoes.idperiodo
            LEFT JOIN avaliacoes ON avaliacoes.id=disciplinas_avaliacoes.idavaliacao

            JOIN medias ON grade.id=medias.idgrade AND periodos.id=medias.idperiodo
            LEFT JOIN alunos_notas ON medias.id=alunos_notas.idmedia AND
                (alunos.id=alunos_notas.idaluno) AND
                avaliacoes.id=alunos_notas.idavaliacao
        WHERE
            -- Constantes
            turmas.id=$idturma AND
            anoletivo.id=$idanoletivo AND
            alunos_matriculas.status IN (1,4) AND
            disciplinas.id IN ($disciplinasIds) AND
            periodos.id IN ($periodosIds)
        ORDER BY periodos.colunaboletim, avaliacoes.avaliacao, disciplinas_avaliacoes.id, pessoas.nome";
        $result = mysql_query($query_notas);

        $disciplinas = [];
        $notas = [];
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            // Pessoa -> Disciplina -> Período -> Média & Avaliações
            if ($row['nome'] !== null) {
                $notas[$row['nome']]['id'] = $row['idaluno'];

                if ($row['idmedia'] != null) {
                    $notas[$row['nome']][$row['disciplina']][$row['periodo']]['media'] = $row['idmedia'];
                    $notas[$row['nome']][$row['disciplina']][$row['periodo']]['mediaDisplay'] =
                        $calculoMediaService->calcularMedia((int) $row['idaluno'], Media::find($row['idmedia']), false, false);
                }

                $notas[$row['nome']][$row['disciplina']][$row['periodo']]['avaliacoes'][$row['avaliacao']]['nota'] = $row['nota'];
            }

            if ($row['avaliacao'] !== null) {
                $disciplinas[$row['disciplina']][$row['periodo']]['avaliacoes'][$row['avaliacao']] = $row['avaliacao'];
            } else {
                $disciplinas[$row['disciplina']][$row['periodo']]['avaliacoes'] = null;
            }
        }

        if (count($input['periodo']) == 1) {
            return $this->platesView('Academico/Mapao/Notas2', compact(
                'disciplinas',
                'idturma',
                'idanoletivo',
                'anoletivo',
                'turma',
                'exibe_medias',
                'exibe_avaliacoes',
                'unidade',
                'curso',
                'serie',
                'turno',
                'disciplina',
                'periodo',
                'notas',
                'notasdecimais'
            ));
        } else {
            $periodosNomes = implode(', ', array_map(function ($p) {
                return explode("@", $p)[1];
            }, $input['periodo']));
            $table_vertical = $input['table_vertical'] == "on" ?? false;

            return $this->platesView('Academico/Mapao/NotasCompleto', compact(
                'disciplinas',
                'idturma',
                'idanoletivo',
                'anoletivo',
                'turma',
                'exibe_medias',
                'exibe_avaliacoes',
                'unidade',
                'curso',
                'serie',
                'turno',
                'disciplina',
                'periodosNomes',
                'notas',
                'notasdecimais',
                'table_vertical'
            ));
        }
    }

    public function situacao(ServerRequestInterface $request)
    {
        $input = $request->getParsedBody();

        // Requisição
        $idturma = filter_var($input['idturma'], FILTER_VALIDATE_INT);
        $idanoletivo = filter_var($input['idanoletivo'], FILTER_VALIDATE_INT);

        $configuracoes = $this->getConfiguracoes($idanoletivo, $idturma);
        $anoletivo = $configuracoes['ano_letivo'];

        $query = "SELECT
                disciplinas.id
            FROM grade
            INNER JOIN disciplinas ON grade.iddisciplina = disciplinas.id
            WHERE numordem > 0
            AND numordem < 100
            AND grade.idanoletivo = {$idanoletivo}
            AND grade.idturma = {$idturma}
            GROUP BY grade.iddisciplina
            ORDER BY disciplinas.numordem;";

        $result = mysql_query($query);
        $disciplinas = [];
        while ($row = mysql_fetch_assoc($result)) {
            $disciplinas[] = $row['id'];
        }

        $query = "SELECT
            unidade,
            curso,
            serie,
            turma,
            turno
        FROM unidades
        INNER JOIN cursos ON (unidades.id = cursos.idunidade)
        INNER JOIN series ON (series.idcurso = cursos.id)
        INNER JOIN turmas ON (turmas.idserie = series.id)
        INNER JOIN turnos ON (turmas.idturno = turnos.id)
        WHERE turmas.id={$idturma}";
        $result = mysql_query($query);
        $turma = mysql_fetch_array($result, MYSQL_ASSOC);

        $query1aa = "SELECT
            pessoas.nome,
            alunos.id as aid,
            alunos_matriculas.id as amid
        FROM alunos
        INNER JOIN alunos_matriculas ON (alunos_matriculas.idaluno = alunos.id)
        INNER JOIN pessoas ON (alunos.idpessoa = pessoas.id)
        WHERE alunos_matriculas.status IN (1,4)
        AND alunos_matriculas.anoletivomatricula={$idanoletivo}
        AND alunos_matriculas.turmamatricula={$idturma}
        ORDER BY pessoas.nome;";
        $result1aa = mysql_query($query1aa);
        $alunos = [];
        while ($row1aa = mysql_fetch_array($result1aa, MYSQL_ASSOC)) {
            $alunos[] = $row1aa + [
                'idanoletivo' => $idanoletivo,
                'idturma' => $idturma,
                'situacao' => $this->situacaoIndividual(
                    $disciplinas,
                    $row1aa['nome'],
                    (int) $row1aa['amid'],
                ),
            ];
        }

        return $this->platesView('Academico/Mapao/Situacao', compact(
            'disciplinas',
            'idturma',
            'idanoletivo',
            'anoletivo',
            'turma',
            'alunos',
        ));
    }

    protected function situacaoIndividual(array $disciplinas, string $nome, int $matriculaId)
    {
        $resultado = "<td>{$nome}</td>";

        $boletimNovo = new BoletimService($matriculaId);
        $boletimCalculado = $boletimNovo->boletimCompleto();

        foreach ($disciplinas as $disciplina) {
            $media = $boletimCalculado['disciplinas']->where('id', $disciplina)->first();
            $color = $this->resultado($media['situacao']) != "APR" ? 'red' : '';
            $resultado .= "<td class='{$color}'>";
            $resultado .= $this->resultado($media['situacao']);
            $resultado .= "</td>";
        }

        $msgfinal = '';
        $msgfinal = $boletimCalculado['situacao'];
        $color = ($msgfinal !== BoletimService::APROVADO) ? 'red' : '';
        $resultado .= "<td class='{$color}'>" . $this->resultado($msgfinal) . "</td>";

        return $resultado;
    }

    public function resultado($msgfinal)
    {
        switch ($msgfinal) {
            case BoletimService::PROVA_FINAL:
                $msgfinal = 'PF';
                break;

            case BoletimService::RECUPERACAO:
                $msgfinal = 'REC';
                break;

            case BoletimService::REPROVADO:
                $msgfinal = 'REP';
                break;

            case BoletimService::APROVADO:
                $msgfinal = 'APR';
                break;
        }

        return $msgfinal;
    }

    private function getConfiguracoes(
        $idanoletivo,
        $idturma
    ) {
        $cliente = $_ENV['CLIENTE'];
        $notasDecimais = Configuracao::chave('notasdecimais') ?? 1;

        $ano_letivo = getAnoLetivo($idanoletivo);
        $tipoperiodo = getTipoPeriodoPorTurma($idturma);
        $mudancaperiodo = getAnoMudanca($cliente);
        $tipoDePeriodo = ($tipoperiodo == "3" && $ano_letivo > $mudancaperiodo) ? Periodo::TRIMESTRE : Periodo::BIMESTRE;
        $indice_boletim = ($tipoperiodo == "3" && $ano_letivo > $mudancaperiodo) ? " colunaboletim between 41 and 100 " : " colunaboletim between 1 and 40 ";

        $periodo = new Periodos();
        $periodos = $periodo->buscarPeriodosMes(13, $indice_boletim);
        $possui_provafinal = count(array_filter($periodos, function ($periodo) {
            return $periodo['provafinal'];
        })) > 0;

        return compact(
            'notasDecimais',
            'idturma',
            'idanoletivo',
            'ano_letivo',
            'tipoDePeriodo',
            'tipoperiodo',
            'mudancaperiodo',
            'indice_boletim',
            'possui_provafinal',
        );
    }
}
