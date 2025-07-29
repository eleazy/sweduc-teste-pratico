<?php

declare(strict_types=1);

namespace App\Event\MovimentacaoDeTitulo;

use App\Event\MovimentacaoDeTitulo;
use App\Academico\Model\Rematricula;
use Psr\Log\LoggerInterface;

class CancelarRematricula
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(MovimentacaoDeTitulo $movimentacao)
    {
        if ($movimentacao->operacao === MovimentacaoDeTitulo::EXCLUSAO) {
            $rematricula = Rematricula::where('titulo_id', $movimentacao->titulo->id)->first();

            if (!$rematricula) {
                return;
            }

            $rematriculaId = $rematricula->id;
            $tituloId = $movimentacao->titulo->id;

            $rematricula->delete();
            $this->logger->info("Desabilitando processo de rematrícula ID: $rematriculaId vínculado ao título ID: $tituloId");
        }
    }
}
