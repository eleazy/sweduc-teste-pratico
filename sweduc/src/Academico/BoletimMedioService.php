<?php

declare(strict_types=1);

namespace App\Academico;

use App\Academico\Boletim\SituacaoFinalDisciplina;
use App\Academico\Boletim\SituacaoFinalMatricula;

/**
 * Boletim contemplando eletivas e diversificadas
 */
class BoletimMedioService extends BoletimService
{
    public function boletimCompleto($filtrarPeriodos = true)
    {
        $this->regeneraCacheMedias();

        $grades = $this->matricula->turma
            ->grades()
            ->where('idanoletivo', $this->matricula->periodoLetivo->id)
            ->with(['disciplina' => fn ($x) => $x->orderBy('numordem')])
            ->has('disciplina')
            ->get();

        $medias = $this->matricula->mediasCalculadas()
            ->when(
                $filtrarPeriodos,
                fn ($x) => $x->whereHas('periodo', fn($q) => $q->visualizacaoLiberada())
            )
            ->get();

        $mediasMap = $medias->map(fn($media) => [
            'id' => $media->id,
            'periodo_id' => $media->periodo_id,
            'media_id' => $media->media_id,
            'grade_id' => $media->media_id,
            'disciplina_id' => $media->disciplina_id,
            'periodo' => $media->periodo,
            'disciplina' => $media->disciplina,
            'tipo' => $media->tipoPeriodo,
            'ordem' => $media->ordemPeriodo,
            'valor' => $media->valor,
        ]);

        $serie = $this->matricula->turma->serie;
        $sfd = new SituacaoFinalDisciplina(
            $serie->mediaaprovacao,
            $serie->mediaaprovacaorec,
            $serie->mediaaprovacaopf,
            $serie->mediarecuperacao
        );

        $disciplinas = $grades
            ->pluck('disciplina')
            ->unique()
            ->whereBetween('numordem', [1, 100])
            ->sortBy('numordem')
            ->map(function ($disciplina) use ($medias, $sfd) {
                $mediasDaDisciplina = $medias->where('disciplina_id', $disciplina->id);
                $mediaAnual = $mediasDaDisciplina->first(fn($x) => $x->isMediaAnual)->valor ?? '0';
                $provaFinal = $mediasDaDisciplina->first(fn($x) => $x->isProvaFinal)->valor ?? '0';
                $recuperacao = $mediasDaDisciplina->first(fn($x) => $x->isRecuperacao)->valor ?? '0';
                $situacaoFinal = $mediasDaDisciplina->first(fn($x) => $x->isSituacaoFinal)->valor ?? '0';

                return [
                    'id' => $disciplina->id,
                    'nome' => $disciplina->titulo,
                    'abreviacao' => $disciplina->abreviacao,
                    'situacao' => $sfd->resultado(
                        $mediaAnual,
                        $provaFinal,
                        $recuperacao,
                        $situacaoFinal
                    ),
                ];
            });

        $disciplinasIndefinidas = $disciplinas->where('situacao', self::INDEFINIDO)->count();
        $disciplinasReprovadas = $disciplinas->where('situacao', self::REPROVADO)->count();
        $disciplinasRecuperacao = $disciplinas->where('situacao', self::RECUPERACAO)->count();
        $disciplinasAprovado = $disciplinas->where('situacao', self::APROVADO)->count();
        $disciplinasEmProvaFinal = $disciplinas->where('situacao', self::PROVA_FINAL)->count();

        $diversificadas = $grades
            ->pluck('disciplina')
            ->unique()
            ->whereBetween('numordem', [101, 1000])
            ->sortBy('numordem')
            ->map(function ($disciplina) use ($medias) {
                $mediasDaDisciplina = $medias->where('disciplina_id', $disciplina->id);
                $mediaAnual = $mediasDaDisciplina->first(fn($x) => $x->isMediaAnual)->valor ?? null;
                $provaFinal = $mediasDaDisciplina->first(fn($x) => $x->isProvaFinal)->valor ?? null;
                $recuperacao = $mediasDaDisciplina->first(fn($x) => $x->isRecuperacao)->valor ?? null;
                $situacaoFinal = $mediasDaDisciplina->first(fn($x) => $x->isSituacaoFinal)->valor ?? null;

                return [
                    'id' => $disciplina->id,
                    'nome' => $disciplina->titulo,
                    'abreviacao' => $disciplina->abreviacao,
                    'situacao' => '',
                ];
            });

        $eletivas = $grades
            ->pluck('disciplina')
            ->unique()
            ->whereBetween('numordem', [1000, 9999])
            ->sortBy('numordem')
            ->map(function ($disciplina) use ($medias) {
                $mediasDaDisciplina = $medias->where('disciplina_id', $disciplina->id);
                $mediaAnual = $mediasDaDisciplina->first(fn($x) => $x->isMediaAnual)->valor ?? null;
                $provaFinal = $mediasDaDisciplina->first(fn($x) => $x->isProvaFinal)->valor ?? null;
                $recuperacao = intval($mediasDaDisciplina->first(fn($x) => $x->isRecuperacao)->valor ?? null);
                $situacaoFinal = $mediasDaDisciplina->first(fn($x) => $x->isSituacaoFinal)->valor ?? null;

                return [
                    'id' => $disciplina->id,
                    'nome' => $disciplina->titulo,
                    'abreviacao' => $disciplina->abreviacao,
                    'situacao' => '',
                ];
            });

        $sfm = SituacaoFinalMatricula::deSerie($serie);

        return [
            'disciplinas' => $disciplinas,
            'diversificadas' => $diversificadas,
            'eletivas' => $eletivas,
            'periodos' => $this->periodos($filtrarPeriodos),
            'medias' => $mediasMap,
            'situacao' => $sfm->resultado(
                $disciplinasIndefinidas,
                $disciplinasReprovadas,
                $disciplinasRecuperacao,
                $disciplinasAprovado,
                $disciplinasEmProvaFinal,
                true
                // $provaFinalHabilitada
            ),
        ];
    }
}
