<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class FormaDePagamento extends Model
{
    public $timestamps = false;

    protected $table = 'formaspagamentos';
}
