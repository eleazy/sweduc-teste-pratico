<?php

// +----------------------------------------------------------------------+
// | BoletoPhp - Versão Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo está disponível sob a Licença GPL disponível pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Você deve ter recebido uma cópia da GNU Public License junto com     |
// | esse pacote; se não, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colaborações de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do    |
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa                |
// |                                                                                                        |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>         |
// | Desenvolvimento Boleto Santander-Banespa : Fabio R. Lenharo                    |
// +----------------------------------------------------------------------------+

if (!function_exists('monta_linha_digitavel_carne')) {
    function monta_linha_digitavel_carne($codigo)
    {
        // Posição  Conteúdo
        // 1 a 3    Número do banco
        // 4        Código da Moeda - 9 para Real ou 8 - outras moedas
        // 5        Fixo "9'
        // 6 a 9    PSK - codigo cliente (4 primeiros digitos)
        // 10 a 12  Restante do PSK (3 digitos)
        // 13 a 19  7 primeiros digitos do Nosso Numero
        // 20 a 25  Restante do Nosso numero (8 digitos) - total 13 (incluindo digito verificador)
        // 26 a 26  IOS
        // 27 a 29  Tipo Modalidade Carteira
        // 30 a 30  Dígito verificador do código de barras
        // 31 a 34  Fator de vencimento (qtdade de dias desde 07/10/1997 até a data de vencimento)
        // 35 a 44  Valor do título

        // 1. Primeiro Grupo - composto pelo código do banco, código da moéda, Valor Fixo "9"
        // e 4 primeiros digitos do PSK (codigo do cliente) e DV (modulo10) deste campo
        $campo1 = substr($codigo, 0, 3) . substr($codigo, 3, 1) . substr($codigo, 19, 1) . substr($codigo, 20, 4);
        $campo1 = $campo1 . modulo_10($campo1);
        $campo1 = substr($campo1, 0, 5) . '.' . substr($campo1, 5);

        // 2. Segundo Grupo - composto pelas 3 últimas posiçoes do PSK e 7 primeiros dígitos do Nosso Número
        // e DV (modulo10) deste campo
        $campo2 = substr($codigo, 24, 10);
        $campo2 = $campo2 . modulo_10($campo2);
        $campo2 = substr($campo2, 0, 5) . '.' . substr($campo2, 5);

        // 3. Terceiro Grupo - Composto por : Restante do Nosso Numero (6 digitos), IOS, Modalidade da Carteira
        // e DV (modulo10) deste campo
        $campo3 = substr($codigo, 34, 10);
        $campo3 = $campo3 . modulo_10($campo3);
        $campo3 = substr($campo3, 0, 5) . '.' . substr($campo3, 5);

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($codigo, 4, 1);

        // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 0000000000 (dez zeros).
        $campo5 = substr($codigo, 5, 4) . substr($codigo, 9, 10);

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }
}

$codigobanco = "033"; //Antigamente era 353
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fixo     = "9";   // Numero fixo para a posição 05-05
$ios      = "0";   // IOS - somente para Seguradoras (Se 7% informar 7, limitado 9%)
                   // Demais clientes usar 0 (zero)
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//Modalidade Carteira
$carteira = $dadosboleto["carteira"];
//codigocedente deve possuir 7 caracteres
$codigocliente = formata_numero($dadosboleto["codigo_cliente"], 7, 0);

//nosso número (sem dv) é 11 digitos
$nnum = formata_numero($dadosboleto["nosso_numero"], 7, 0);
//dv do nosso número
$dv_nosso_numero = modulo_11($nnum, 9, 0);
// nosso número (com dvs) são 13 digitos
$nossonumero = "00000" . $nnum . $dv_nosso_numero;

$vencimento = $dadosboleto["data_vencimento"];

$vencjuliano = dataJuliano($vencimento);

// 43 numeros para o calculo do digito verificador do codigo de barras
$barra = "$codigobanco$nummoeda$fator_vencimento$valor$fixo$codigocliente$nossonumero$ios$carteira";

//$barra = "$codigobanco$nummoeda$fixo$codigocliente$nossonumero$ios$carteira";
$dv = digitoVerificador_barra($barra);
// Numero para o codigo de barras com 44 digitos
$linha = substr($barra, 0, 4) . $dv . substr($barra, 4);

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_carne($linha);
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
