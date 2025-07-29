<?php

// +----------------------------------------------------------------------+
// | BoletoPhp - Vers�o Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo est� dispon�vel sob a Licen�a GPL dispon�vel pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Voc� deve ter recebido uma c�pia da GNU Public License junto com     |
// | esse pacote; se n�o, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colabora��es de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do    |
// | PHPBoleto de Jo�o Prado Maia e Pablo Martins F. Costa                      |
// |                                                                                                        |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Bradesco: Ramon Soares                                  |
// +----------------------------------------------------------------------+

if (!function_exists('digitoVerificador_nossonumero_bradesco')) {
    function digitoVerificador_nossonumero_bradesco($numero)
    {
        $resto2 = modulo_11($numero, 7, 1);
        $digito = 11 - $resto2;
        if ($digito == 10) {
            $dv = "P";
        } elseif ($digito == 11) {
            $dv = 0;
        } else {
            $dv = $digito;
        }
        return $dv;
    }
}

if (!function_exists('monta_linha_digitavel_bradesco')) {
    function monta_linha_digitavel_bradesco($codigo)
    {
        // 01-03 -> C�digo do banco sem o digito
        // 04-04 -> C�digo da Moeda (9-Real)
        // 05-05 -> D�gito verificador do c�digo de barras
        // 06-09 -> Fator de vencimento
        // 10-19 -> Valor Nominal do T�tulo
        // 20-44 -> Campo Livre (Abaixo)
        // 20-23 -> C�digo da Agencia (sem d�gito)
        // 24-05 -> N�mero da Carteira
        // 26-36 -> Nosso N�mero (sem d�gito)
        // 37-43 -> Conta do Cedente (sem d�gito)
        // 44-44 -> Zero (Fixo)

        // 1. Campo - composto pelo c�digo do banco, c�digo da mo�da, as cinco primeiras posi��es
        // do campo livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 0, 4);                            // Numero do banco + Carteira
        $p2 = substr($codigo, 19, 5);                       // 5 primeiras posi��es do campo livre
        $p3 = modulo_10("$p1$p2");                      // Digito do campo 1
        $p4 = "$p1$p2$p3";                              // Uni�o
        $campo1 = substr($p4, 0, 5) . '.' . substr($p4, 5);

        // 2. Campo - composto pelas posi�oes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 24, 10);                      //Posi��es de 6 a 15 do campo livre
        $p2 = modulo_10($p1);                               //Digito do campo 2
        $p3 = "$p1$p2";
        $campo2 = substr($p3, 0, 5) . '.' . substr($p3, 5);

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 34, 10);                      //Posi��es de 16 a 25 do campo livre
        $p2 = modulo_10($p1);                               //Digito do Campo 3
        $p3 = "$p1$p2";
        $campo3 = substr($p3, 0, 5) . '.' . substr($p3, 5);

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

$codigobanco = "237";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//agencia � 4 digitos
$agencia = formata_numero($dadosboleto["agencia"], 4, 0);
//conta � 6 digitos
$conta = formata_numero($dadosboleto["conta"], 6, 0);
//dv da conta
$conta_dv = formata_numero($dadosboleto["conta_dv"], 1, 0);
//carteira � 2 caracteres
$carteira = $dadosboleto["carteira"];

//nosso n�mero (sem dv) � 11 digitos
$nnum = formata_numero($dadosboleto["carteira"], 2, 0) . formata_numero($dadosboleto["nosso_numero"], 11, 0);
//dv do nosso n�mero
$dv_nosso_numero = digitoVerificador_nossonumero_bradesco($nnum);

//conta cedente (sem dv) � 7 digitos
$conta_cedente = formata_numero($dadosboleto["conta_cedente"], 7, 0);         // MODIFIQUEI AQUI EM 06/09
//dv da conta cedente
$conta_cedente_dv = formata_numero($dadosboleto["conta_cedente_dv"], 1, 0);       // MODIFIQUEI AQUI EM 06/09

//$ag_contacedente = $agencia . $conta_cedente;



// 43 numeros para o calculo do digito verificador do codigo de barras
$dv = digitoVerificador_barra("$codigobanco$nummoeda$fator_vencimento$valor$agencia$nnum$conta_cedente" . '0');
// Numero para o codigo de barras com 44 digitos
$linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$agencia$nnum$conta_cedente" . "0";

$nossonumero = substr($nnum, 0, 2) . '/' . substr($nnum, 2) . '-' . $dv_nosso_numero;
$agencia_codigo = $agencia . "-" . $dadosboleto["agencia_dv"] . " / " . $conta_cedente . "-" . $cta[1];
//$agencia_codigo = $agencia." / ".formata_numero( $conta."-".modulo_10($agencia.$conta),7,0);

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_bradesco($linha);
$dadosboleto["agencia_codigo"] = $agencia_codigo;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
