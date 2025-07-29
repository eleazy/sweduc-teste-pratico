<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use App\Model\Core\Empresa;
use App\Framework\Model;

class LoteRPS extends Model
{
    public $timestamps = false;
    protected $table = 'loterps';

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'idempresa');
    }
}
