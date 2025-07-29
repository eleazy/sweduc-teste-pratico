<?php

declare(strict_types=1);

namespace App\Framework\Service;

class EventsService
{
    /**
     * Registra sistema de eventos
     */
    public function boot(): void
    {
        require_once __DIR__ . '/../../Event/listeners.php';
    }
}
