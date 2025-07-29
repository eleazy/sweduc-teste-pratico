<?php

namespace Tests\Unit\Financeiro\Remessa;

use App\Financeiro\Remessa\GeradorRemessa;
use App\Financeiro\Remessa\Registro;
use App\Service\FilesystemService;
use Mockery;
use Mockery\MockInterface;

use function PHPUnit\Framework\assertEquals;

test('garante timezone final de são paulo', function () {
    new GeradorRemessa();
    assertEquals('America/Sao_Paulo', date_default_timezone_get());
});

test('gerador remessa tenta expandir layout invalido', function () {
    $gerador = Mockery::mock(GeradorRemessa::class, function (MockInterface $mock) {
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('mysqlQuery')->once()->andReturn([]);
        $mock->shouldReceive('mysqlFetchArray')->once()->andReturn([
            'banconum' => 123456,
            'nomeb' => 'Teste',
        ]);
    })->makePartial();

    $idffin = 1;
    $idbancolote = null;
    $versao = null;
    $tamanho = null;
    $desc3 = null;
    $bruto = null;

    assertOutput('N&atilde;o h&aacute; gerador de remessa para o banco 123456 - Teste', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_cef240', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = 0;
    $bruto = 0;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('10411110', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_001', function () {
    $banconum = '001';

    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = null;
    $desc3 = 0;
    $bruto = 0;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '123',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => 1,
        'dataemissao' => 1,
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, function (MockInterface $mock) use ($data) {
        $mock->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('mysqlQuery')->andReturn([]);
        $mock->shouldReceive('mysqlFetchArray')->andReturn(
            $data,
            $data,
            $data,
            $data,
            $data,
            false,
        );
    })->makePartial();

    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    assertOutput('01REMESSA01COBRANCA', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_033_240', function () {
    $banconum = '033';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = 0;
    $bruto = 0;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();

    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);

    $queries = [
        $data,
        $data,
        $data,
        $data,
        false,
    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('03300000', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_104', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = null;
    $bruto = 0;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('10411110', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_237', function () {
    $banconum = '237';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '';
    $desc3 = 0;
    $bruto = 0;

    $registro = Mockery::mock(Registro::class);
    $registro->expects('descontoComercialData1')->atLeast()->once()->andReturn(str_pad('', 13, '0'));
    $registro->expects('descontoComercialValor1')->atLeast()->once()->andReturn(str_pad('', 13, '0'));
    $registro->expects('descontoComercialData2')->atLeast()->once()->andReturn(str_pad('', 13, '0'));
    $registro->expects('descontoComercialValor2')->atLeast()->once()->andReturn(str_pad('', 13, '0'));
    $registro->expects('descontoComercialData3')->atLeast()->once()->andReturn(str_pad('', 13, '0'));
    $registro->expects('descontoComercialValor3')->atLeast()->once()->andReturn(str_pad('', 13, '0'));

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->passthru();
    $gerador->expects('buscaEstado')->atLeast()->once()->passthru();
    $gerador->expects('buscaMultaJuros')->atLeast()->once()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('criarRegistro')->atLeast()->once()->andReturns($registro);
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->atLeast()->once()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->atLeast()->once()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->never()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());


    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('01REMESSA01COBRANCA', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_cef240_107', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = '107';
    $tamanho = '240';
    $desc3 = 0;
    $bruto = 2;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
        'rg' => '',
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('10411110', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_cef240_pj', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = 0;
    $bruto = 2;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
        'rg' => '',
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('10411110', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_033_240_3desc', function () {
    $banconum = '033';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = true;
    $bruto = null;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->passthru();
    $gerador->expects('buscaEstado')->atLeast()->once()->passthru();
    $gerador->expects('buscaMultaJuros')->atLeast()->once()->andReturns($data);
    $gerador->expects('complementoRegistro')->atLeast()->once()->passthru();
    $gerador->expects('completarEspaco')->never()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->never()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->atLeast()->once()->passthru();
    $gerador->expects('moraFormat')->atLeast()->once()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->atLeast()->once()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->atLeast()->once()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->atLeast()->once()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('03300000         2000000012345670                   00012123430000000000111111111111SWSISTEMAS                    BANCO SANTANDER', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_033', function () {
    $banconum = '033';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = null;
    $desc3 = null;
    $bruto = null;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '123',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->never()->passthru();
    $gerador->expects('buscaCidade')->never()->andReturns($data);
    $gerador->expects('buscaEstado')->never()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->never()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->never()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->never()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->never()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->never()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        null,
        $data,
    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('01REMESSA01COBRANÇA', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_104_pj', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = null;
    $bruto = 2;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
        'rg' => '',
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('10411110', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_341_bruto', function () {
    $banconum = '341';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '';
    $desc3 = null;
    $bruto = 1;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());
    $gerador->expects('buscaMultaJuros')->atLeast()->once()->andReturns($data);

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('01REMESSA01COBRANCA', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_cef240_bruto', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = null;
    $bruto = true;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('104', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_cef400', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '400';
    $desc3 = null;
    $bruto = false;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('01REMESSA01COBRANCA', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_033_240_bruto', function () {
    $banconum = '033';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = null;
    $bruto = true;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);

    $queries = [
        $data,
        $data,
        $data,
        $data,
        false,
    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('03300000', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_104_bruto', function () {
    $banconum = '104';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '240';
    $desc3 = 0;
    $bruto = 1;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaEstado')->atLeast()->once()->andReturns($data);
    $gerador->expects('buscaMultaJuros')->never()->andReturns($data);
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->never()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->never()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->atLeast()->once()->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('1', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_237_bruto', function () {
    $banconum = '237';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '';
    $desc3 = null;
    $bruto = true;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
        'carteira' => '',
        'baixa_dias' => 1,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);
    $gerador->expects('applyHeaders')->atLeast()->once()->passthru();
    $gerador->expects('atualizaLoteRemessa')->atLeast()->once()->passthru();
    $gerador->expects('atualizaRemessaFFin')->atLeast()->once()->passthru();
    $gerador->expects('buscaCidade')->atLeast()->once()->passthru();
    $gerador->expects('buscaEstado')->atLeast()->once()->passthru();
    $gerador->expects('complementoRegistro')->never()->passthru();
    $gerador->expects('completarEspaco')->atLeast()->once()->passthru();
    $gerador->expects('completarZero')->atLeast()->once()->passthru();
    $gerador->expects('descComercial')->never()->passthru();
    $gerador->expects('digitoVerificadorNossoNumero')->atLeast()->once()->passthru();
    $gerador->expects('fDatadesc')->never()->passthru();
    $gerador->expects('limpaCaractereEspecial')->atLeast()->once()->passthru();
    $gerador->expects('modulo10')->never()->passthru();
    $gerador->expects('modulo11')->atLeast()->once()->passthru();
    $gerador->expects('moraFormat')->never()->passthru();
    $gerador->expects('picture9')->never()->passthru();
    $gerador->expects('pictureHH')->never()->passthru();
    $gerador->expects('pictureX')->never()->passthru();
    $gerador->expects('postSlug')->never()->passthru();
    $gerador->expects('removerAcentos')->never()->passthru();
    $gerador->expects('resolveDescontoPorcentagem')->never()->passthru();
    $gerador->expects('respFinInfo')->atLeast()->once()->andReturns($data);
    $gerador->expects('sanitizeString')->never()->passthru();
    $gerador->expects('statusDescComercial')->atLeast()->once()->passthru();
    $gerador->expects('stripNonAlphaNumeric')->passthru();
    $gerador->expects('textoLimpoTeste')->never()->passthru();
    $gerador->expects('valorItensDescontoBoleto')->atLeast()->once()->andReturns(1);
    $gerador->expects('verificaDescComercial')->never()->passthru();
    $gerador->expects('zeros')->never()->passthru();
    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());
    $gerador->expects('buscaMultaJuros')->atLeast()->once()->andReturns($data);

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        null,
        $data,

    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries, $data) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('01REMESSA01COBRANCA', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});

test('gerador remessa: remessa_criarlote_341', function () {
    $banconum = '341';
    $idffin = 1;
    $idbancolote = 1;
    $versao = null;
    $tamanho = '';
    $desc3 = null;
    $bruto = null;

    $data = [
        'id' => 1,
        'banconum' => $banconum,
        'nome' => 'Teste',
        'logradouro' => 'Teste',
        'numero' => '123',
        'complemento' => '123',
        'bairro' => '123',
        'nom_cidade' => 'qwe',
        'sgl_estado' => 'RJ',
        'nomeb' => 'Teste',
        'nomefantasia' => 'SW Sistemas',
        'razaosocial' => 'SW Sistemas',
        'conta' => '11-11111',
        'remessalote' => 1,
        'tarifaboleto' => 0,
        'agencia' => '12-12343',
        'dvagencia' => '9',
        'convenio' => '1234567',
        'cnpj' => '1234567',
        'eid' => '1234567',
        'valor' => 123,
        'matricula' => 123,
        'multa' => 0,
        'multapercentual' => 0,
        'juro' => 0,
        'jurodia' => 0,
        'mora' => 0,
        'datavencimento' => '2022-09-01',
        'dataemissao' => '2022-09-01',
        'idaluno' => 1,
        'idcidade' => 1,
        'idestado' => 1,
        'bolsa' => 1,
        'descontoboleto' => 1,
        'cep' => '12345',
        'cpf' => '12345678',
        'recebeDesconto' => 0,
        'codTransmissao' => 0,
        'codComplemento' => 0,
        'dc' => 0,
        'titulo' => 0,
        'valortotal' => 0,
    ];

    $gerador = Mockery::mock(GeradorRemessa::class, fn (MockInterface $mock) => $mock->shouldAllowMockingProtectedMethods())->makePartial();

    $gerador->expects('storage')->once()->andReturn(FilesystemService::memory());
    $gerador->expects('mysqlQuery')->atLeast()->once()->andReturnArg(0);

    $queries = [
        $data,
        $data,
        $data,
        $data,
        $data,
        $data,
        false,
    ];

    $gerador->expects('mysqlFetchArray')->atLeast()->once()->withAnyArgs()->andReturnUsing(function (string $query) use (&$queries) {
        assert(!empty($queries), 'Unexpected query: ' . $query);
        return array_shift($queries);
    });

    assertOutput('01REMESSA01COBRANCA', fn() => $gerador->gerar(
        $idffin,
        $idbancolote,
        $versao,
        $tamanho,
        $desc3,
        $bruto,
    ));
});
