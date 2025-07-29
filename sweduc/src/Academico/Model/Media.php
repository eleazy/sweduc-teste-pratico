<?php

declare(strict_types=1);

namespace App\Academico\Model;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'medias';
    public $timestamps = false;

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'idgrade');
    }

    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'idperiodo');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'idmedia');
    }

    public function alunos()
    {
        return $this->belongsToMany(Aluno::class, 'alunos_notas', 'idaluno', 'idaluno');
    }

    public function getIsSituacaoFinalAttribute(): bool
    {
        return (bool) $this->periodo->situacaofinalanual;
    }

    public function getIsRecuperacaoAttribute(): bool
    {
        return (bool) $this->periodo->recuperacao;
    }

    public function getIsProvaFinalAttribute(): bool
    {
        return (bool) $this->periodo->provafinal;
    }

    public function getIsMediaAnualAttribute(): bool
    {
        return (bool) $this->periodo->mediaanual;
    }
}
