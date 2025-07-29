<?php

declare(strict_types=1);

namespace App\Framework\Middleware;

use App\Exception\RecursoNaoEncontradoException;
use App\Academico\Model\Matricula;
use App\Model\Core\Usuario;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GuardaMatriculaMiddleware implements MiddlewareInterface
{
    /**
     * @{inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $usuario = $request->getAttribute(Usuario::class);
        $matriculaId = filter_var($request->getAttribute('matriculaId'), FILTER_VALIDATE_INT);

        if (!$matriculaId) {
            throw new RecursoNaoEncontradoException();
        }

        $matricula = Matricula::findOrFail($matriculaId);
        // Pode lançar RecursoNaoAutorizadoException
        $matricula->autoriza($usuario);
        return $handler->handle($request);
    }
}
