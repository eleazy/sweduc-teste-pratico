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
// | PHPBoleto de Jo�o Prado Maia e Pablo Martins F. Costa                |
// |                                                                                                        |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>         |
// | Desenvolvimento Boleto Santander-Banespa : Fabio R. Lenharo                    |
// +----------------------------------------------------------------------------+

use App\Model\Financeiro\Pix;

if (!function_exists('monta_linha_digitavel_santander')) {
    function monta_linha_digitavel_santander($codigo)
    {
        // Posição Conteúdo
        // 1 a 3    N�mero do banco
        // 4        C�digo da Moeda - 9 para Real ou 8 - outras moedas
        // 5        Fixo "9'
        // 6 a 9    PSK - codigo cliente (4 primeiros digitos)
        // 10 a 12  Restante do PSK (3 digitos)
        // 13 a 19  7 primeiros digitos do Nosso Numero
        // 20 a 25  Restante do Nosso numero (8 digitos) - total 13 (incluindo digito verificador)
        // 26 a 26  IOS
        // 27 a 29  Tipo Modalidade Carteira
        // 30 a 30  D�gito verificador do c�digo de barras
        // 31 a 34  Fator de vencimento (qtdade de dias desde 07/10/1997 at� a data de vencimento)
        // 35 a 44  Valor do t�tulo

        // 1. Primeiro Grupo - composto pelo c�digo do banco, c�digo da mo�da, Valor Fixo "9"
        // e 4 primeiros digitos do PSK (codigo do cliente) e DV (modulo10) deste campo
        $campo1 = substr($codigo, 0, 3) . substr($codigo, 3, 1) . substr($codigo, 19, 1) . substr($codigo, 20, 4);
        $campo1 = $campo1 . modulo_10($campo1);
        $campo1 = substr($campo1, 0, 5) . '.' . substr($campo1, 5);

        // 2. Segundo Grupo - composto pelas 3 �ltimas posi�oes do PSK e 7 primeiros d�gitos do Nosso N�mero
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

if (!function_exists('mascara_linha_digitavel_santander')) {
    function mascara_linha_digitavel_santander($codigo)
    {
        $campo1 = substr($codigo, 0, 5) . "." . substr($codigo, 5, 5);
        $campo2 = substr($codigo, 10, 5) . "." . substr($codigo, 15, 6);
        $campo3 = substr($codigo, 21, 5) . "." . substr($codigo, 26, 6);
        $digito = substr($codigo, 32, 1);
        $final  = substr($codigo, 33);

        return "$campo1 $campo2 $campo3 $digito $final";
    }
}

$codigobanco = "033"; //Antigamente era 353
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fixo = "9";   // Numero fixo para a posi��o 05-05
$ios = "0";   // IOS - somente para Seguradoras (Se 7% informar 7, limitado 9%)
                   // Demais clientes usar 0 (zero)
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//Modalidade Carteira
$carteira = $dadosboleto["carteira"];
//codigocedente deve possuir 7 caracteres
$codigocliente = formata_numero($dadosboleto["codigo_cliente"], 7, 0);

//nosso n�mero (sem dv) � 11 digitos
$nnum = formata_numero($dadosboleto["nosso_numero"], 7, 0);
//dv do nosso n�mero
$dv_nosso_numero = modulo_11($nnum, 9, 0);

// nosso n�mero (com dvs) s�o 13 digitos
if (strlen($nnum . $dv_nosso_numero) > 13) {
    throw new \RuntimeException('O tamanho do nosso número excede o esperado');
}
$nossonumero = str_pad($nnum . $dv_nosso_numero, 13, '0', STR_PAD_LEFT);

$vencimento = $dadosboleto["data_vencimento"];

$vencjuliano = dataJuliano($vencimento);

// 43 numeros para o calculo do digito verificador do codigo de barras
$barra = "$codigobanco$nummoeda$fator_vencimento$valor$fixo$codigocliente$nossonumero$ios$carteira";

//$barra = "$codigobanco$nummoeda$fixo$codigocliente$nossonumero$ios$carteira";
$dv = digitoVerificador_barra($barra);
// Numero para o codigo de barras com 44 digitos
$linha = substr($barra, 0, 4) . $dv . substr($barra, 4);

$tem_pix = str_contains($rowb['tipo'], 'pix');
if ($tem_pix) {
    $pix = Pix::where('alunos_fichafinanceira_id', $row['afid'])->first();

    $dadosboleto["codigo_barras"] = $pix->barCode;
    $dadosboleto["linha_digitavel"] = mascara_linha_digitavel_santander($pix->digitableLine);
} else {
    $dadosboleto["codigo_barras"] = $linha;
    $dadosboleto["linha_digitavel"] = monta_linha_digitavel_santander($linha);
}

$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
