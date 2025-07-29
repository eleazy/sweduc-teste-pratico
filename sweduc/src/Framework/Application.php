<?php

declare(strict_types=1);

namespace App\Framework;

use App\Framework\DependencyInjection\DIContainerWrapperTrait;
use App\Framework\DependencyInjection\InvokerTrait;
use App\Framework\Environment\EnvironmentManager;
use App\Framework\Service\EloquentService;
use App\Framework\Service\EnvironmentService;
use App\Framework\Service\ErrorLoggerService;
use App\Framework\Service\EventsService;
use App\Framework\Service\SentryService;
use App\Framework\Service\ManageServicesTrait;
use App\Framework\Service\MysqlConnectionService;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Application implements ContainerInterface, InvokerInterface
{
    use DIContainerWrapperTrait;
    use InvokerTrait;
    use ManageServicesTrait;

    private static ?self $instance = null;

    protected ContainerInterface $container;

    private $services = [
        EventsService::class,
        ErrorLoggerService::class,
        EnvironmentService::class,
        MysqlConnectionService::class,
        EloquentService::class,
        SentryService::class,
    ];

    public static function globalInstance(ContainerInterface $container = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($container);
        }

        return self::$instance;
    }

    public function __construct(
        ContainerInterface $container = null,
        $autoBoot = true,
        $globalInstance = true
    ) {
        if ($globalInstance && self::$instance === null) {
            self::$instance = $this;
        }

        $this->container = $container ?? self::defaultContainer();

        if ($autoBoot) {
            $this->boot();
        }
    }

    public function boot()
    {
        foreach ($this->services as $service) {
            $this->call([$service, 'boot']);
        }
    }

    /**
     * Dispara eventos da aplicação
     *
     * @param object $evento
     */
    public function evento($evento): void
    {
        $this->get(EventDispatcherInterface::class)->dispatch($evento);
    }

    public static function isProductionEnv()
    {
        return EnvironmentManager::isProductionEnv();
    }

    public static function isDevelopmentEnv()
    {
        return EnvironmentManager::isDevelopmentEnv();
    }
}
