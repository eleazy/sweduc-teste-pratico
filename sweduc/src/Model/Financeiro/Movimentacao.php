<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    public function scopeFechamento($query)
    {
        $query->where('motivo', 'like', 'Fechamento%');
    }

    public $timestamps = false;
}
