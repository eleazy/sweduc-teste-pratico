<?php

declare(strict_types=1);

namespace App\Academico;

/**
 * Boletim contemplando notas do Ensino Infantil
 */
class BoletimInfantilService extends BoletimService
{
    public function periodos($somenteVisualizacaoLiberada = true)
    {
        $somenteVisualizacaoLiberada = false;

        $periodoLetivoId = $this->matricula->anoletivomatricula;
        $grades = $this->matricula->turma
            ->grades()
            ->where('idanoletivo', $periodoLetivoId)
            ->get();

        $periodos = $grades
            ->flatMap(
                fn($x) => $somenteVisualizacaoLiberada ?
                    $x->periodos()->visualizacaoLiberada()->get() :
                    $x->periodos()->get()
            )
            ->whereBetween('colunaboletim', [41, 100])
            ->sortBy('colunaboletim')
            ->map(fn($x) => [
                'id' => $x->id,
                'nome' => $x->titulo,
                'ordem' => $x->colunaboletim,
                'funcao' => $x->funcao,
            ])
            ->unique();
        return $periodos;
    }

    public function avaliacoes($periodoId)
    {
        $periodoLetivoId = $this->matricula->anoletivomatricula;
        $grades = $this->matricula->turma
            ->grades()
            ->where('idanoletivo', $periodoLetivoId)
            ->with(['medias', 'disciplina'])
            ->get();

        $mediasDaTurma = $grades->flatMap(fn($x) => $x->medias->where('idperiodo', $periodoId));
        $patternAVA = "/#A(\d+)@/";

        $notas = $mediasDaTurma->flatMap(function ($media) use ($patternAVA) {
            preg_match_all($patternAVA, $media->formula, $matches);
            $avaliacaoIds = $matches[1] ?? [];

            if (empty($avaliacaoIds)) {
                return collect();
            }

            return $media
                ->notas()
                ->with('avaliacao')
                ->where('idaluno', $this->matricula->idaluno)
                ->where('idavaliacao', '!=', 0)
                ->whereIn('idavaliacao', $avaliacaoIds)
                ->get();
        });

        $notasF = $notas
            ->map(fn($x) => [
                'id' => $x->id,
                'nota' => $x->nota,
                'avaliacao' => $x->avaliacao->avaliacao,
                'disciplina' => $x->media->grade->disciplina->titulo,
            ]);

        return $notasF;
    }

    public function boletimCompleto($filtrarPeriodos = true)
    {
        $grades = $this->matricula->turma
            ->grades()
            ->where('idanoletivo', $this->matricula->periodoLetivo->id)
            ->with(['disciplina' => fn($x) => $x->normais()->orderBy('numordem')])
            ->has('disciplina')
            ->get();

        $disciplinas = $grades
            ->pluck('disciplina')
            ->unique()
            ->whereBetween('numordem', [1, 100])
            ->sortBy('numordem')
            ->map(function ($disciplina) {
                return [
                    'id' => $disciplina->id,
                    'nome' => $disciplina->titulo,
                    'abreviacao' => $disciplina->abreviacao
                ];
            });

        // O sistema vai procurar por uma disciplina com o nome exato 'FALTAS:' e exibir as faltas do ensino Infantil
        $disciplinaFaltas = collect(['id' => 9999, 'nome' => 'FALTAS:', 'abreviacao' => 'FALTAS:']);
        $disciplinas->push($disciplinaFaltas);

        /** @var \Illuminate\Support\Collection */
        $periodos = $this->periodos($this->matricula->periodoLetivo->id);

        /** @var \Illuminate\Support\Collection */
        $avaliacoes = $periodos->mapWithKeys(function ($periodo) {
            return [$periodo['id'] => $this->avaliacoes($periodo['id'])];
        });

        return [
            'disciplinas' => $disciplinas,
            'periodos' => $periodos,
            'avaliacoes' => $avaliacoes,
        ];
    }
}
