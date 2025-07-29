<?php

declare(strict_types=1);

namespace Tests\Unit\Academico;

use App\Academico\Boletim\SituacaoFinalDisciplina;
use PHPUnit\Framework\TestCase;
use TypeError;

use function PHPUnit\Framework\assertEquals;

final class SituacaoFinalDisciplinaTest extends TestCase
{
    /**
     * Testa uso de inteiros
     *
     */
    public function testImpedeValoresInteiros(): void
    {
        $config = [
            'aprovacao' => 10,
            'aprovacaoRecuperacao' => 10,
            'aprovacaoPF' => 10,
            'minimoParaRecuperacao' => 10,
        ];

        $situacaoFinal = new SituacaoFinalDisciplina(
            $config['aprovacao'],
            $config['aprovacaoRecuperacao'],
            $config['aprovacaoPF'],
            $config['minimoParaRecuperacao'],
        );

        $notas = [
            'mediaAnual' => 10,
            'provaFinal' => 0,
            'recuperacao' => 0,
            'situacaoFinal' => 0,
        ];

        $this->expectException(TypeError::class);

        assertEquals(
            SituacaoFinalDisciplina::APROVADO,
            $situacaoFinal->resultado(
                $notas['mediaAnual'],
                $notas['provaFinal'],
                $notas['recuperacao'],
                $notas['situacaoFinal'],
            ),
            'Resultado da disciplina inválido'
        );
    }

    /**
     * Testa regras de situação de disciplina
     *
     * @dataProvider alfacemSetupDataProvider
     */
    public function testSituacaoDisciplina($config, $notas, $resultadoEsperado): void
    {
        $situacaoFinal = new SituacaoFinalDisciplina(
            $config['aprovacao'],
            $config['aprovacaoRecuperacao'],
            $config['aprovacaoPF'],
            $config['minimoParaRecuperacao'],
        );

        assertEquals(
            $resultadoEsperado,
            $situacaoFinal->resultado(
                $notas['mediaAnual'],
                $notas['provaFinal'],
                $notas['recuperacao'],
                $notas['situacaoFinal'],
            ),
            'Resultado da disciplina inválido'
        );
    }

    public function alfacemSetupDataProvider()
    {
        $configAlfacem = [
            'aprovacao' => 65,
            'aprovacaoRecuperacao' => 50,
            'aprovacaoPF' => 50,
            'minimoParaRecuperacao' => 0,
        ];

        return [
            'Zerado' => [
                $configAlfacem,
                [
                    'mediaAnual' => "0",
                    'provaFinal' => "0",
                    'recuperacao' => "0",
                    'situacaoFinal' => "0",
                ],
                SituacaoFinalDisciplina::REPROVADO
            ],

            'Aprovação direta' => [
                $configAlfacem,
                [
                    'mediaAnual' => "65",
                    'provaFinal' => "",
                    'recuperacao' => "",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::APROVADO
            ],

            'Aprovação por prova final' => [
                $configAlfacem,
                [
                    'mediaAnual' => "",
                    'provaFinal' => "50",
                    'recuperacao' => "",
                    'situacaoFinal' => "50",
                ],
                SituacaoFinalDisciplina::APROVADO
            ],

            'Aprovação por recuperação' => [
                $configAlfacem,
                [
                    'mediaAnual' => "",
                    'provaFinal' => "1",
                    'recuperacao' => "1",
                    'situacaoFinal' => "50",
                ],
                SituacaoFinalDisciplina::APROVADO
            ],

            'Em prova final' => [
                $configAlfacem,
                [
                    'mediaAnual' => "64",
                    'provaFinal' => "",
                    'recuperacao' => "",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::PROVA_FINAL
            ],

            'Em recuperação' => [
                $configAlfacem,
                [
                    'mediaAnual' => "64",
                    'provaFinal' => "49",
                    'recuperacao' => "",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::RECUPERACAO
            ],

            // Por fórmula: ignoramos o valor da recuperação e checamos situação final
            'Reprovado por fórmula (Recuperação)' => [
                $configAlfacem,
                [
                    'mediaAnual' => "64",
                    'provaFinal' => "49",
                    'recuperacao' => "49",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::REPROVADO
            ],

            // Por fórmula: ignoramos o valor da recuperação e checamos situação final
            'Aprovado por fórmula (Recuperação)' => [
                $configAlfacem,
                [
                    'mediaAnual' => "64",
                    'provaFinal' => "49",
                    'recuperacao' => "",
                    'situacaoFinal' => "50",
                ],
                SituacaoFinalDisciplina::APROVADO
            ],

            'Caso 06/12' => [
                $configAlfacem,
                [
                    'mediaAnual' => "58",
                    'provaFinal' => "43",
                    'recuperacao' => "",
                    'situacaoFinal' => "51",
                ],
                SituacaoFinalDisciplina::APROVADO
            ],

            'Fórmula padrão MF = MA + PF / 2' => [
                $configAlfacem,
                [
                    'mediaAnual' => "64",
                    'provaFinal' => "49",
                    'recuperacao' => "",
                    'situacaoFinal' => "56.5",
                ],
                SituacaoFinalDisciplina::APROVADO
            ],

            'Caso Isabela 07/12' => [
                $configAlfacem,
                [
                    'mediaAnual' => "61",
                    'provaFinal' => "39",
                    'recuperacao' => "",
                    'situacaoFinal' => "50",
                ],
                SituacaoFinalDisciplina::APROVADO
            ],

            'Caso Ana Beatriz 07/12' => [
                $configAlfacem,
                [
                    'mediaAnual' => "49",
                    'provaFinal' => "48",
                    'recuperacao' => "",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::RECUPERACAO
            ],

            /**
             * Foi identificado que a passagem de valor está usando strings
             * vazias em vez de nulo ou zero, gerando diferença no teste
             */
            'Caso Elloá 07/12' => [
                $configAlfacem,
                [
                    'mediaAnual' => "58",
                    'provaFinal' => "40",
                    'recuperacao' => "",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::RECUPERACAO
            ],

            'Caso Giulliana 09/12' => [
                $configAlfacem,
                [
                    'mediaAnual' => "41",
                    'provaFinal' => "50",
                    'recuperacao' => "",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::RECUPERACAO
            ],

            'Caso Andrey 09/12' => [
                $configAlfacem,
                [
                    'mediaAnual' => "10",
                    'provaFinal' => "0",
                    'recuperacao' => "",
                    'situacaoFinal' => "",
                ],
                SituacaoFinalDisciplina::RECUPERACAO
            ],
        ];
    }
}
