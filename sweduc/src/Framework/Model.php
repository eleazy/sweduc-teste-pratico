<?php

declare(strict_types=1);

namespace App\Framework;

use App\Framework\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use HasFactory;

    public static function factory($count = null, $state = [])
    {
        $factory = static::newFactory() ?: Factory::factoryForModel(get_called_class());

        return $factory
                    ->count(is_numeric($count) ? $count : null)
                    ->state(is_callable($count) || is_array($count) ? $count : $state);
    }
}
