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
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa                      |
// |                                                                                                      |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto CEF SIGCB: Davi Nunes Camargo                 |
// +----------------------------------------------------------------------+

if (!function_exists('monta_linha_digitavel_cef')) {
    function monta_linha_digitavel_cef($codigo)
    {
        // Posição  Conteúdo
        // 1 a 3    Número do banco
        // 4        Código da Moeda - 9 para Real
        // 5        Digito verificador do Código de Barras
        // 6 a 9    Fator de Vencimento
        // 10 a 19  Valor (8 inteiros e 2 decimais)
        // 20 a 44  Campo Livre definido por cada banco (25 caracteres)

        // 1. Campo - composto pelo código do banco, código da moéda, as cinco primeiras posições
        // do campo livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 0, 4);
        $p2 = substr($codigo, 19, 5);
        $p3 = modulo_10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $p5 = substr($p4, 0, 5);
        $p6 = substr($p4, 5);
        $campo1 = "$p5.$p6";

        // 2. Campo - composto pelas posiçoes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 24, 10);
        $p2 = modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo2 = "$p4.$p5";

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 34, 10);
        $p2 = modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo3 = "$p4.$p5";

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($codigo, 4, 1);

        // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
        $p1 = substr($codigo, 5, 4);
        $p2 = substr($codigo, 9, 10);
        $campo5 = "$p1$p2";

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }
}

$codigobanco = "104";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//agencia é 4 digitos
$agencia = formata_numero($dadosboleto["agencia"], 4, 0);
//conta é 5 digitos
$conta = formata_numero($dadosboleto["conta"], 5, 0);
//dv da conta
$conta_dv = formata_numero($dadosboleto["conta_dv"], 1, 0);
//carteira é 2 caracteres
$carteira = $dadosboleto["carteira"];

//conta cedente (sem dv) com 6 digitos
$conta_cedente = formata_numero($dadosboleto["conta_cedente"], 6, 0);
//dv da conta cedente
$conta_cedente_dv = digitoVerificador_cedente($conta_cedente);

//campo livre (sem dv) é 24 digitos
$campo_livre = $conta_cedente . $conta_cedente_dv . formata_numero($dadosboleto["nosso_numero1"], 3, 0) . formata_numero($dadosboleto["nosso_numero_const1"], 1, 0) . formata_numero($dadosboleto["nosso_numero2"], 3, 0) . formata_numero($dadosboleto["nosso_numero_const2"], 1, 0) . formata_numero($dadosboleto["nosso_numero3"], 9, 0);
//dv do campo livre
$dv_campo_livre = digitoVerificador_nossonumero($campo_livre);
$campo_livre_com_dv = "$campo_livre$dv_campo_livre";

//nosso número (sem dv) é 17 digitos
$nnum = formata_numero($dadosboleto["nosso_numero_const1"], 1, 0) . formata_numero($dadosboleto["nosso_numero_const2"], 1, 0) . formata_numero($dadosboleto["nosso_numero1"], 3, 0) . formata_numero($dadosboleto["nosso_numero2"], 3, 0) . formata_numero($dadosboleto["nosso_numero3"], 9, 0);
//nosso número completo (com dv) com 18 digitos
$nossonumero = $nnum . digitoVerificador_nossonumero($nnum);

// 43 numeros para o calculo do digito verificador do codigo de barras
$dv = digitoVerificador_barra("$codigobanco$nummoeda$fator_vencimento$valor$campo_livre_com_dv");
// Numero para o codigo de barras com 44 digitos
$linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$campo_livre_com_dv";

$agencia_codigo = $agencia . " / " . $conta_cedente . "-" . $conta_cedente_dv;

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_cef($linha);
$dadosboleto["agencia_codigo"] = $agencia_codigo;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
