<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class PermissaoGrupo extends Model
{
    protected $fillable = [ 'grupo_id', 'unidade_id', 'permissao' ];

    public function grupo()
    {
        return $this->hasOne(Grupo::class);
    }

    public function unidades()
    {
        return $this->belongsTo(Unidade::class);
    }

    protected $table = 'politica_permissoes_grupos';
}
