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
// |                                                                      |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Ita�: Glauber Portella                        |
// +----------------------------------------------------------------------+

// Alterada por Glauber Portella para especifica��o do Ita�
if (!function_exists('monta_linha_digitavel_itau')) {
    function monta_linha_digitavel_itau($codigo)
    {
        // campo 1
        $banco    = substr($codigo, 0, 3);
        $moeda    = substr($codigo, 3, 1);
        $ccc      = substr($codigo, 19, 3);
        $ddnnum   = substr($codigo, 22, 2);
        $dv1      = modulo_10($banco . $moeda . $ccc . $ddnnum);

        // campo 2
        $resnnum  = substr($codigo, 24, 6);
        $dac1     = substr($codigo, 30, 1);
        $dddag    = substr($codigo, 31, 3);
        $dv2      = modulo_10($resnnum . $dac1 . $dddag);

            // campo 3
        $resag    = substr($codigo, 34, 1);
        $contadac = substr($codigo, 35, 6); //substr($codigo,35,5).modulo_10(substr($codigo,35,5));
        $zeros    = substr($codigo, 41, 3);
        $dv3      = modulo_10($resag . $contadac . $zeros);
        // campo 4
        $dv4      = substr($codigo, 4, 1);
        // campo 5
        $fator    = substr($codigo, 5, 4);
        $valor    = substr($codigo, 9, 10);

        $campo1 = substr($banco . $moeda . $ccc . $ddnnum . $dv1, 0, 5) . '.' . substr($banco . $moeda . $ccc . $ddnnum . $dv1, 5, 5);
        $campo2 = substr($resnnum . $dac1 . $dddag . $dv2, 0, 5) . '.' . substr($resnnum . $dac1 . $dddag . $dv2, 5, 6);
        $campo3 = substr($resag . $contadac . $zeros . $dv3, 0, 5) . '.' . substr($resag . $contadac . $zeros . $dv3, 5, 6);
        $campo4 = $dv4;
        $campo5 = $fator . $valor;

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }
}

$codigobanco = "341";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//agencia � 4 digitos
$agencia = formata_numero($dadosboleto["agencia"], 4, 0);
//conta � 5 digitos + 1 do dv
$conta = formata_numero($dadosboleto["conta"], 5, 0);
$conta_dv = formata_numero($dadosboleto["conta_dv"], 1, 0);
//carteira 175
$carteira = $dadosboleto["carteira"];
//nosso_numero no maximo 8 digitos
$nnum = formata_numero($dadosboleto["nosso_numero"], 8, 0);

$codigo_barras = $codigobanco . $nummoeda . $fator_vencimento . $valor . $carteira . $nnum . modulo_10($agencia . $conta . $carteira . $nnum) . $agencia . $conta . modulo_10($agencia . $conta) . '000';

// echo "<br>".$codigobanco."*".$nummoeda."*".$fator_vencimento."*".$valor."*".$carteira."*".$nnum."*".modulo_10($agencia.$conta.$carteira.$nnum)."*".$agencia."*".$conta."*".modulo_10($agencia.$conta)."<br>";

// 43 numeros para o calculo do digito verificador
$dv = digitoVerificador_barra($codigo_barras);
// Numero para o codigo de barras com 44 digitos
$linha = substr($codigo_barras, 0, 4) . $dv . substr($codigo_barras, 4, 43);

$nossonumero = $carteira . '/' . $nnum . '-' . modulo_10($agencia . $conta . $carteira . $nnum);
$agencia_codigo = $agencia . " / " . $conta . "-" . modulo_10($agencia . $conta);

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_itau($linha); // verificar
$dadosboleto["agencia_codigo"] = $agencia_codigo ;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
