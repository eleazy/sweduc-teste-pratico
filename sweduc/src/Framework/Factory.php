<?php

declare(strict_types=1);

namespace App\Framework;

use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Illuminate\Support\Str;

abstract class Factory extends EloquentFactory
{
    public function modelName()
    {
        $resolver = function (self $factory) {
            $semSufixo = Str::replaceLast('Factory', '', $factory::class);
            $caminhoDaModel = Str::replaceFirst('Factory', 'Model', $semSufixo);

            return $caminhoDaModel;
        };

        return $this->model ?: $resolver($this);
    }

    public static function resolveFactoryName(string $modelName)
    {
        return Str::replaceFirst('Model', 'Factory', $modelName . 'Factory');
    }

    protected function withFaker()
    {
        return FakerFactory::create('pt_BR');
    }
}
