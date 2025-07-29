<?php

declare(strict_types=1);

namespace App\Framework\Service;

trait ManageServicesTrait
{
    public function prependService($service)
    {
        $this->services = [
            $service,
            ...$this->services,
        ];
    }

    public function appendService($service)
    {
        $this->services = [
            ...$this->services,
            $service,
        ];
    }

    public function replaceService($serviceToReplace, $newService)
    {
        $position = array_search($serviceToReplace, $this->services, true);

        if ($position === false) {
            throw new \Exception('Service not found');
        }

        $this->services[$position] = $newService;
    }

    public function removeService($service)
    {
        $this->services = [
            ...$this->services,
            $service,
        ];
    }

    public function setServices(array $services)
    {
        $this->services = $services;
    }
}
