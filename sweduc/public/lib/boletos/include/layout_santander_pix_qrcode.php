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
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa				  |
// | 																	  |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+
// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Itaú: Glauber Portella		                    |
// +----------------------------------------------------------------------+
//      body{
//          margin: 70px 0px 50px 280px;
//          -webkit-transform: scale(1.55, 1.3);
//         -moz-transform: scale(1.55, 1.3);
//          -ms-transform: scale(1.55, 1.3);  /* IE9 */
//          -o-transform: scale(1.55, 1.3);  /* Opera 10.5+ */
//            transform: scale(1.55, 1.3);  /* IE8+ - must be on one line, unfortunately */
//          -ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.5, M12=0, M21=0, M22=0.5, SizingMethod='auto expand')";

          /* IE6 and 7 */
//          filter: progid:DXImageTransform.Microsoft.Matrix(
//              M11=0.5,
//              M12=0,
//              M21=0,
//              M22=0.5,
//          SizingMethod='auto expand');
//      }

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<HTML>
  <HEAD>
    <TITLE>
      <?php echo $dadosboleto["identificacao"]; ?>
    </TITLE>
    <META http-equiv=Content-Type content=text/html charset=utf-8>
    <meta name="Generator" content="Projeto BoletoPHP - www.boletophp.com.br - Licença GPL" />
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

table {
    line-height:0px;
}

br {
    line-height:6px;
}

</style>
  </head>
  <BODY text=#000000 bgColor=#ffffff>
    <table cellspacing=0 cellpadding=3 style="width:500px" border=0>
      <tr><td style="border-right:1px dashed #000;vertical-align:top;">

        <table cellspacing=0 cellpadding=0 border=0 style="width:150px">
        </tbody>
      </table>
</td><td>&nbsp;</td><td>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <tr>
        <td class=cp width=150>
          <span class="campo">
            <IMG        src="imagens/logosantandernovo.png" width="150" height="37" border=0>
          </span></td>
      <tbody>
        <tr>
          <td colspan=5>
            <img height=2 src=imagens/2.png width=666 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=472 height=13>N<u>o</u>  documento</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=180 height=13>Vencimento</td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=472 height=12>
          <span class="campo">
              <?php echo $dadosboleto["numero_documento"]?>
            </span>
          </td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              <?php echo $dadosboleto["data_vencimento"]?>
            </span></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=472 height=1>
            <img height=1 src=imagens/2.png width=472 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1>
            <img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=472 height=1>
            <img height=1 src=imagens/2.png width=472 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1>
            <img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=113 height=1>
            <img height=1 src=imagens/2.png width=113 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=153 height=1>
            <img height=1 src=imagens/2.png width=153 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=62 height=1>
            <img height=1 src=imagens/2.png width=62 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=34 height=1>
            <img height=1 src=imagens/2.png width=34 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=82 height=1>
            <img height=1 src=imagens/2.png width=82 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1>
            <img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <tbody>
        <tr>
          <td align=right width=10>
            <table cellspacing=0 cellpadding=0 border=0 align=left>
              <tbody>
                <td align=right width=10>
                    <?php if (!empty($dadosboleto["qrcode"])) : ?>
                        <IMG src="imagens/logopix.png" width="80" height="30" border=0 style="position: absolute;top:200px; right: 500px;">
                    <?php endif ?>
                </td>
                <td  style="margin-top: -30%;">
                <?php if (!empty($dadosboleto["qrcode"])) : ?>
                <?php fqrcode($dadosboleto["qrcode"]); ?>
                <?php endif ?>
              </tbody>
            </table>
        </td>
        </tr>
      </tbody>
    </table>
  </BODY>
</HTML>
