<?php

declare(strict_types=1);

namespace App\Model\Core;

use App\Academico\Factory\UnidadeFactory;
use App\Academico\Model\Curso;
use App\Framework\Factory;
use App\Framework\Model;
use App\Scopes\AtivoScope;

class Unidade extends Model
{
    protected $fillable = ['unidade'];
    public $timestamps = false;

    protected static function booted()
    {
        static::addGlobalScope(
            new AtivoScope()
        );
    }

    /**
     * Incrementa número de matrícula e retorna número único de matrícula
     */
    public function numeroDeMatricula(bool $incrementar = true): int
    {
        if ($incrementar) {
            $this->increment('numerodamatricula');
        }

        return $this->numerodamatricula;
    }

    public function empresas()
    {
        return $this->belongsToMany(\App\Model\Core\Unidade::class, 'unidades_empresas', 'idempresa', 'idunidade');
    }

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'idunidade');
    }

    protected static function newFactory(): Factory
    {
        return UnidadeFactory::new();
    }
}
