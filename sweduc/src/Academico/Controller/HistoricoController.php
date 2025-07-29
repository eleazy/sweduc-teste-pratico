<?php

declare(strict_types=1);

namespace App\Academico\Controller;

use App\Academico\Model\Aluno;
use App\Academico\Model\Historico;
use App\Controller\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as DB;

class HistoricoController extends Controller
{
    public function salvar()
    {
        $dados = $_POST;

        /**
         * Os dados de históricos são divididos em 3 partes:
         * 1. Dados de série (replicada em cada disciplina do alunos_historico)
         * 2. Dados de disciplinas (tabela alunos_historico)
         * 3. Dados de observações (tabela de alunos)
         */

        // Dados de observações
        Aluno::where('id', $dados['idaluno'])
            ->update([
                'obs_fundamental' => $dados['obs_fundamental'],
                'obs_medio' => $dados['obs_medio'],
                'obs_individual' => $dados['obs_individual']
            ]);

        // Faz carregamento dos dados do histórico
        $historico = Historico::where('idaluno', $dados['idaluno'])->get();

        foreach ($dados['disciplina'] as $disciplina => $dadosDisciplina) {
            foreach ($dadosDisciplina['notas'] as $curso => $series) {
                foreach ($series as $serie => $valores) {
                    $dadosSerie = $dados['curso'][$curso][$serie];

                    $nota = $historico
                        ->where('idaluno', $dados['idaluno'])
                        ->where('nomealuno', $dados['nomealuno'])
                        ->where('serie', $serie)
                        ->where('disciplina', $disciplina);
                    //->filter(fn($item) => !is_null($item->media));

                    $nota = $nota->first() ?? new Historico([
                        'idaluno' => $dados['idaluno'],
                        'nomealuno' => $dados['nomealuno'],
                        'serie' => $serie,
                        'disciplina' => $disciplina,
                    ]);

                    $nota->fill($dadosSerie);

                    $nota->curso = $curso;
                    $nota->media = $valores['nota'] === '' ? null : $valores['nota'];
                    $nota->cargahoraria = $valores['ch'] === '' ? null : $valores['ch'];
                    $nota->ordem = $dadosDisciplina['ordem'];

                    $nota->save();
                }
            }
        }

        return $this->jsonResponse([
            'mensagem' => 'Histórico alterado com sucesso',
        ]);
    }

    public function cadastrar(ServerRequestInterface $request)
    {
        $body = $request->getParsedBody();
        $idaluno = $body['idaluno'] ?? null;

        $alunoModel = Aluno::find($idaluno);

        $cursosComHistorico = DB::select('SELECT * FROM cursos where historico > 0 order by curso');

        $dadosAutocomplete = $this->pegaDadosAutocomplete();

        $allSeries = DB::table('series')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('idcurso');

        $historicoRaw = DB::table('alunos_historico')
            ->where('idaluno', $idaluno)
            ->get();

        $historicoBySerie = $historicoRaw->groupBy('serie');
        $historicoByDisciplinaSerie = $historicoRaw->groupBy(function ($item) {
            return $item->disciplina . '-' . $item->serie;
        });

        $dados = [];
        // monta estrutura com os dados necessarios
        foreach ($cursosComHistorico as $curso) {
            $seriesDoCurso = $allSeries[$curso->id] ?? collect();
            $cursoIndex = [
                'curso' => $curso->curso,
                'id' => $curso->id,
                'series' => [],
                'disciplinas' => [],
            ];

            foreach ($seriesDoCurso as $serie) {
                $historicoSerie = $historicoBySerie[$serie->serie][0] ?? null;
                $cursoIndex['series'][] = [
                    'serie' => $serie->serie,
                    'id' => $serie->id,
                    'ano' => $historicoSerie->ano ?? '',
                    'situacao' => $historicoSerie->situacao ?? '',
                    'escola' => $historicoSerie->escola ?? '',
                    'local' => $historicoSerie->local ?? '',
                    'carga_horaria_total' => $historicoSerie->carga_horaria_total ?? '',
                    'frequencia' => $historicoSerie->frequencia ?? '',
                ];
            }

            $disciplinas = $this->buscarDisciplina($curso->id, $idaluno);
            foreach ($disciplinas as $disciplinaNome) {
                $notasPorSerie = [];
                foreach ($cursoIndex['series'] as $serieData) {
                    $notas = $historicoByDisciplinaSerie[$disciplinaNome . '-' . $serieData['serie']][0] ?? null;
                    $notasPorSerie[] = [
                        'serie' => $serieData['serie'],
                        'media' => $notas->media ?? '',
                        'cargahoraria' => $notas->cargahoraria ?? '',
                    ];
                }
                $cursoIndex['disciplinas'][] = [
                    'disciplina' => $disciplinaNome,
                    'notas' => $notasPorSerie,
                ];
            }

            $dados[] = $cursoIndex;
        }

        return $this->platesView('Academico/Historico/Cadastrar', [
            'aluno' => $alunoModel,
            'dados' => $dados,
            'situacaocadastradas' => $dadosAutocomplete['situacaocadastradas'],
            'localcadastradas' => $dadosAutocomplete['localcadastradas'],
            'escolascadastradas' => $dadosAutocomplete['escolascadastradas'],
            'disciplinacadastradas' => $dadosAutocomplete['disciplinacadastradas'],
            'seriecadastradas' => $dadosAutocomplete['seriecadastradas'],
        ]);
    }

    private function buscarDisciplina($idCurso, $idaluno)
    {
        $disciplinas = DB::select('
            SELECT DISTINCT disciplina FROM (
                SELECT d.abreviacao AS disciplina, d.numordem
                FROM grade g
                JOIN disciplinas d ON g.iddisciplina = d.id
                JOIN series s ON g.idserie = s.id
                WHERE s.idcurso = ? AND d.numordem > 0 AND d.numordem < 100

                UNION

                SELECT h.disciplina, NULL AS numordem
                FROM alunos_historico h
                WHERE h.idaluno = ?
                AND h.serie IN (
                    SELECT s.serie FROM series s WHERE s.idcurso = ?
                )
            ) AS combined
            ORDER BY numordem IS NULL, numordem
        ', [$idCurso, $idaluno, $idCurso]);

        return array_map(fn($row) => $row->disciplina, $disciplinas);
    }

    private function pegaDadosAutocomplete(): array
    {
        // Situacoes cadastradas
        $situacaocadastradas = DB::table('alunos_historico')
            ->select('situacao')
            ->distinct()
            ->orderBy('situacao', 'asc')
            ->whereNotNull('situacao')
            ->pluck('situacao')
            ->filter() // removes empty strings/nulls
            ->map(function ($item) {
                return "'" . addslashes($item) . "'";
            })
            ->implode(',');

        // Locais cadastrados
        $localcadastradas = DB::table('alunos_historico')
            ->select('local')
            ->distinct()
            ->orderBy('local', 'asc')
            ->whereNotNull('local')
            ->pluck('local')
            ->filter()
            ->map(function ($item) {
                return "'" . addslashes($item) . "'";
            })
            ->implode(',');

        // Escolas cadastradas
        $escolascadastradas = DB::table('alunos_historico')
            ->select('escola')
            ->groupBy('escola') // DISTINCT works the same here
            ->orderBy('escola', 'asc')
            ->whereNotNull('escola')
            ->pluck('escola')
            ->filter()
            ->map(function ($item) {
                return "'" . addslashes($item) . "'";
            })
            ->implode(',');

        // Disciplinas cadastradas
        $disciplinacadastradas = DB::table('alunos_historico')
            ->select('disciplina')
            ->distinct()
            ->orderBy('disciplina', 'asc')
            ->whereNotNull('disciplina')
            ->pluck('disciplina')
            ->filter()
            ->map(function ($item) {
                return "'" . addslashes($item) . "'";
            })
            ->implode(',');

        // Séries cadastradas
        $seriecadastradas = DB::table('alunos_historico')
            ->select('serie')
            ->distinct()
            ->orderBy('serie', 'asc')
            ->whereNotNull('serie')
            ->pluck('serie')
            ->filter()
            ->map(function ($item) {
                return "'" . addslashes($item) . "'";
            })
            ->implode(',');

        return [
            'situacaocadastradas' => $situacaocadastradas,
            'localcadastradas' => $localcadastradas,
            'escolascadastradas' => $escolascadastradas,
            'disciplinacadastradas' => $disciplinacadastradas,
            'seriecadastradas' => $seriecadastradas,
        ];
    }
}
