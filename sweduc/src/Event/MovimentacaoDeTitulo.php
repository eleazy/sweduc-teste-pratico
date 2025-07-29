<?php

namespace App\Event;

use App\Model\Financeiro\Titulo;

class MovimentacaoDeTitulo
{
    public const CRIACAO = 0;
    public const RECEBIMENTO = 1;
    public const REABERTURA = 2;
    public const EXCLUSAO = 3;

    public Titulo $titulo;

    public function __construct(public int $operacao, int $tituloId, public int $operador)
    {
        $this->titulo = Titulo::findOrFail($tituloId);
    }

    public static function criacao(int $tituloId, int $operador)
    {
        return new self(self::CRIACAO, $tituloId, $operador);
    }

    public static function recebimento(int $tituloId, int $operador)
    {
        return new self(self::RECEBIMENTO, $tituloId, $operador);
    }

    public static function reabertura(int $tituloId, int $operador)
    {
        return new self(self::REABERTURA, $tituloId, $operador);
    }

    public static function exclusao(int $tituloId, int $operador)
    {
        return new self(self::EXCLUSAO, $tituloId, $operador);
    }
}
