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
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa                                      |
// |                                                                                                                                                                      |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+
// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto CEF: Elizeu Alcantara                         |
// +----------------------------------------------------------------------+
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="generator" content=
        "HTML Tidy for HTML5 for Linux version 5.7.28">
        <title><?php echo $dadosboleto["identificacao"]; ?></title>
        <meta charset="UTF-8">
        <meta name="Generator" content=
        "Projeto BoletoPHP - www.boletophp.com.br - Licença GPL">
        <style type="text/css">
            body{
                top:0px;
                left:0px;
                margin: 50px 0px 0px 150px;
                -webkit-transform: scale(1.23,1.2);
                -moz-transform: scale(1.23,1.2);
                -ms-transform: scale(1.23,1.2);   /* IE9 */
                -o-transform: scale(1.23,1.2);   /* Opera 10.5+ */
                transform: scale(1.23,1.2);   /* IE8+ - must be on one line, unfortunately */
                -ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.5, M12=0, M21=0, M22=0.5, SizingMethod='auto expand')";

                /* IE6 and 7 */
                filter: progid:DXImageTransform.Microsoft.Matrix(
                    M11=0.5,
                    M12=0,
                    M21=0,
                    M22=0.5,
                    SizingMethod='auto expand');

                background-color: #FFFFFF;
                color: #000000;
            }

            table.c15 {
                width:1100px;
                margin-left:-34px
            }
            div.c14 {
                margin-left:5mm;
            }
            span.c13 {
                margin-left:5mm;
            }
            span.c12 {
                float:right;
                margin-right:190px;
            }
            span.c11 {
                float:left;
            }
            span.c10 {
                font-size: 16.7px;
                width: 470px;
                display: block;
            }
            td.c9 {
                border-right:1px dashed #000;
                vertical-align:top;
                width:200px
            }
            table.c8 {
                width:200px
            }
            td.c7 {
                border-bottom:0px solid #000;
                height:35px;
            }
            span.c6 {
                height:15px;
                width:150px;
            }
            td.c5 {
                border-bottom:1px solid #000;
                height:15px;
            }
            td.c4 {
                border-bottom:1px solid #000;
                height:50px;
            }
            span.c3 {
                font-size:7px;
            }
            td.c2 {
                border-bottom:1px solid #000;
                height:42px;
            }
            img.c1 {
                float:left;
            }
        </style>
    </head>
    <body>
        <table cellspacing="0" cellpadding="3" class="c15" border="0" style="width: 100%;">
            <tr>
                <td class="c9">
                <table cellspacing="0" cellpadding="0" border="0" class="c8">
                <tbody>
                <tr>
                <td valign="top" class="c2"><img src="./lib/boletos/imagens/logocaixa.jpg" width=
                "150" height="40" border="0" class="c1"></td>
                </tr>
                <tr>
                <td valign="top" class="c4"><span class="cp1">SAC CAIXA</span><br>
                <span class="cp1 c3">Informações,reclamações,sugestões,elogios:
                0800 726 0101<br>
                Pessoas com deficiência auditiva ou de fala: 0800 726 2492<br>
                Ouvidoria: 0800 725 7474<br>
                caixa.org.br</span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">Vencimento</span>
                <span class=
                "ct1"><?php echo $dadosboleto["data_vencimento"]; ?></span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">CPF / CNPJ do
                Beneficiário</span> <span class=
                "ct1"><?php echo $dadosboleto["cpf_cnpj"]; ?></span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">Agência / Código
                Cedente</span> <span class=
                "ct1"><?php echo $dadosboleto["agencia_codigo"]; ?></span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">Nosso Número</span>
                <span class=
                "ct1"><?php echo $dadosboleto["nosso_numero"]; ?></span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">(=)Valor
                Documento</span> <span class=
                "ct1"><?php echo $dadosboleto["valor_boleto"]; ?></span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">(-)Desconto /
                Abatimentos</span> <span class="ct1">&nbsp;</span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">(-)Outras
                Deduções</span> <span class="ct1">&nbsp;</span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">(+)Mora /
                Multa</span> <span class="ct1">&nbsp;</span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">(+)Outros
                Acréscimos</span> <span class="ct1">&nbsp;</span></td>
                </tr>
                <tr>
                <td valign="top" class="c5"><span class="cp1">(=)Valor
                Cobrado</span> <span class="ct1">&nbsp;</span></td>
                </tr>
                <tr>
                <td valign="top" class="c7"><span class="cp1">RECIBO DO
                PAGADOR</span><br>
                <span class=
                "cp1 c6"><?php echo "N. Documento: ".$dadosboleto["numero_documento"] . "<br />" . $dadosboleto["sacado"] . "<br />" . $dadosboleto["cpf"]; ?></span></td>
                </tr>
                </tbody>
                </table>
                </td>
                <!--<td>&nbsp;</td>-->
                <td>
                    <table cellspacing="0" cellpadding="0" width="680" border="0">
                    <tr>
                    <td class="cp" width="150"><span class="campo"><img src=
                    "./lib/boletos/imagens/logocaixa.jpg" width="150" height="40" border=
                    "0"></span></td>
                    <td width="3" valign="bottom"><img height="22" src="./lib/boletos/imagens/3.png"
                    width="2" border="0"></td>
                    <td class="cpt" width="58" valign="bottom">
                    <div align="center">
                    <span><?php echo $dadosboleto["codigo_banco_com_dv"] ?></span></div>
                    </td>
                    <td width="3" valign="bottom"><img height="22" src="./lib/boletos/imagens/3.png"
                    width="2" border="0"></td>
                    <td class="ld" align="right" width="467" valign="bottom">
                    <span class=
                    "ld campotitulo c10"><?php echo $dadosboleto["linha_digitavel"] ?></span></td>
                    </tr>
                    <tbody>
                    <tr>
                    <td colspan="5"><img height="2" src="./lib/boletos/imagens/2.png" width="680"
                    border="0"></td>
                    </tr>
                    </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="486" height="13">Local de
                    pagamento</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="180" height="13">Vencimento</td>
                    </tr>
                    <tr>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="486" height="12">
                    PREFERENCIALMENTE NAS CASAS LOTÉRICAS ATÉ O VALOR LIMITE</td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" align="right" width="180" height="12">
                    <span class=
                    "campo"><?php echo ($data_venc != "") ? $dadosboleto["data_vencimento"] : "Contra Apresentação" ?></span></td>
                    </tr>
                    <tr>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="486" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="486" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="180" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                    </tr>
                    </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="299" height="13">
                    Beneficiário</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="180" height="13">CPF / CNPJ do
                    Beneficiário</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="180" height="13">Agência/Código
                    Beneficiário</td>
                    </tr>
                    <tr>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="299" height="12"><span class=
                    "campo"><?php echo $dadosboleto["cedente"] ?></span></td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="180" height="12"><span class=
                    "campo"><?php echo $dadosboleto["cpf_cnpj"] ?></span></td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" align="right" width="180" height="12">
                    <span class=
                    "campo"><?php echo $dadosboleto["agencia_codigo"] ?></span></td>
                    </tr>
                    <tr>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="299" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="299" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="180" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="180" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                    </tr>
                    </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="638" height="13">Endereço do
                    Beneficiário</td>
                    </tr>
                    <tr>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="638" height="12"><span class=
                    "campo"><?php echo $dadosboleto["endereco"] ?></span></td>
                    </tr>
                    <tr>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="675" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="675" border="0"></td>
                    </tr>
                    </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="113" height="13">Data do
                    documento</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="147" height="13">Nr do
                    documento</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="62" height="13">Espécie
                    doc.</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="34" height="13">Aceite</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="102" height="13">Data
                    processamento</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="180" height="13">Nosso
                    número</td>
                    </tr>
                    <tr>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="113" height="12">
                    <div align="left"><span class=
                    "campo"><?php echo $dadosboleto["data_documento"] ?></span></div>
                    </td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="147" height="12"><span class=
                    "campo"><?php echo $dadosboleto["numero_documento"] ?></span></td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="62" height="12">
                    <div align="left"><span class=
                    "campo"><?php echo $dadosboleto["especie_doc"] ?></span></div>
                    </td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="34" height="12">
                    <div align="left"><span class=
                    "campo"><?php echo $dadosboleto["aceite"] ?></span></div>
                    </td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="102" height="12">
                    <div align="left"><span class=
                    "campo"><?php echo $dadosboleto["data_processamento"] ?></span></div>
                    </td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" align="right" width="180" height="12">
                    <span class=
                    "campo"><?php echo $dadosboleto["nosso_numero"]; ?></span></td>
                    </tr>
                    <tr>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="113" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="113" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="133" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="147" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="62" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="62" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="34" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="34" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="102" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="102" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="180" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                    </tr>
                    </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" colspan="3" height="13">Uso do
                    banco</td>
                    <td class="ct" valign="top" height="13" width="7"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="87" height="13">Carteira</td>
                    <td class="ct" valign="top" height="13" width="7"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="53" height="13">Espécie</td>
                    <td class="ct" valign="top" height="13" width="7"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="103" height="13">Quantidade</td>
                    <td class="ct" valign="top" height="13" width="7"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="102" height="13">Valor
                    Documento</td>
                    <td class="ct" valign="top" width="7" height="13"><img height="13"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="ct" valign="top" width="180" height="13">(=) Valor
                    documento</td>
                    </tr>
                    <tr>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td valign="top" class="cp" height="12" colspan="3">
                    <div align="left"></div>
                    </td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="87">
                    <div align="left"><span class=
                    "campo"><?php echo $dadosboleto["carteira"] ?></span></div>
                    </td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="53">
                    <div align="left"><span class=
                    "campo"><?php echo $dadosboleto["especie"] ?></span></div>
                    </td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="103"><span class=
                    "campo"><?php echo $dadosboleto["quantidade"] ?></span></td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" width="102"><span class=
                    "campo"><?php echo $dadosboleto["valor_unitario"] ?></span></td>
                    <td class="cp" valign="top" width="7" height="12"><img height="12"
                    src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                    <td class="cp" valign="top" align="right" width="180" height="12">
                    <span class=
                    "campo"><?php echo $dadosboleto["valor_boleto"] ?></span></td>
                    </tr>
                    <tr>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="75" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="31" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="31" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="83" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="87" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="43" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="53" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="103" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="103" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="102" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="102" border="0"></td>
                    <td valign="top" width="7" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                    <td valign="top" width="180" height="1"><img height="1" src=
                    "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                    </tr>
                    </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" width="680" border="0">
                        <tbody>
                            <tr>
                                <td align="right" width="10">
                                    <table cellspacing="0" cellpadding="0" border="0" align="left">
                                        <tbody>
                                            <tr>
                                                <td class="ct" valign="top" width="7" height="13"><img height="13"
                                                src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            </tr>
                                            <tr>
                                                <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="7" height="1"><img height="1" src="./lib/boletos/imagens/2.png" width="1" border="0"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td valign="top" width="488" rowspan="5"><span>Instruções (Texto de
                                Responsabilidade do Beneficiário)</span><br>
                                <br>
                                <span class=
                                "cp"><span><?php echo $dadosboleto["instrucoes1"]; ?><br>
                                <?php echo $dadosboleto["instrucoes2"]; ?><br>
                                <?php echo $dadosboleto["instrucoes3"]; ?><br>
                                <?php echo $dadosboleto["instrucoes4"]; ?></span><br>
                                <br></span></td>
                                <td align="right" width="188">
                                    <table cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr>
                                                <td class="ct" valign="top" width="7" height="13"><img height="13" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="ct" valign="top" width="180" height="13">(-) Desconto / Abatimentos</td>
                                            </tr>
                                            <tr>
                                                <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="cp" valign="top" align="right" width="180" height="12"></td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="7" height="1"><img height="1" src="./lib/boletos/imagens/2.png" width="7" border="0"></td>
                                                <td valign="top" width="180" height="1"><img height="1" src="./lib/boletos/imagens/2.png" width="180" border="0"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" width="10">
                                    <table cellspacing="0" cellpadding="0" border="0" align="left">
                                        <tbody>
                                        <tr>
                                            <td class="ct" valign="top" width="7" height="13"><img height="13"
                                        src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                        </tr>
                                        <tr>
                                            <td class="cp" valign="top" width="7" height="12"><img height="12"
                                        src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                        </tr>
                                        <tr>
                                            <td valign="top" width="7" height="1"><img height="1" src=
                                        "./lib/boletos/imagens/2.png" width="1" border="0"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td align="right" width="188">
                                    <table cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                        <tr>
                                            <td class="ct" valign="top" width="7" height="13"><img height="13" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            <td class="ct" valign="top" width="180" height="13">(-) Outras deduções</td>
                                        </tr>
                                        <tr>
                                            <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            <td class="cp" valign="top" align="right" width="180" height="12">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top" width="7" height="1"><img height="1" src= "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                                            <td valign="top" width="180" height="1"><img height="1" src= "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" width="10">
                                    <table cellspacing="0" cellpadding="0" border="0" align="left">
                                        <tbody>
                                        <tr>
                                            <td class="ct" valign="top" width="7" height="13"><img height="13" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                        </tr>
                                        <tr>
                                            <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                        </tr>
                                        <tr>
                                            <td valign="top" width="7" height="1"><img height="1" src="./lib/boletos/imagens/2.png" width="1" border="0"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td align="right" width="188">
                                    <table cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr>
                                                <td class="ct" valign="top" width="7" height="13"><img height="13" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="ct" valign="top" width="180" height="13">(+) Mora / Multa</td>
                                            </tr>
                                            <tr>
                                                <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="cp" valign="top" align="right" width="180" height="12">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="7" height="1"><img height="1" src= "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                                                <td valign="top" width="180" height="1"><img height="1" src= "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" width="10">
                                    <table cellspacing="0" cellpadding="0" border="0" align="left">
                                        <tbody>
                                            <tr>
                                                <td class="ct" valign="top" width="7" height="13"><img height="13" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            </tr>
                                            <tr>
                                                <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="7" height="1"><img height="1" src="./lib/boletos/imagens/2.png" width="1" border="0"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td align="right" width="188">
                                    <table cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr>
                                                <td class="ct" valign="top" width="7" height="13"><img height="13"
                                                src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="ct" valign="top" width="180" height="13">(+) Outros
                                                acréscimos</td>
                                            </tr>
                                            <tr>
                                                <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="cp" valign="top" align="right" width="180" height="12"></td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="7" height="1"><img height="1" src="./lib/boletos/imagens/2.png" width="7" border="0"></td>
                                                <td valign="top" width="180" height="1"><img height="1" src="./lib/boletos/imagens/2.png" width="180" border="0"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" width="10">
                                    <table cellspacing="0" cellpadding="0" border="0" align="left">
                                        <tbody>
                                            <tr>
                                                <td class="ct" valign="top" width="7" height="13"><img height="13" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            </tr>
                                            <tr>
                                                <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td align="right" width="188">
                                    <table cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr>
                                                <td class="ct" valign="top" width="7" height="13"><img height="13" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="ct" valign="top" width="180" height="13">(=) Valor cobrado</td>
                                            </tr>
                                            <tr>
                                                <td class="cp" valign="top" width="7" height="12"><img height="12" src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                                <td class="cp" valign="top" align="right" width="180" height="12"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" width="680" border="0">
                        <tbody>
                            <tr>
                                <td valign="top" width="680" height="1"><img height="1" src=
                            "./lib/boletos/imagens/2.png" width="680" border="0"></td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                            <tr>
                                <td class="ct" valign="top" width="7" height="13"><img height="13"
                                src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                <td class="ct" valign="top" width="679" height="13">Pagador</td>
                            </tr>
                            <tr>
                                <td class="cp" valign="top" width="7" height="12"><img height="12"
                                src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                <td class="cp" valign="top" width="679" height="12"><span class=
                                "campo c11"><?php echo substr($dadosboleto["sacado"],0,30). ' (CPF: '.$dadosboleto["cpf"].')'; ?></span>
                                <span class=
                                "campo c12"><?php echo "Aluno: " . substr($dadosboleto["nomealuno"], 0, 32) ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                            <tr>
                                <td class="cp" valign="top" width="7" height="12"><img height="12"
                                src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                <td class="cp" valign="top" width="679" height="12"><span class=
                                "campo c11"><?php echo $dadosboleto["endereco1"] ?></span>
                                <span class=
                                "campo c12"><?php echo substr($dadosboleto["cursoturma"], 0, 30) ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                            <tr>
                                <td class="ct" valign="top" width="7" height="13"><img height="13"
                                src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                <td class="cp" valign="top" width="486" height="13"><span class=
                                "campo"><?php echo $dadosboleto["endereco2"] ?></span></td>
                                <td class="ct" valign="top" width="7" height="13"><img height="13"
                                src="./lib/boletos/imagens/1.png" width="1" border="0"></td>
                                <td class="ct" valign="top" width="180" height="13">Cód. baixa</td>
                            </tr>
                            <tr>
                                <td valign="top" width="7" height="1"><img height="1" src=
                                "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                                <td valign="top" width="486" height="1"><img height="1" src=
                                "./lib/boletos/imagens/2.png" width="486" border="0"></td>
                                <td valign="top" width="7" height="1"><img height="1" src=
                                "./lib/boletos/imagens/2.png" width="7" border="0"></td>
                                <td valign="top" width="180" height="1"><img height="1" src=
                            "./lib/boletos/imagens/2.png" width="180" border="0"></td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" border="0" width="666">
                        <tbody>
                            <tr>
                                <td class="ct" width="7" height="12"></td>
                                <td class="ct" width="409"><span class=
                                "c13">Pagador/Avalista</span></td>
                                <td class="ct" width="250">
                                <div align="right">Autenticação mecânica - <b class="cp">Ficha de
                                Compensação</b></div>
                                </td>
                            </tr>
                            <tr>
                                <td class="ct" colspan="3"></td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" width="666" border="0">
                        <tbody>
                            <tr>
                                <td height=50 style="text-align: left;">
                                    <barcode code="<?=$dadosboleto["codigo_barras"]?>" type="I25" height="1.5" style="padding: 0;margin: 10px -10px;" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing="0" cellpadding="0" width="666" border="0">
                        <tr>
                            <td class="ct" width="666"></td>
                        </tr>
                        <tbody>
                            <tr>
                                <td class="ct" width="666">
                                    <div align="right">Corte na linha pontilhada</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="ct" width="666"><img height="1" src="./lib/boletos/imagens/6.png"
                                width="665" border="0"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
