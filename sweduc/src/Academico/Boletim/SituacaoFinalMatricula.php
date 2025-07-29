<?php

declare(strict_types=1);

namespace App\Academico\Boletim;

use App\Academico\Model\Serie;

class SituacaoFinalMatricula
{
    public const PROVA_FINAL = 'PROVA FINAL';
    public const APROVADO = 'APROVADO';
    public const APROVADO_DEPS = 'APROVADO COM DEPENDÊNCIA';
    public const RECUPERACAO = 'RECUPERAÇÃO';
    public const REPROVADO = 'REPROVADO';
    public const INDEFINIDO = 'INDEFINIDO';

    public static function deSerie(Serie $serie)
    {
        return new self(
            $serie->limiterecuperacoes,
            $serie->dependencias,
            $serie->limiteprovasfinais,
        );
    }

    public function __construct(private $limiteDeRecuperacoes, private $limiteDeDependencias, private $limiteDeProvasFinais)
    {
    }

    public function resultado(
        $disciplinasIndefinidas,
        $disciplinasReprovadas,
        $disciplinasRecuperacao,
        $disciplinasAprovado,
        $disciplinasEmProvaFinal,
        $provaFinalHabilitada
    ) {
        $ultrapassaLimiteDeProvasFinais = $this->limiteDeProvasFinais < $disciplinasEmProvaFinal;
        $ultrapassaLimiteDeRecuperacoes = $this->limiteDeRecuperacoes < $disciplinasRecuperacao;
        $ultrapassaLimiteDeDependencias = $this->limiteDeDependencias < $disciplinasReprovadas + $disciplinasRecuperacao;

        /**
         * Tem prova final se diferencia de possui prova final pois o primeiro se trata
         * de disciplinas em prova final e o segundo sobre ter a avaliação do tipo prova final
         */
        $temProvaFinal = $disciplinasEmProvaFinal > 0;
        $temRecuperacao = $disciplinasRecuperacao > 0;
        $temReprovacao = $disciplinasReprovadas > 0;
        $temAprovacao = $disciplinasAprovado > 0;

        if ($disciplinasIndefinidas > 0) {
            return self::INDEFINIDO;
        }

        if ($provaFinalHabilitada && $temProvaFinal && !$ultrapassaLimiteDeProvasFinais) {
            return self::PROVA_FINAL;
        }

        if ($temRecuperacao && !$ultrapassaLimiteDeRecuperacoes) {
            return self::RECUPERACAO;
        }

        if (
            ($temReprovacao || $temRecuperacao) &&
            $ultrapassaLimiteDeDependencias ||
            $ultrapassaLimiteDeProvasFinais
        ) {
            return self::REPROVADO;
        }

        if (
            ($temReprovacao || $temRecuperacao) &&
            $temAprovacao &&
            !$ultrapassaLimiteDeDependencias
        ) {
            return self::APROVADO_DEPS;
        }

        if ($temAprovacao) {
            return self::APROVADO;
        }

        return self::INDEFINIDO;
    }
}
