<?php

declare(strict_types=1);

namespace App\Model\Config;

use App\Framework\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signatario extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome_completo',
        'email',
        'cpf',
        'nascimento',
    ];
}
