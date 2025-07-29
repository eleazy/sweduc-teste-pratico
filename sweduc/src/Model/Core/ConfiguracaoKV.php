<?php

declare(strict_types=1);

namespace App\Model\Core;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoKV extends Model
{
    public $timestamps = false;

    public static function chave($key)
    {
        return self::firstWhere('chave', $key)->valor ?? null;
    }

    protected $table = 'configuracoes_kv';
}
