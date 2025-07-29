<?php

declare(strict_types=1);

namespace App\Framework\DependencyInjection;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

trait DIContainerWrapperTrait
{
    protected function defaultContainer(bool $shouldCache = false): ContainerInterface
    {
        if (empty($this->container)) {
            $containerBuilder = new ContainerBuilder();

            if ($shouldCache) {
                $containerBuilder->enableCompilation(__DIR__ . '/../../storage/tmp');
                $containerBuilder->writeProxiesToFile(true, __DIR__ . '/../../storage/tmp/proxies');
            }

            $containerBuilder->addDefinitions(__DIR__ . '/../../config.php');

            $this->container = $containerBuilder->build();
        }

        return $this->container;
    }

    /**
     * Retorna os itens do container
     *
     * @return mixed
     */
    public function get(mixed $item)
    {
        return $this->defaultContainer()->get($item);
    }

    /**
     * Configura valor de entrada no container
     *
     * @return mixed
     */
    public function set(mixed $item, mixed $value): void
    {
        if ($this->container instanceof Container) {
            $this->container->set($item, $value);
            return;
        }

        throw new \Exception('Container não é uma instância de ' . Container::class);
    }

    /**
     * Retorna os itens do container
     *
     * @return mixed
     */
    public function has(mixed $item)
    {
        return $this->defaultContainer()->get($item);
    }
}
