<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Telefone extends Model implements \Stringable
{
    public $timestamps = false;
    public $table = 'telefones';
    protected $fillable = ['telefone'];

    public function tipo()
    {
        return $this->belongsTo(TipoTel::class, 'idtipotel');
    }

    public function scopeCelular($query)
    {
        return $query->where('idtipotel', TipoTel::TIPO_CELULAR);
    }

    public function __toString(): string
    {
        return (string) $this->telefone;
    }
}
