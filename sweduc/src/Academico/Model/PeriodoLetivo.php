<?php

declare(strict_types=1);

namespace App\Academico\Model;

use App\Framework\Model;

class PeriodoLetivo extends Model implements \Stringable
{
    protected $fillable = [
        'anoletivo',
        'compras',
        'responsaveis',
    ];

    public function scopePublico($query)
    {
        return $query->where('responsaveis', 1);
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'anoletivomatricula');
    }

    public function __toString(): string
    {
        return (string) $this->anoletivo;
    }

    protected $table = 'anoletivo';
}
