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

/*
#################################################
FUN��O DO M�DULO 11 RETIRADA DO PHPBOLETO

MODIFIQUEI ALGUMAS COISAS...

ESTA FUN��O PEGA O D�GITO VERIFICADOR:

NOSSONUMERO
AGENCIA
CONTA
CAMPO 4 DA LINHA DIGIT�VEL
#################################################
*/
if (!function_exists('modulo_11_bb2')) {
    function modulo_11_bb($num, $base = 9, $r = 0)
    {
        $numeros = [];
        $parcial = [];
        $soma = 0;
        $fator = 2;

        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                $fator = 1;
            }
            $fator++;
        }

        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;

            //corrigido
            if ($digito == 10) {
                $digito = "X";
            }

            /*
            alterado por mim, Daniel Schultz

            Vamos explicar:

            O m�dulo 11 s� gera os digitos verificadores do nossonumero,
            agencia, conta e digito verificador com codigo de barras (aquele que fica sozinho e triste na linha digit�vel)
            s� que � foi um rolo...pq ele nao podia resultar em 0, e o pessoal do phpboleto se esqueceu disso...

            No BB, os d�gitos verificadores podem ser X ou 0 (zero) para agencia, conta e nosso numero,
            mas nunca pode ser X ou 0 (zero) para a linha digit�vel, justamente por ser totalmente num�rica.

            Quando passamos os dados para a fun��o, fica assim:

            Agencia = sempre 4 digitos
            Conta = at� 8 d�gitos
            Nosso n�mero = de 1 a 17 digitos

            A unica vari�vel que passa 17 digitos � a da linha digitada, justamente por ter 43 caracteres

            Entao vamos definir ai embaixo o seguinte...

            se (strlen($num) == 43) { n�o deixar dar digito X ou 0 }
            */

            if (strlen($num) == "43") {
                //ent�o estamos checando a linha digit�vel
                if ($digito == "0" or $digito == "X" or $digito > 9) {
                    $digito = 1;
                }
            }
            return $digito;
        } elseif ($r == 1) {
            $resto = $soma % 11;
            return $resto;
        }
    }
}

/*
Montagem da linha digit�vel - Fun��o tirada do PHPBoleto
N�o mudei nada
*/
if (!function_exists('monta_linha_digitavel_bb2')) {
    function monta_linha_digitavel_bb($linha)
    {
        // Posi��o  Conte�do
        // 1 a 3    N�mero do banco
        // 4        C�digo da Moeda - 9 para Real
        // 5        Digito verificador do C�digo de Barras
        // 6 a 19   Valor (12 inteiros e 2 decimais)
        // 20 a 44  Campo Livre definido por cada banco

        // 1. Campo - composto pelo c�digo do banco, c�digo da mo�da, as cinco primeiras posi��es
        // do campo livre e DV (modulo10) deste campo
        $p1 = substr($linha, 0, 4);
        $p2 = substr($linha, 19, 5);
        $p3 = modulo_10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $p5 = substr($p4, 0, 5);
        $p6 = substr($p4, 5);
        $campo1 = "$p5.$p6";

        // 2. Campo - composto pelas posi�oes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 24, 10);
        $p2 = modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo2 = "$p4.$p5";

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 34, 10);
        $p2 = modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo3 = "$p4.$p5";

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($linha, 4, 1);

        // 5. Campo composto pelo valor nominal pelo valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
        $campo5 = substr($linha, 5, 14);

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }
}

$codigobanco = "001";
$codigo_banco_com_dv = geraCodigoBanco($codigobanco);
$nummoeda = "9";
$fator_vencimento = fator_vencimento($dadosboleto["data_vencimento"]);

//valor tem 10 digitos, sem virgula
$valor = formata_numero($dadosboleto["valor_boleto"], 10, 0, "valor");
//agencia � 4 digitos
$agencia = formata_numero($dadosboleto["agencia"], 4, 0);
//conta � 5 digitos + 1 do dv
// $conta = formata_numero($dadosboleto["conta"],5,0);
// $conta_dv = formata_numero($dadosboleto["conta_dv"],1,0);
//conta � sempre 8 digitos
$conta = formata_numero($dadosboleto["conta"], 8, 0);
//carteira 175
$carteira = $dadosboleto["carteira"];
//nosso_numero no maximo 8 digitos
// $nnum = formata_numero($dadosboleto["nosso_numero"],8,0);
//agencia e conta
$agencia_codigo = $agencia . "-" . modulo_11_bb($agencia) . " / " . $conta . "-" . modulo_11_bb($conta);
//Zeros: usado quando convenio de 7 digitos
$livre_zeros = '000000';

// Carteira 18 com Conv�nio de 7 d�gitos
// if ($dadosboleto["formatacao_convenio"] == "7") {
  $convenio = formata_numero($dadosboleto["convenio"], 7, 0, "convenio");
  // Nosso n�mero de at� 10 d�gitos
  $nossonumero = formata_numero($dadosboleto["nosso_numero"], 10, 0);
  $dv = modulo_11_bb("$codigobanco$nummoeda$fator_vencimento$valor$livre_zeros$convenio$nossonumero$carteira");
  $linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$livre_zeros$convenio$nossonumero$carteira";
  $nossonumero = $convenio . $nossonumero;
  //N�o existe DV na composi��o do nosso-n�mero para conv�nios de sete posi��es
// }

// // Carteira 17 com Conv�nio de 4 d�gitos
//   $convenio = formata_numero($dadosboleto["convenio"],4,0,"convenio");
//   // Nosso n�mero de at� 10 d�gitos
//   $nossonumero = formata_numero($dadosboleto["nosso_numero"],7,0);
//   $dv=modulo_11_bb("$codigobanco$nummoeda$fator_vencimento$valor$convenio$nossonumero$conta$carteira");
//   $linha="$codigobanco$nummoeda$dv$fator_vencimento$valor$convenio$nossonumero$conta$carteira";
//   $nossonumero = $convenio.$nossonumero;
//   //N�o existe DV na composi��o do nosso-n�mero para conv�nios de quatro posi��es

$dadosboleto["codigo_barras"] = $linha;
$dadosboleto["linha_digitavel"] = monta_linha_digitavel_bb($linha);
$dadosboleto["agencia_codigo"] = $agencia_codigo;
$dadosboleto["nosso_numero"] = $nossonumero;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;
