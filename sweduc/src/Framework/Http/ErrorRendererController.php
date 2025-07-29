<?php

declare(strict_types=1);

namespace App\Framework\Http;

use App\Academico\Exception\CalculoDeMediaException;
use App\Framework\Application;
use App\Exception\RecursoNaoAutorizadoException;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorRendererController extends BaseController
{
    /**
     * Retorna visualização do erro
     */
    public function handleThrowable(Throwable $exception, $hasAcceptJsonHeader): ResponseInterface
    {
        if (!empty($hasAcceptJsonHeader)) {
            return $this->renderJsonError($exception);
        }

        return $this->renderHTMLError($exception);
    }

    protected function renderJsonError(Throwable $exception): ResponseInterface
    {
        if ($exception instanceof NotFoundException) {
            return $this->errorJsonResponse('Página não encontrada', 404);
        }


        if ($exception instanceof RecursoNaoAutorizadoException) {
            return $this->errorJsonResponse($exception->getMessage(), 401);
        }

        return $this->errorJsonResponse($exception->getMessage(), 500);
    }

    protected function renderHTMLError(Throwable $exception): ResponseInterface
    {
        if (Application::isDevelopmentEnv()) {
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            return $this->plainTextResponse($whoops->handleException($exception), 500);
        }

        $code = 500;
        $title = 'Erro no servidor';
        $details = 'Erro indefinido';
        $contexto = [];

        if ($exception instanceof NotFoundException) {
            $code = 404;
            $title = 'Página não encontrada';
            $details = 'A página que você busca não foi encontrada, verifique se a URL está correta';
        }

        if ($exception instanceof RecursoNaoAutorizadoException) {
            $code = 401;
            $title = 'Erro de autorização';
            $details = $exception->getMessage();
        }

        if ($exception instanceof CalculoDeMediaException) {
            $code = 500;
            $title = 'Erro de cálculo';
            $details = $exception->getMessage();
            $contexto = $exception->getContexto();
        }

        return $this->platesView('Core/Exception', [
            'code' => $code,
            'title' => $title,
            'details' => $details,
            'contexto' => $contexto,
        ]);
    }
}
