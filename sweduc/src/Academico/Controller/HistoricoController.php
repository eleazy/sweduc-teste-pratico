<?php

declare(strict_types=1);

namespace App\Academico\Controller;

use App\Academico\Model\Aluno;
use App\Academico\Model\Historico;
use App\Framework\Http\BaseController;

class HistoricoController extends BaseController
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
}
