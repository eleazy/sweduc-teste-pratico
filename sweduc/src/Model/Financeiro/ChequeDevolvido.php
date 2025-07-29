<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class ChequeDevolvido extends Model
{
    public $timestamps = false;

    public function fichaFinanceira()
    {
        return $this->belongsTo('App\Model\FichaFinanceira', 'id_fichafinanceira');
    }

    protected $table = 'cheque_devolvido';
}
