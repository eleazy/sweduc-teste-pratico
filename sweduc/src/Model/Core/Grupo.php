<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    public $fillable = [
        'id',
        'nome'
    ];

    public function permissoes()
    {
        return $this->hasMany(PermissaoGrupo::class);
    }

    public $incrementing = false;
    protected $table = 'politica_grupos';
}
