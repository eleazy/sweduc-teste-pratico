<?php

namespace Tests\Unit\Academico;

use App\Academico\CalculoMediaService;
use App\Academico\Model\Media;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertIsString;

final class CalculoMediaServiceTest extends TestCase
{
    public function testCanInterpretConstantFormula(): void
    {
        $media = $this->makeMedia('1 + 1');
        $logger = null;
        $servico = new CalculoMediaService(2, $logger);
        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('2.00', $nota, 'Cálculo ou formatação inválida');
    }

    public function testCanUseFunctionsInFormula(): void
    {
        $media = $this->makeMedia('arredonda05(8.3)');
        $logger = null;
        $servico = new CalculoMediaService(2, $logger);
        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('8.50', $nota, 'Cálculo ou formatação inválida');

        $media = $this->makeMedia('mediaNaoNulos(8,0,8)');
        $logger = null;
        $servico = new CalculoMediaService(2, $logger);
        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('8.00', $nota, 'Cálculo ou formatação inválida');

        $media = $this->makeMedia('mediaNaoNulos(5, 0, 7, 8)');
        $logger = null;
        $servico = new CalculoMediaService(2, $logger);
        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('6.67', $nota, 'Cálculo ou formatação inválida');
    }

    public function testInvalidFormula(): void
    {
        // Fórmula inválida
        $media = $this->makeMedia('$xxxxxx;');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->withConsecutive([
                $this->isType('string')
            ], [
                $this->isType('array')
            ])
            ->will($this->returnValue(null));

        $servico = new CalculoMediaService(2, $logger);
        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('', $nota, 'Cálculo ou formatação inválida');
    }

    public function testBasicFormula(): void
    {
        $media = $this->makeMedia('#M1337@ * #A123@ * 10');
        $media1337 = $this->makeMedia('10', 1337);

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        // Stub da função que vai buscar a média #M1337@
        $servico->method('getMedia')
             ->willReturn($media1337);

        // Valida retorno de função getMedia
        $servico->expects($this->once())
            ->method('getMedia')
            ->with($this->equalTo($media1337->id))
            ->will($this->returnValue('10'));

        // Stub da função que vai buscar a nota da av #A123@ do aluno
        $servico->method('getNota')
            ->willReturn('10');

        // Valida retorno de função getNota
        $servico->expects($this->once())
            ->method('getNota')
            ->withConsecutive([
                $this->isType('int'),
                $this->equalTo(123)
            ], [
                $this->isType('int'),
                $this->equalTo(1)
            ], [
                $this->objectEquals($media),
            ])
            ->will($this->returnValue('10'));

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('1000.00', $nota, 'Cálculo ou formatação inválida');
    }

    /**
     * Testa periodos comuns com notas não preenchidas
     */
    public function testPeriodosVaziosEmMediaSituacaoFinal(): void
    {
        $media = $this->makeMedia('#A1@ * #A2@ * #A3@');

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        $servico->expects($this->never())
            ->method('getMedia');

        // Stub da função que vai buscar as notas não lançadas
        $servico->expects($this->exactly(3))
            ->method('getNota')
            ->willReturn(null);

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('0.00', $nota, 'Cálculo deve retornar 0.00');
    }

    /**
     * Testa periodos comuns com notas parcialmente preenchida
     */
    public function testPartialCommonPeriodFormula(): void
    {
        $media = $this->makeMedia('(#A1@ + #A2@ + #A3@) / 3');

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        $servico->expects($this->never())
            ->method('getMedia');

        // Stub da função que vai buscar as notas
        $servico->expects($this->exactly(3))
            ->method('getNota')
            ->will($this->onConsecutiveCalls('10', null, null));

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals(
            number_format(10 / 3, 2, '.', ''),
            $nota,
            'Cálculo deve retornar 10/3 formatado'
        );
    }

    /**
     * Testa periodos comuns com notas parcialmente preenchida
     */
    public function testCompleteCommonPeriodFormula(): void
    {
        $media = $this->makeMedia('(#A1@ + #A2@ + #A3@) / 3');

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        $servico->expects($this->never())
            ->method('getMedia');

        // Stub da função que vai buscar as notas
        $servico->expects($this->exactly(3))
            ->method('getNota')
            ->will($this->onConsecutiveCalls('10', '10', '10'));

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals(
            number_format(10, 2, '.', ''),
            $nota,
            'Cálculo deve retornar 10 formatado'
        );
    }

    /**
     * Testa periodo média anual com notas parcialmente preenchida
     */
    public function testPartialAnualMediaPeriodFormula(): void
    {
        $this->markTestSkipped(
            'Conforme necessidade do cliente Alfacem, devido a nova modalidade de disciplinas FGB, boletim deve calcular média anual mesmo com disciplinas sem nota'
        );

        $media = $this->makeMedia('(#M1@ + #M2@ + #M3@) / 3', null, 'mediaanual');

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        $servico->expects($this->never())
            ->method('getNota');

        // Stub da função que vai buscar as médias
        $servico->expects($this->exactly(3))
            ->method('getMedia')
            ->will($this->onConsecutiveCalls(
                $this->makeMedia('3'),
                $this->makeMedia('3'),
                null
            ));

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals(
            '',
            $nota,
            'A média anual só pode ser cálculada preenchida totalmente'
        );
    }

    /**
     * Testa periodos comuns com notas não preenchidas
     */
    public function testPeriodosVaziosEmMediaComum(): void
    {
        $media = $this->makeMedia('(#M1@ + #M2@ + #M3@) / 3', tipo: 'comum');

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        $servico->expects($this->never())
            ->method('getNota');

        // Stub da função que vai buscar as médias
        $servico->expects($this->exactly(3))
            ->method('getMedia')
            ->will($this->onConsecutiveCalls(
                $this->makeMedia('3'),
                $this->makeMedia('3'),
                null
            ));

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('', $nota, 'Cálculo deve retornar vazio para medias comuns com notas nulas');
    }

    public function testPeriodosVaziosEmMediaEspecial(): void
    {
        $this->markTestSkipped(
            'Após a ocorrência de nota 0 numa avaliação prova final, verificou-se necessário que a média especial seja calculada mesmo com notas nulas'
        );

        $media = $this->makeMedia('(#M1@ + #M2@ + #M3@) / 3', tipo: 'mediaanual');

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        $servico->expects($this->never())
            ->method('getNota');

        // Stub da função que vai buscar as médias
        $servico->expects($this->exactly(3))
            ->method('getMedia')
            ->will($this->onConsecutiveCalls(
                $this->makeMedia('3'),
                $this->makeMedia('3'),
                null
            ));

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals('', $nota, 'Cálculo deve retornar vazio para medias especiais com notas nulas');
    }

    /**
     * Testa periodo média anual com notas parcialmente preenchida
     */
    public function testFullfiledAnualMediaPeriodFormula(): void
    {
        $media = $this->makeMedia('(#M1@ + #M2@ + #M3@) / 3', null, 'mediaanual');

        $servico = $this->getMockBuilder(CalculoMediaService::class)
            ->setConstructorArgs([2])
            ->onlyMethods(['getMedia', 'getNota'])
            ->getMock();

        $servico->expects($this->never())
            ->method('getNota');

        // Stub da função que vai buscar as médias
        $servico->expects($this->exactly(3))
            ->method('getMedia')
            ->will($this->onConsecutiveCalls(
                $this->makeMedia('3'),
                $this->makeMedia('3'),
                $this->makeMedia('3'),
            ));

        $nota = $servico->calcularMedia(1, $media);

        assertIsString($nota, 'A nota deve retornar uma string');
        assertEquals(
            '3.00',
            $nota,
            'A média anual só pode ser cálculada preenchida totalmente'
        );
    }

    private function makeMedia(
        string $formula,
        int $id = null,
        $tipo = 'situacaofinalanual',
    ) {
        $media = new Media();
        $media->id = $id ?? random_int(0, mt_getrandmax());
        $media->formula = $formula;
        $media->periodo = (object) [
            'situacaofinalanual' => $tipo === 'situacaofinalanual',
            'recuperacao' => $tipo === 'recuperacao',
            'provafinal' => $tipo === 'provafinal',
            'mediaanual' => $tipo === 'mediaanual',
        ];

        return $media;
    }
}
