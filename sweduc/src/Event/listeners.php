<?php

use App\Framework\Application;
/**
 * @var Application $this
 */

use App\Event\MovimentacaoDeTitulo;
use App\Opencart\Listener\OpencartMovimentacaoTituloListener;
use League\Event\EventDispatchingListenerRegistry;

use function App\Framework\app;
use function App\Framework\resolve;

$eventDispatcher = resolve(EventDispatchingListenerRegistry::class);

/**
 * Aciona listeners de eventos configurados em classes
 * com invokables com nome de pasta respectivo ao nome
 * do evento chamado
 *
 * @param [type] $event
 * @return void
 */
function wireListeners($event)
{
    $eventClass = $event::class;
    $eventClassName = ($pos = strrpos($eventClass, '\\')) ? substr($eventClass, $pos + 1) : $eventClass;

    $path = __DIR__ . DIRECTORY_SEPARATOR . $eventClassName;
    $files = array_slice(scandir($path), 2);
    $listenerClasses = array_map(
        fn ($x) => substr($x, 0, -4),
        array_filter(
            $files,
            fn ($x) => strpos($x, '.php')
        )
    );

    foreach ($listenerClasses as $class) {
        $listenerClass = "$eventClass\\$class";
        app()->call($listenerClass, [$event]);
    }
}

/**
 * Opencart
 */
$eventDispatcher->subscribeTo(
    MovimentacaoDeTitulo::class,
    fn(MovimentacaoDeTitulo $event) => app()->call([OpencartMovimentacaoTituloListener::class, 'comunicarLoja'], [ $event ])
);

/**
 * Autowiring de eventos de movimentação
 */
$eventDispatcher->subscribeTo(MovimentacaoDeTitulo::class, fn ($event) => wireListeners($event));
