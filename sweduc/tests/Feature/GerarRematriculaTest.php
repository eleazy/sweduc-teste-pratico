<?php

use App\Model\Core\Usuario;

use function PHPUnit\Framework\assertEquals;

test('gera rematricula com sucesso', function () {
    loggedAs(Usuario::first());

    $request = postJson('/api/v1/academico/rematriculas/gerar-matriculas', [
        'rematriculas' => ["4101"],
        'eventoFinanceiro' => "1",
        'conta' => "156",
        'qtdParcelas' => 12,
        'valorDaParcela' => "1.231,23",
        'dataPrimeiroVencimento' => "2023-01-21",
        'descontoNoBoleto' => true,
        'pagamentoComCartao' => true
    ]);

    assertEquals(204, $request->getStatusCode());
})->skip('Ainda falta corrigir o teste');
