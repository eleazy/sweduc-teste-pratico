<?php

namespace App\Framework\Router;

use App\Framework\Http\BaseController as Controller;
use League\Route\Route;
use League\Route\Strategy\ApplicationStrategy as ApplicationStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerAwareStrategy extends ApplicationStrategy
{
    /**
     * {@inheritdoc}
     */
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());

        if ($controller instanceof Controller) {
            $controller->setRequest($request);
        }

        if (
            is_array($controller) &&
            !empty($controller[0]) &&
            $controller[0] instanceof Controller
        ) {
            $controller[0]->setRequest($request);
        }

        $response = $controller($request, $route->getVars());
        return $this->decorateResponse($response);
    }
}
