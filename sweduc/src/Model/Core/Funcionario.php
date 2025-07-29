<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    protected $attributes = [
        'idunidade' => 0,
        'iddepartamento' => 0,
        'professor' => false,
        'numeroprof' => 0,
        'pis' => '',
        'grauescolar' => '',
        'formacaoescolar' => '',
        'comissao' => 0,
    ];

    protected $fillable = [
        'idpessoa'
    ];

    public function scopeIsProfessor($query)
    {
        $query->where('professor', 1);
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'idpessoa');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'idunidade');
    }

    public function getTituloAttribute()
    {
        return $this->pessoa->nome ?? '';
    }

    public $timestamps = false;
}
