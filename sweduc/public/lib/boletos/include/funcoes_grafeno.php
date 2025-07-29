<?php

if (!function_exists('monta_linha_digitavel_grafeno')) {
    function monta_linha_digitavel_grafeno($codigo)
    {
        // campo 1
        $banco    = substr($codigo, 0, 3); //  Identificação do Banco (Sem DV)
        $moeda    = substr($codigo, 3, 1); //  Moeda
        $cincop   = substr($codigo, 19, 5); // Cinco primeiras posições do campo livre
        $dv1      = modulo_10($banco . $moeda . $cincop); // Dígito verificador do primeiro campo

        // campo 2
        $sextaadecimaquinta  = substr($codigo, 24, 10); // 6a a 15a posições do campo livre
        $dv2                 = modulo_10($sextaadecimaquinta);     // Dígito verificador do segundo campo

        // campo 3
        $decima16a25 = substr($codigo, 34, 10); // 16a a 25a posições do campo livre
        $dv3         = modulo_10($decima16a25); //Dígito verificador do terceiro campo

        // campo 4
        $dv4      = substr($codigo, 4, 1); // Dígito verificador geral

        // campo 5
        $fator  = substr($codigo, 5, 4); // Fator de vencimento
        $valor  = substr($codigo, 9, 10); // Valor nominal do título

        $campo1 = substr($banco . $moeda . $cincop . $dv1, 0, 5) . '.' . substr($banco . $moeda . $cincop . $dv1, 5, 5);
        $campo2 = substr($sextaadecimaquinta . $dv2, 0, 5) . '.' . substr($sextaadecimaquinta . $dv2, 5, 6);
        $campo3 = substr($decima16a25 . $dv3, 0, 5) . '.' . substr($decima16a25 . $dv3, 5, 6);
        $campo4 = $dv4;
        $campo5 = $fator . $valor;

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }
}

$codigobanco = "247";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "7";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//agencia 4 digitos
$agencia = formata_numero($dadosboleto["agencia"], 4, 0);
//conta 5 digitos + 1 do dv
$conta = formata_numero($dadosboleto["conta"], 5, 0);
$conta_dv = formata_numero($dadosboleto["conta_dv"], 1, 0);

//carteira ??
$carteira = $dadosboleto["carteira"];

$nnum = formata_numero($dadosboleto["nosso_numero"], 8, 0);

// Nosso Número no máximo 11 digitos (No caso é o número deles né)
$nossonumero = formata_numero($dadosboleto["identificacao_banco"], 11, 0);

// Número de operacao no maximo 7 digitos
$numeroOperacao = formata_numero($dadosboleto["numero_operacao"], 7, 0);

// # Montagem do Código de Barras (antes do Dv)
// 001 a 003 Identificação do Banco (Sem DV)
// 004 a 004 Moeda
// 005 a 005 Dígito verificador do código de barras (Só pega depois)
// 006 a 009 Fator de Vencimento
// 010 a 019 Valor nominal do título
// 020 a 044 Campo livre

// # Campo Livre (20 caracteres finais do Cod. de Barras)
// 020 a 023 Código da Agência (Sem DV)
// 024 a 025 Número da Carteira do Título
// 026 a 036 Número do Nosso Número (Sem DV)
// 037 a 043 Número da Conta Corrente (Sem DV)
// 044 a 044 Zero (Porém carteira tem na verdade 3 digitos)

$codigo_barras = $codigobanco . $nummoeda . $fator_vencimento . $valor . $agencia . $carteira . $nossonumero . $numeroOperacao;

// 43 numeros para o calculo do digito verificador
$dv = digitoVerificador_barra($codigo_barras);

// Numero para o codigo de barras com 44 digitos
$linha = substr($codigo_barras, 0, 4) . $dv . substr($codigo_barras, 4, 43);

$agencia_codigo = $agencia . " / " . $conta . "-" . modulo_10($agencia . $conta);

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_grafeno($linha); // verificar
$dadosboleto["agencia_codigo"] = $agencia_codigo ;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
