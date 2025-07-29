<?php

declare(strict_types=1);

namespace App\Framework\DependencyInjection;

use InvalidArgumentException;
use Invoker\Invoker;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Container\ContainerInterface;

use function App\Framework\resolve;

trait InvokerTrait
{
    protected Invoker $invoker;

    /**
     * Resolve dependencias de um callable com o container
     *
     * @param [type] $callable
     * @return mixed
     */
    public function call($callable, array $parameters = [])
    {
        if (!($this instanceof ContainerInterface)) {
            throw new InvalidArgumentException(
                "A classe usada pela trait não é uma interface de container"
            );
        }

        if (empty($this->invoker)) {
            $parameterResolver = new ResolverChain([
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
                new TypeHintContainerResolver($this->container),
            ]);

            $this->invoker = new Invoker(
                $parameterResolver,
                $this->container
            );
        }

        return $this->invoker->call($callable, $parameters);
    }
}
