<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Vers?o Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo est? dispon?vel sob a Licen?a GPL dispon?vel pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Voc? deve ter recebido uma c?pia da GNU Public License junto com     |
// | esse pacote; se n?o, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colabora??es de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de Jo?o Prado Maia e Pablo Martins F. Costa				  |
// | 																	  |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordena??o Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto HSBC: Bruno Leonardo M. F. Gon?alves          |
// +----------------------------------------------------------------------+
?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<HTML>
<HEAD>
<TITLE><?php echo $dadosboleto["identificacao"]; ?></TITLE>
<!-- <META http-equiv=Content-Type content=text/html charset=ISO-8859-1> -->
<META http-equiv=Content-Type content=text/html charset=utf-8>
<meta name="Generator" content="Projeto BoletoPHP - www.boletophp.com.br - Licen?a GPL" />
<style type=text/css>

    <!--.cp {  font: bold 10px Arial; color: black}
    <!--.ct { FONT: 9px "Arial Narrow"; COLOR: #000033}
    <!--.cp1 {  font: bold 8px Arial; color: black; float:left;}
    <!--.ti {  font: 9px Arial, Helvetica, sans-serif}
    <!--.ld { font: bold 15px Arial; color: #000000}
    <!--.ct1 { FONT: bold 11px "Arial Narrow"; COLOR: #000033; float:right; margin-right:8px;margin-top:11px;}
    <!--.cn { FONT: 9px Arial; COLOR: black }
    <!--.bc { font: bold 20px Arial; color: #000000 }
    <!--.ld2 { font: bold 12px Arial; color: #000000 }
    -->

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
}
</style>
</head>

<BODY text=#000000 bgColor=#ffffff topMargin=0 rightMargin=0>




<!-- ############################################## -->


<table cellspacing=0 cellpadding=3 style="width:1100px; margin-left:-34px" border=0>
      <tr><td style="border-right:1px dashed #000;vertical-align:top;width:200px">



<!-- ############################################## -->

    <table cellspacing=0 cellpadding=0 border=0 style="width:200px">
          <tbody>

            <tr><td valign=top style="border-bottom:1px solid #000;height:42px;">
              <IMG src="imagens/logohsbc.jpg" width="144" height="30" border=0 style="float:left;">
            </td></tr>

            <!-- <tr><td valign=top style="border-bottom:1px solid #000;height:50px;">
              <span class=cp1>SAC CAIXA</span><br />
              <span class=cp1 style="font-size:7px;">
                Informações,reclamações,sugestões,elogios: 0800 726 0101<br />
                Pessoas com deficiência auditiva ou de fala: 0800 726 2492<br />
                Ouvidoria: 0800 725 7474<br />
                caixa.org.br
              </span>
            </td></tr> -->

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>Vencimento</span>
              <span class=ct1><?php echo $dadosboleto["data_vencimento"]; ?></span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>CPF / CNPJ do Beneficiário</span>
              <span class=ct1><?php echo $dadosboleto["cpf_cnpj"]; ?></span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>Agência / Código Cedente</span>
              <span class=ct1><?php echo $dadosboleto["agencia_codigo"]; ?></span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>Nosso Número</span>
              <span class=ct1><?php echo $dadosboleto["nosso_numero"]; ?></span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>(=)Valor Documento</span>
              <span class=ct1><?php echo $dadosboleto["valor_boleto"]; ?></span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>(-)Desconto / Abatimentos</span>
              <span class=ct1>&nbsp;</span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>(-)Outras Deduções</span>
              <span class=ct1>&nbsp;</span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>(+)Mora / Multa</span>
              <span class=ct1>&nbsp;</span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>(+)Outros Acréscimos</span>
              <span class=ct1>&nbsp;</span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>(=)Valor Cobrado</span>
              <span class=ct1>&nbsp;</span>
            </td></tr>

            <tr><td valign=top style="border-bottom:0px solid #000;height:35px;">
              <span class=cp1>PAGADOR</span><br />
              <span class=cp1 style="height:15px;width:150px;"><?php echo $dadosboleto["sacado"]."<br />".$dadosboleto["cpf"]; ?></span>
            </td></tr>

        </tbody>
      </table>



</td><!--<td>&nbsp;</td>--><td>
<!-- ############################################## -->

<table cellspacing=0 cellpadding=0 width=666 border=0><tr><td class=cp width=150>
  <span class="campo"><IMG
      src="imagens/logohsbc.jpg" width="144" height="30"
      border=0></span></td>
<td width=3 valign=bottom><img height=22 src=imagens/3.png width=2 border=0></td><td class=cpt width=58 valign=bottom><div align=center><font class=bc><?php echo $dadosboleto["codigo_banco_com_dv"]?></font></div></td><td width=3 valign=bottom><img height=22 src=imagens/3.png width=2 border=0></td><td class=ld align=right width=453 valign=bottom><span class=ld>
<span class="campotitulo">
<?php echo $dadosboleto["linha_digitavel"]?>
</span></span></td>
</tr><tbody><tr><td colspan=5><img height=2 src=imagens/2.png width=666 border=0></td></tr></tbody></table><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=472 height=13>Local
de pagamento</td><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>Vencimento</td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top width=472 height=12>Pagável
em qualquer Banco até o vencimento</td><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12>
  <span class="campo">
  <?php echo $dadosboleto["data_vencimento"]?>
  </span></td>
</tr><tr><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=472 height=1><img height=1 src=imagens/2.png width=472 border=0></td><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody></table><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=472 height=13>Cedente</td><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>Agência/Código
cedente</td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top width=472 height=12>
  <span class="campo">
  <?php echo $dadosboleto["cedente"]?>
  </span></td>
<td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12>
  <span class="campo">
  <?php echo $dadosboleto["agencia_codigo"]?>
  </span></td>
</tr><tr><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=472 height=1><img height=1 src=imagens/2.png width=472 border=0></td><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody></table><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13>
<img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=113 height=13>Data
do documento</td><td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=153 height=13>N<u>o</u>
documento</td><td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=62 height=13>Espécie
doc.</td><td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=34 height=13>Aceite</td><td class=ct valign=top width=7 height=13>
<img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=82 height=13>Data
processamento</td><td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>Nosso
número</td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top  width=113 height=12><div align=left>
  <span class="campo">
  <?php echo $dadosboleto["data_documento"]?>
  </span></div></td><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top width=153 height=12>
    <span class="campo">
    <?php echo $dadosboleto["numero_documento"]?>
    </span></td>
  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top  width=62 height=12><div align=left><span class="campo">
    <?php echo $dadosboleto["especie_doc"]?>
  </span>
 </div></td><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top  width=34 height=12><div align=left><span class="campo">
 <?php echo $dadosboleto["aceite"]?>
 </span>
 </div></td><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top  width=82 height=12><div align=left>
   <span class="campo">
   <?php echo $dadosboleto["data_processamento"]?>
   </span></div></td><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12>
     <span class="campo">
     <?php echo $dadosboleto["nosso_numero"]?>
     </span></td>
</tr>

<tr>
  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
  <td valign=top width=113 height=1><img height=1 src=imagens/2.png width=113 border=0></td>
  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
  <td valign=top width=153 height=1><img height=1 src=imagens/2.png width=153 border=0></td>
  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
  <td valign=top width=62 height=1><img height=1 src=imagens/2.png width=62 border=0></td>
  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
  <td valign=top width=34 height=1><img height=1 src=imagens/2.png width=34 border=0></td>
  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
  <td valign=top width=82 height=1><img height=1 src=imagens/2.png width=82 border=0></td>
  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
  <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
</tr>
</tbody>
</table>

<table cellspacing=0 cellpadding=0 border=0><tbody>
  <tr>
    <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
    <td class=ct valign=top COLSPAN="3" height=13>Uso do banco</td>
    <td class=ct valign=top height=13 width=7> <img height=13 src=imagens/1.png width=1 border=0></td>
    <td class=ct valign=top width=83 height=13>Carteira</td>
    <td class=ct valign=top height=13 width=7> <img height=13 src=imagens/1.png width=1 border=0></td>
    <td class=ct valign=top width=53 height=13>Espécie</td>
    <td class=ct valign=top height=13 width=7> <img height=13 src=imagens/1.png width=1 border=0></td>
    <td class=ct valign=top width=123 height=13>Quantidade</td>
    <td class=ct valign=top height=13 width=7> <img height=13 src=imagens/1.png width=1 border=0></td>
    <td class=ct valign=top width=72 height=13> Valor Documento</td>
    <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
    <td class=ct valign=top width=180 height=13>(=) Valor documento</td>
  </tr>
  <tr>
    <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
    <td valign=top class=cp height=12 COLSPAN="3"><div align=left>  </div></td>
    <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
    <td class=cp valign=top  width=83> <div align=left> <span class="campo">  <?php echo $dadosboleto["carteira"]?></span></div></td>
    <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
    <td class=cp valign=top  width=53><div align=left><span class="campo"><?php echo $dadosboleto["especie"]?></span>  </div></td>
    <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
    <td class=cp valign=top  width=123><span class="campo"> <?php echo $dadosboleto["quantidade"]?> </span>  </td>
 <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top  width=72>
   <span class="campo">
   <?php echo $dadosboleto["valor_unitario"]?>
   </span></td>
 <td class=cp valign=top width=7 height=12> <img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12>
   <span class="campo">
   <?php echo $dadosboleto["valor_boleto"]?>
   </span></td>
</tr><tr><td valign=top width=7 height=1> <img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=75 border=0></td><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=31 height=1><img height=1 src=imagens/2.png width=31 border=0></td><td valign=top width=7 height=1>
<img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=83 height=1><img height=1 src=imagens/2.png width=83 border=0></td><td valign=top width=7 height=1>
<img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=53 height=1><img height=1 src=imagens/2.png width=53 border=0></td><td valign=top width=7 height=1>
<img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=123 height=1><img height=1 src=imagens/2.png width=123 border=0></td><td valign=top width=7 height=1>
<img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=72 height=1><img height=1 src=imagens/2.png width=72 border=0></td><td valign=top width=7 height=1>
<img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody>
</table><table cellspacing=0 cellpadding=0 width=666 border=0><tbody><tr><td align=right width=10><table cellspacing=0 cellpadding=0 border=0 align=left><tbody>
<tr> <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td></tr><tr>
<td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td></tr><tr>


<td valign=top width=7 height=1><img height=1 src=imagens/2.png width=1 border=0></td></tr></tbody></table></td><td valign=top width=468 rowspan=5><font class=ct>Instruções
(Texto de responsabilidade do cedente)</font><br><br><span class=cp> <FONT class=campo>
<?php echo $dadosboleto["instrucoes1"]; ?><br>
<?php echo $dadosboleto["instrucoes2"]; ?><br>
<?php echo $dadosboleto["instrucoes3"]; ?><br>
<?php echo $dadosboleto["instrucoes4"]; ?></FONT><br><br>
</span></td>
<td align=right width=188><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>(-)
Desconto / Abatimentos</td></tr><tr> <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12></td></tr><tr>
<td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody></table></td></tr><tr><td align=right width=10>
<table cellspacing=0 cellpadding=0 border=0 align=left><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td></tr><tr><td valign=top width=7 height=1>
<img height=1 src=imagens/2.png width=1 border=0></td></tr></tbody></table></td><td align=right width=188><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>(-)
Outras deduções</td></tr><tr><td class=cp valign=top width=7 height=12> <img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12></td></tr><tr><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody></table></td></tr><tr><td align=right width=10>
<table cellspacing=0 cellpadding=0 border=0 align=left><tbody><tr><td class=ct valign=top width=7 height=13>
<img height=13 src=imagens/1.png width=1 border=0></td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td></tr><tr><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=1 border=0></td></tr></tbody></table></td><td align=right width=188>
<table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>(+)
Mora / Multa</td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12></td></tr><tr>
<td valign=top width=7 height=1> <img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1>
<img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody></table></td></tr><tr><td align=right width=10><table cellspacing=0 cellpadding=0 border=0 align=left><tbody><tr>
<td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td></tr><tr><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=1 border=0></td></tr></tbody></table></td><td align=right width=188>
<table cellspacing=0 cellpadding=0 border=0><tbody><tr> <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>(+)
Outros acréscimos</td></tr><tr> <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12></td></tr><tr><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody></table></td></tr><tr><td align=right width=10><table cellspacing=0 cellpadding=0 border=0 align=left><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td></tr></tbody></table></td><td align=right width=188><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>(=)
Valor cobrado</td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top align=right width=180 height=12></td></tr></tbody>
</table></td></tr></tbody></table><table cellspacing=0 cellpadding=0 width=666 border=0><tbody><tr><td valign=top width=666 height=1><img height=1 src=imagens/2.png width=666 border=0></td></tr></tbody></table><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=659 height=13>Sacado</td></tr><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top width=659 height=12><span class="campo">
<?php echo $dadosboleto["sacado"]?>
</span>
</td>
</tr></tbody></table><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td><td class=cp valign=top width=659 height=12><span class="campo">
<?php echo $dadosboleto["endereco1"]?>
</span>
</td>
</tr></tbody></table><table cellspacing=0 cellpadding=0 border=0><tbody><tr><td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=cp valign=top width=472 height=13>
  <span class="campo">
  <?php echo $dadosboleto["endereco2"]?>
  </span></td>
<td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td><td class=ct valign=top width=180 height=13>Cód.
baixa</td></tr><tr><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=472 height=1><img height=1 src=imagens/2.png width=472 border=0></td><td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td><td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td></tr></tbody></table><TABLE cellSpacing=0 cellPadding=0 border=0 width=666><TBODY><TR><TD class=ct  width=7 height=12></TD><TD class=ct  width=409 >Sacador/Avalista</TD><TD class=ct  width=250 ><div align=right>Autenticação
mecânica - <b class=cp>Ficha de Compensação</b></div></TD></TR><TR><TD class=ct  colspan=3 ></TD></tr></tbody></table><TABLE cellSpacing=0 cellPadding=0 width=666 border=0><TBODY><TR><TD vAlign=bottom align=left height=50>
    <?php if (!isset($_GLOBAL['pdf'])) : ?>
        <?php fbarcode($dadosboleto["codigo_barras"]); ?>
    <?php else : ?>
        <barcode code="<?=$dadosboleto["codigo_barras"]?>" type="I25" height="1.5" style="padding: 0;margin: 10px -10px;" />
    <?php endif ?>
 </TD>
</tr></tbody></table><TABLE cellSpacing=0 cellPadding=0 width=666 border=0><TR><TD class=ct width=666></TD></TR><TBODY><TR><TD class=ct width=666><div align=right>Corte
na linha pontilhada</div></TD></TR><TR><TD class=ct width=666><img height=1 src=imagens/6.png width=665 border=0></TD></tr></tbody></table>
</BODY></HTML>
