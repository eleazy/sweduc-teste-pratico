<?php

declare(strict_types=1);

namespace App\Academico;

use App\Academico\Boletim\SituacaoFinalDisciplina;
use App\Academico\Model\Matricula;
use App\Academico\Model\Media;
use App\Academico\Model\MediaCalculada;
use App\Academico\Model\Nota;
use App\Model\Core\Configuracao;
use Carbon\Carbon;
use Exception;

class BoletimService
{
    public const DISCIPLINAS_TODAS = 1;
    public const DISCIPLINAS_NORMAIS = 2;
    public const DISCIPLINAS_DIVERSIFICADAS = 4;
    public const DISCIPLINAS_ELETIVAS = 8;

    public const PROVA_FINAL = 'PROVA FINAL';
    public const APROVADO = 'APROVADO';
    public const APROVADO_DEPS = 'APROVADO COM DEPENDENCIAS';
    public const RECUPERACAO = 'RECUPERAÇÃO';
    public const REPROVADO = 'REPROVADO';
    public const INDEFINIDO = 'INDEFINIDO';

    protected Matricula $matricula;
    protected CalculoMediaService $calculadoraMedia;

    /**
     * Inicia serviço de boletim
     *
     * @param Matricula|int $matricula
     */
    public function __construct($matricula, CalculoMediaService $calculadoraMedia = null)
    {
        $this->calculadoraMedia = $calculadoraMedia ?? new CalculoMediaService(Configuracao::chave('notasdecimais'), null);

        if (is_int($matricula)) {
            $matricula = Matricula::findOrFail($matricula);
        }

        if (!($matricula instanceof Matricula)) {
            throw new Exception('Matrícula não identificada');
        }

        $this->matricula = $matricula;
    }

    public function periodos($somenteVisualizacaoLiberada = true)
    {
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
            ->sortBy('colunaboletim')
            ->map(fn ($x) => [
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
            ->with(['medias','disciplina'])
            ->get();

        $mediasDaTurma = $grades->flatMap(fn($x) => $x->medias->where('idperiodo', $periodoId));
        $notas = $mediasDaTurma->flatMap(
            fn($x) => $x
                ->notas()
                ->with('avaliacao')
                ->where('idaluno', $this->matricula->idaluno)
                ->where('idavaliacao', '!=', 0)
                ->get()
        );

        return $notas->map(fn($x) => [
            'id' => $x->id,
            'nota' => $x->nota,
            'avaliacao' => $x->avaliacao->avaliacao,
            'disciplina' => $x->media->grade->disciplina->titulo,
        ]);
    }

    public function boletimCompleto($filtrarPeriodos = true)
    {
        $this->regeneraCacheMedias();

        $grades = $this->matricula->turma
            ->grades()
            ->where('idanoletivo', $this->matricula->periodoLetivo->id)
            ->with(['disciplina' => fn ($x) => $x->normais()->orderBy('numordem')])
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
                $situacao = $disciplina->ignoraNoBoletim == 1 ? self::APROVADO : $sfd->resultado(
                    $mediaAnual,
                    $provaFinal,
                    $recuperacao,
                    $situacaoFinal
                );

                return [
                    'id' => $disciplina->id,
                    'nome' => $disciplina->titulo,
                    'abreviacao' => $disciplina->abreviacao,
                    'situacao' => $situacao,
                ];
            });

        $disciplinasIndefinidas = $disciplinas->where('situacao', self::INDEFINIDO)->count();
        $disciplinasReprovadas = $disciplinas->where('situacao', self::REPROVADO)->count();
        $disciplinasRecuperacao = $disciplinas->where('situacao', self::RECUPERACAO)->count();
        $disciplinasAprovado = $disciplinas->where('situacao', self::APROVADO)->count();
        $disciplinasEmProvaFinal = $disciplinas->where('situacao', self::PROVA_FINAL)->count();

        return [
            'disciplinas' => $disciplinas,
            'periodos' => $this->periodos($filtrarPeriodos),
            'medias' => $mediasMap,
            'situacao' => $this->situacaoMatricula(
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

    /**
     * Calcula resultado das médias do aluno e grava em um sistema de cache
     *
     * @return void
     */
    public function regeneraCacheMedias(): void
    {
        $periodoLetivoId = $this->matricula->anoletivomatricula;

        $ultimaNotaLancada = Nota::where('idaluno', $this->matricula->idaluno)->latest()->first();
        $ultimaNotaAtualizada = Nota::where('idaluno', $this->matricula->idaluno)->latest('updated_at')->first();
        $ultimaMediaLancada = Media::latest()->first();
        $ultimaMediaAtualizada = Media::latest('updated_at')->first();

        // Remove médias cacheadas antes da ultima nota
        $mediasDeletadas = MediaCalculada::where('matricula_id', $this->matricula->id)
            ->where('created_at', '<', Carbon::parse($ultimaMediaLancada->created_at))
            ->when(
                $ultimaMediaAtualizada->updated_at,
                fn ($q) => $q->orWhere(
                    'created_at',
                    '<',
                    Carbon::parse($ultimaMediaAtualizada->updated_at)
                )
            )
            ->orWhere('created_at', '<', Carbon::parse($ultimaNotaLancada->created_at))
            ->when(
                $ultimaNotaAtualizada->updated_at,
                fn ($q) => $q->orWhere(
                    'created_at',
                    '<',
                    Carbon::parse($ultimaNotaAtualizada->updated_at)
                )
            )
            ->delete();

        $mediasCalculadas = MediaCalculada::where('matricula_id', $this->matricula->id)
            ->get();

        if ($mediasCalculadas->isNotEmpty() && $mediasDeletadas == 0) {
            return;
        }

        $grades = $this->matricula->turma
            ->grades()
            ->where('idanoletivo', $periodoLetivoId)
            ->with([
                'medias' => fn ($media) => $media->with(['periodo', 'grade.disciplina', 'notas'])
            ])
            ->get();

        $medias = $grades->flatMap(fn($x) => $x->medias)->filter(
            fn ($x) =>
                $x->periodo &&
                $x->grade &&
                $x->grade->disciplina
        );

        $novasMedias = $medias->filter(fn ($media) => $mediasCalculadas->where([
                'matricula_id' => $this->matricula->id,
                'disciplina_id' => $media->grade->disciplina->id,
                'grade_id' => $media->grade->id,
                'media_id' => $media->id,
                'periodo_id' => $media->periodo->id,
                'disciplina' => $media->grade->disciplina->titulo,
                'periodo' => $media->periodo->titulo,
                'tipoPeriodo' => $media->periodo->funcao,
                'ordemPeriodo' => $media->periodo->colunaboletim,
                'formula' => $media->formula
            ])->first() == null);

        $novasMediasCalculadas = $novasMedias->map(fn ($media) => [
                'matricula_id' => $this->matricula->id,
                'disciplina_id' => $media->grade->disciplina->id,
                'grade_id' => $media->grade->id,
                'media_id' => $media->id,
                'periodo_id' => $media->periodo->id,
                'disciplina' => $media->grade->disciplina->titulo,
                'periodo' => $media->periodo->titulo,
                'tipoPeriodo' => $media->periodo->funcao,
                'ordemPeriodo' => $media->periodo->colunaboletim,
                'formula' => $media->formula,
                'valor' => $this->calculadoraMedia->calcularMedia($this->matricula->idaluno, $media, true),
            ])->toArray();

        MediaCalculada::upsert($novasMediasCalculadas, [
            'matricula_id',
            'disciplina_id',
            'grade_id',
            'media_id',
            'periodo_id',
        ]);
    }

    protected function situacaoMatricula(
        $disciplinasIndefinidas,
        $disciplinasReprovadas,
        $disciplinasRecuperacao,
        $disciplinasAprovado,
        $disciplinasEmProvaFinal,
        $provaFinalHabilitada
    ) {
        $limiteDeRecuperacoes = $this->matricula->turma->serie->limiterecuperacoes;
        $limiteDeDependencias = $this->matricula->turma->serie->dependencias;
        $limiteDeProvasFinais = $this->matricula->turma->serie->limiteprovasfinais;

        $ultrapassaLimiteDeProvasFinais = $limiteDeProvasFinais < $disciplinasEmProvaFinal;
        $ultrapassaLimiteDeRecuperacoes = $limiteDeRecuperacoes < $disciplinasRecuperacao;
        $ultrapassaLimiteDeDependencias = $limiteDeDependencias < $disciplinasReprovadas + $disciplinasRecuperacao;

        /**
         * Tem prova final se diferencia de possui prova final pois o primeiro se trata
         * de disciplinas em prova final e o segundo sobre ter a avaliação do tipo prova final
         */
        $temProvaFinal = $disciplinasEmProvaFinal > 0;
        $temRecuperacao = $disciplinasRecuperacao > 0;
        $temReprovacao = $disciplinasReprovadas > 0;
        $temAprovacao = $disciplinasAprovado > 0;

        if ($disciplinasIndefinidas > 0) {
            return self::INDEFINIDO;
        }

        if ($provaFinalHabilitada && $temProvaFinal && !$ultrapassaLimiteDeProvasFinais) {
            return self::PROVA_FINAL;
        }

        if ($temRecuperacao && !$ultrapassaLimiteDeRecuperacoes) {
            return self::RECUPERACAO;
        }

        if (
            ($temReprovacao || $temRecuperacao) &&
            $ultrapassaLimiteDeDependencias ||
            $ultrapassaLimiteDeProvasFinais
        ) {
            return self::REPROVADO;
        }

        if (
            ($temReprovacao || $temRecuperacao) &&
            $temAprovacao &&
            !$ultrapassaLimiteDeDependencias
        ) {
            return self::APROVADO_DEPS;
        }

        if ($temAprovacao) {
            return self::APROVADO;
        }

        return self::INDEFINIDO;
    }
}
