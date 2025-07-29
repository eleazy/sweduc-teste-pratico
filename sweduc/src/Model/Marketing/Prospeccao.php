<?php

declare(strict_types=1);

namespace App\Model\Marketing;

use App\Model\Core\Funcionario;
use Illuminate\Database\Eloquent\Model;

class Prospeccao extends Model
{
    public function eventos()
    {
        return $this->hasMany(Evento::class, 'id_prospeccao_ficha');
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'id_funcionario');
    }

    public function midia()
    {
        return $this->belongsTo(Midia::class, 'id_midia');
    }

    public $timestamps = false;
    protected $table = 'prospeccao_fichas';
}
