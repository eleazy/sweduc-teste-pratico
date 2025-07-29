<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Model\Core\Cidade;

class Empresa extends Model
{
    public $timestamps = false;

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'idcidade');
    }

    public function getTituloAttribute()
    {
        return "{$this->razaosocial} ({$this->nomefantasia})";
    }

    public function gerarNumeroDeTitulo()
    {
        $numero = $this->titulofichafinanceira;
        $this->titulofichafinanceira++;
        $this->save();
        return $numero;
    }

    public function unidades()
    {
        return $this->belongsToMany(Unidade::class, 'unidades_empresas', 'idempresa', 'idunidade');
    }
}
