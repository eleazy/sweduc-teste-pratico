<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class Retorno extends Model
{
    public $timestamps = false;
    protected $table = 'financeiro_retornos';
}
