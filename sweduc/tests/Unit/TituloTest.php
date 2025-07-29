<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Academico\Model\Matricula;
use App\Model\Core\Empresa;
use App\Model\Core\Funcionario;
use App\Model\Financeiro\Conta;
use App\Model\Financeiro\Titulo;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;

class TituloTest extends TestCase
{
    /**
     * Testa se os valores de bolsa, vencimento, valor e total conferem
     * com as regras de negócio dadas as condições e configurações.
     *
     * @dataProvider fichaDataProvider
     */
    public function testaValoresEsperados($contaConfig, $fichaConfig)
    {
        $empresa = new Empresa();
        $empresa->multa = $contaConfig['multa'];
        $empresa->mora = $contaConfig['mora'];
        $empresa->bolsaAposVencimento = $contaConfig['bolsaAposVencimento'];

        $conta = new Conta();
        $conta->empresa = $empresa;

        $ficha = $this->createPartialMock(Titulo::class, ['getBolsaAposVencimento']);
        $ficha->method('getBolsaAposVencimento')->willReturn($conta->empresa->bolsaAposVencimento);

        assertEquals(
            $contaConfig['bolsaAposVencimento'],
            $ficha->getBolsaAposVencimento(),
            'configuração de bolsa apos vencimento incorreta.'
        );

        $ficha->conta = $conta;
        $ficha->valor = $fichaConfig['valor'];
        $ficha->bolsa = $fichaConfig['bolsa'];
        $ficha->desconto = $fichaConfig['desconto'];
        $ficha->vencimento = $fichaConfig['vencimento'];

        assertEquals($fichaConfig['vencidoEsperado'], $ficha->vencido, 'status de vencimento não confere');
        assertEquals($fichaConfig['valorEsperado'], $ficha->valor, 'valor incorreto');
        assertEquals($fichaConfig['bolsaEsperada'], $ficha->bolsa, 'bolsa incorreta');
        // assertEquals($fichaConfig['multaCorrenteEsperada'], $ficha->multaCorrente, 'multa corrente incorreta');
        // assertEquals($fichaConfig['jurosCorrenteEsperado'], $ficha->jurosCorrente, 'juros corrente incorreto');
        // assertEquals($fichaConfig['totalEsperado'], $ficha->total, 'total esperado incorreto');
        // assertEquals($fichaConfig['saldoEsperado'], $ficha->saldo, 'saldo incorreto');
    }

    /**
     * @ignore description
     *
     * Testa se os valores de bolsa, vencimento, valor e total conferem
     * com as regras de negócio dadas as condições e configurações.
     *
     * @dataProvider metodoGerarDataProvider
     */
    public function testaGerarTitulo(
        $conta,
        $funcionario,
        $matricula,
        $opcoesTitulo,
        $total,
        $vencimento,
        $metadados,
        $pagarComCartao,
        $pagarComBoleto
    ) {
        $this->markTestSkipped(
            'Ignora teste por problemas temporários com configuração de banco de dados.'
        );
        return;

        $titulo = Titulo::gerar(
            $conta,
            $funcionario,
            $matricula,
            $vencimento,
            $total,
            $metadados,
            $opcoesTitulo
        );

        assertFalse($titulo->vencimento->isWeekend(), 'vencimento para fim de semana');

        assertEquals($opcoesTitulo['remessalote'] == 0, !$titulo->recebeComBoleto, 'Botão de pagamento com boleto visível com remessa lote 0.');
        assertEquals($pagarComCartao, $titulo->recebeComCartao, 'Erro na configuração de pagamento em cartão.');
        assertEquals($pagarComBoleto, $titulo->recebeComBoleto, 'Erro na configuração de pagamento em boleto.');
        assertEquals($pagarComCartao, !$titulo->recebeComBoleto, 'Cartão permitido em pagamento com boleto.');
        assertEquals($pagarComBoleto, !$titulo->recebeComCartao, 'Boleto permitido em pagamento com cartão.');
    }

    public function metodoGerarDataProvider()
    {
        // Ignora teste por problemas temporários com configuração de banco de dados
        return;

        return [
            'OpenCart Cartao' => [
                'conta' => Conta::first(),
                'funcionario' => Funcionario::first(),
                'matricula' => Matricula::first(),
                'opcoesTitulo' => [
                    'pagamento_cartao_online' => true,
                    'remessaenviado' => 1,
                    'remessalote' => 0,
                    'remessadata' => Carbon::now(),
                ],
                'total' => 25,
                'vencimento' => Carbon::now(),
                'metadados' => [
                    'origin' => 'opencart_sweduc_checkout',
                    'return_url' => null,
                    'order_id' => random_int(0, PHP_INT_MAX),
                    'customer_id' => random_int(0, PHP_INT_MAX),
                ],
                'pagarComCartao' => true,
                'pagarComBoleto' => false,
            ],

            'Opencart Boleto' => [
                'conta' => Conta::first(),
                'funcionario' => Funcionario::first(),
                'matricula' => Matricula::first(),
                'opcoesTitulo' => [
                    'pagamento_cartao_online' => false,
                    'remessaenviado' => 1,
                    'remessalote' => 50,
                    'remessadata' => Carbon::now(),
                ],
                'total' => 25,
                'vencimento' => Carbon::now(),
                'metadados' => [
                    'origin' => 'opencart_sweduc_checkout',
                    'return_url' => null,
                    'order_id' => random_int(0, PHP_INT_MAX),
                    'customer_id' => random_int(0, PHP_INT_MAX),
                ],
                'pagarComCartao' => false,
                'pagarComBoleto' => true,
            ],

            'Opencart vencimento em fim de semana' => [
                'conta' => Conta::first(),
                'funcionario' => Funcionario::first(),
                'matricula' => Matricula::first(),
                'opcoesTitulo' => [
                    'pagamento_cartao_online' => true,
                    'remessaenviado' => 1,
                    'remessalote' => 0,
                    'remessadata' => Carbon::now(),
                ],
                'total' => 25,
                'vencimento' => Carbon::now(),
                'metadados' => [
                    'origin' => 'opencart_sweduc_checkout',
                    'return_url' => null,
                    'order_id' => random_int(0, PHP_INT_MAX),
                    'customer_id' => random_int(0, PHP_INT_MAX),
                ],
                'pagarComCartao' => true,
                'pagarComBoleto' => false,
            ],
        ];
    }

    public function fichaDataProvider()
    {
        return [
            'ficha a vencer' => [
                'contaConfig' => [
                    // Limite max. de multa pela lei 2%
                    'multa' => 2,
                    'mora' => 0.5,
                    'bolsaAposVencimento' => false,
                ],

                'ficha' => [
                    'valor' => 100,
                    'bolsa' => 100,
                    'desconto' => 0,
                    'vencimento' => Carbon::now()->addDays(10),
                    // 10 dias a frente

                    'vencidoEsperado' => false,
                    'multaCorrenteEsperada' => 0,
                    'jurosCorrenteEsperado' => 0,
                    'valorEsperado' => 100,
                    'bolsaEsperada' => 100,
                    'totalEsperado' => 0,
                    'saldoEsperado' => 0,
                ],
            ],

            'ficha vencida / sem bolsa apos vencimento' => [
                'contaConfig' => [
                    // Limite max. de multa pela lei 2%
                    'multa' => 2,
                    'mora' => 0.5,
                    'bolsaAposVencimento' => false,
                ],

                'ficha' => [
                    'valor' => 100,
                    'bolsa' => 100,
                    'desconto' => 0,
                    'vencimento' => Carbon::now()->subDays(10),
                    // 10 dias atrasada

                    'vencidoEsperado' => true,
                    'valorEsperado' => 100,
                    'bolsaEsperada' => 0,

                    // Valor: $100, Multa: $2, Mora: $5
                    'multaCorrenteEsperada' => 2,
                    'jurosCorrenteEsperado' => 5,
                    'totalEsperado' => 100 + 2 + 5,
                    'saldoEsperado' => - (100 + 2 + 5),
                ],
            ],

            'ficha vencida / com bolsa apos vencimento' => [
                'contaConfig' => [
                    // Limite max. de multa pela lei 2%
                    'multa' => 2,
                    'mora' => 0.5,
                    'bolsaAposVencimento' => true,
                ],

                'ficha' => [
                    'valor' => 100,
                    'bolsa' => 100,
                    'desconto' => 0,
                    'vencimento' => Carbon::now()->subDays(10),
                    // 10 dias atrasada

                    'vencidoEsperado' => true,
                    'valorEsperado' => 100,
                    'bolsaEsperada' => 100,

                    // Valor: $100, Multa: $2, Mora: $5, Bolsa: $100
                    'multaCorrenteEsperada' => 2,
                    'jurosCorrenteEsperado' => 5,
                    'totalEsperado' => 100 + 2 + 5 - 100,
                    'saldoEsperado' => - (100 + 2 + 5 - 100),
                ],
            ]
        ];
    }
}
