<?php

declare(strict_types=1);

namespace App\Academico\Boletim;

class SituacaoFinalDisciplina
{
    public const PROVA_FINAL = 'PROVA FINAL';
    public const APROVADO = 'APROVADO';
    public const RECUPERACAO = 'RECUPERAÇÃO';
    public const REPROVADO = 'REPROVADO';
    public const INDEFINIDO = 'INDEFINIDO';

    public function __construct(private $mediaAprovacao, private $mediaAprovacaorec, private $mediaAprovacaopf, private $minimoRecuperacao)
    {
    }

    public function resultado(string $mediaAnual, string $provaFinal, string $recuperacao, string $situacaoFinal)
    {
        $provaFinalLancada = strlen($provaFinal) > 0;
        $recuperacaoLancada = strlen($recuperacao) > 0;

        // Média maior que aprovação
        // Prova final maior que média de aprovação por prova final
        if (
            $mediaAnual >= $this->mediaAprovacao
            || ($provaFinalLancada && $situacaoFinal >= $this->mediaAprovacaopf)
            || ($recuperacaoLancada && $situacaoFinal >= $this->mediaAprovacaorec)
        ) {
            return self::APROVADO;
        }

        // Média maior que limite de recuperação e prova final não realizada
        if (!$provaFinalLancada && $mediaAnual >= $this->minimoRecuperacao) {
            return self::PROVA_FINAL;
        }

        // Média maior que limite de recuperação e recuperação não realizada
        if (!$recuperacaoLancada && $mediaAnual >= $this->minimoRecuperacao) {
            return self::RECUPERACAO;
        }

        return self::REPROVADO;
    }
}
