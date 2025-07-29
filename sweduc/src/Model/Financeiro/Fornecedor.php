<?php

declare(strict_types=1);

namespace App\Model\Financeiro;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    protected $table = 'fornecedores';
}
