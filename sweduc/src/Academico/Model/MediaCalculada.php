<?php

declare(strict_types=1);

namespace App\Academico\Model;

use App\Academico\CalculoMediaService;
use Illuminate\Database\Eloquent\Model;

class MediaCalculada extends Model
{
    protected $table = 'alunos_medias_calculadas';
    protected $guarded = [];
    public $timestamps = false;

    public static function fromMedia(Matricula $matricula, Media $media, CalculoMediaService $cs): MediaCalculada
    {
        $mediaCalculada = new self();
        $mediaCalculada->matricula_id = $matricula->id;
        $mediaCalculada->disciplina_id = $media->grade->disciplina->id;
        $mediaCalculada->grade_id = $media->grade->id;
        $mediaCalculada->media_id = $media->id;
        $mediaCalculada->periodo_id = $media->periodo->id;
        $mediaCalculada->disciplina = $media->grade->disciplina->disciplina;
        $mediaCalculada->periodo = $media->periodo->titulo;
        $mediaCalculada->tipoPeriodo = $media->periodo->funcao;
        $mediaCalculada->ordemPeriodo = $media->periodo->colunaboletim;
        $mediaCalculada->formula = $media->formula;
        $mediaCalculada->valor = $cs->calcularMedia($matricula->aluno->id, $media, true);

        return $mediaCalculada;
    }

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'media_id');
    }

    public function getIsSituacaoFinalAttribute(): bool
    {
        return in_array('situacao-final', explode(',', $this->tipoPeriodo));
    }

    public function getIsRecuperacaoAttribute(): bool
    {
        return in_array('recuperacao', explode(',', $this->tipoPeriodo));
    }

    public function getIsProvaFinalAttribute(): bool
    {
        return in_array('prova-final', explode(',', $this->tipoPeriodo));
    }

    public function getIsMediaAnualAttribute(): bool
    {
        return in_array('media-anual', explode(',', $this->tipoPeriodo));
    }
}
