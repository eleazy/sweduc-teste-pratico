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
    <META http-equiv=Content-Type content=text/html charset="utf-8">
    <meta name="Generator" content="Projeto BoletoPHP - www.boletophp.com.br - Licença GPL" />
    <style type=text/css>
      .cp {  font: bold 10px Arial; color: black}
      .ct { font: 8px "Arial Narrow"; color: #000033}
      .cp1 {  font: bold 8px Arial; color: black; float:left;}
      .ti { font: 9px Arial, Helvetica, sans-serif}
      .ld { font: bold 14px Arial; color: #000000}
      .ct1 { font: bold 11px "Arial Narrow"; color: #000033; float:right; margin-right:8px;margin-top:11px;}
      .cn { font: 9px Arial; color: black }
      .bc { font: bold 14px Arial; color: #000000 }
      .ld2 { font: bold 12px Arial; color: #000000 }
</style>
  </head>
  <BODY text=#000000 bgColor=#ffffff>
    <table cellspacing=0 cellpadding=3 style="width:500px">
      <tr><td style="border-right:1px dashed #000;vertical-align:top;">
        <table cellspacing=0 cellpadding=0 style="width:150px">
          <tbody>
            <tr><td valign=top style="border-bottom:1px solid #000;">
              <IMG src="./lib/boletos/imagens/logoitau.jpg" width="150" height="40" style="float:left;">
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>Vencimento</span>
              <span class=ct1><?php echo $dadosboleto["data_vencimento"]; ?></span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>Agência / Código Beneficiário</span>
              <span class=ct1><?php echo $dadosboleto["agencia_codigo"] ; ?></span>
            </td></tr>

            <tr><td valign=top style="border-bottom:1px solid #000;height:15px;">
              <span class=cp1>Nosso Número</span>
              <span class=ct1><?php $tmp = $dadosboleto["nosso_numero"]; $tmp = substr($tmp,0,strlen($tmp)-1).''.substr($tmp,strlen($tmp)-1,1); print $tmp; ?></span>
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

            <tr><td valign=top style="border-bottom:0px solid #000;height:15px;">
              <span class=cp1>Pagador</span>
              <span class=cp1 style="height:15px;width:150px;"><?php echo "N. Documento: ".$dadosboleto["numero_documento"] . "<br />" . $dadosboleto["sacado"]."<br />".$dadosboleto["cpf"]; ?></span>
            </td></tr>
        </tbody>
      </table>
</td><td>&nbsp;</td><td>
    <table cellspacing=0 cellpadding=0 width=666>
      <tr>
        <td class=cp width=150>
          <span class="campo">
            <IMG       src="./lib/boletos/imagens/logoitau.jpg" width="150" height="40"      >
          </span></td>
        <td width=3 valign=bottom>
          <img height=22 src=./lib/boletos/imagens/3.png width=2></td>
        <td class=cpt width=58 valign=bottom style="text-align: center;">
          <div align=center>
            <font class=bc>
              <?php echo $dadosboleto["codigo_banco_com_dv"]?>
            </font>
          </div></td>
        <td width=3 valign=bottom>
          <img height=22 src=./lib/boletos/imagens/3.png width=2></td>
        <td class=ld align=right width=453 valign=bottom>
          <span class=ld>
            <span class="campotitulo">
              <?php echo $dadosboleto["linha_digitavel"]?>
            </span>
          </span></td>
      </tr>
      <tbody>
        <tr>
          <td colspan=5>
            <img height=2 src=./lib/boletos/imagens/2.png width=666></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=472 height=13>Local de pagamento</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=180 height=13>Vencimento</td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top width=472 height=12>Até o vencimento, preferencialmente no Itaú. Após o vencimento, somente no Itaú </td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              <?php echo $dadosboleto["data_vencimento"]?>
            </span></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=472 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=472></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=180 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=472 height=13>Beneficiário</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=180 height=13>Agência/Código Beneficiário</td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top width=472 height=12>
            <span class="campo">
              <?php echo $dadosboleto["cedente"] . '  ' . $dadosboleto["cpf_cnpj"] . ' ' . $dadosboleto["endereco"]  . ' ' . $cidade_empresa . '-' . $uf_empresa ?>
            </span></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              <?php echo $dadosboleto["agencia_codigo"]?>
            </span></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=472 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=472></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=180 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=113 height=13>Data do documento</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=153 height=13>N<u>o</u>documento</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=62 height=13>Espécie doc.</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=34 height=13>Aceite</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=82 height=13>Data processamento</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=180 height=13>Nosso número</td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=113 height=12>
            <div align=left>
              <span class="campo">
                <?php echo $dadosboleto["data_documento"]?>
              </span>
            </div></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top width=153 height=12>
            <span class="campo">
              <?php echo $dadosboleto["numero_documento"]?>
            </span></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=62 height=12>
            <div align=left>
              <span class="campo">
                <?php echo $dadosboleto["especie_doc"]?>
              </span>
            </div></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=34 height=12>
            <div align=left>
              <span class="campo">
                <?php echo $dadosboleto["aceite"]?>
              </span>
            </div></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=82 height=12>
            <div align=left>
              <span class="campo">
                <?php echo $dadosboleto["data_processamento"]?>
              </span>
            </div></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              <?php echo $dadosboleto["nosso_numero"]?>
            </span></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=113 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=113></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=153 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=153></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=62 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=62></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=34 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=34></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=82 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=82></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=180 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top COLSPAN="3" height=13>Uso do banco</td>
          <td class=ct valign=top height=13 width=7>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=83 height=13>Carteira</td>
          <td class=ct valign=top height=13 width=7>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=53 height=13>Espécie</td>
          <td class=ct valign=top height=13 width=7>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=123 height=13>Quantidade</td>
          <td class=ct valign=top height=13 width=7>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=72 height=13>Valor Documento</td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=180 height=13>(=) Valor documento</td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td valign=top class=cp height=12 COLSPAN="3">
            <div align=left>
            </div></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=83>
            <div align=left>
              <span class="campo">
                <?php echo $dadosboleto["carteira"]?>
              </span>
            </div></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=53>
            <div align=left>
              <span class="campo">
                <?php echo $dadosboleto["especie"]?>
              </span>
            </div></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=123>
            <span class="campo">
              <?php echo $dadosboleto["quantidade"]?>
            </span> </td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top  width=72>
            <span class="campo">
              <?php echo $dadosboleto["valor_unitario"]?>
            </span></td>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              <?php echo $dadosboleto["valor_boleto"]?>
            </span></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=75></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=31 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=31></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=83 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=83></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=53 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=53></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=123 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=123></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=72 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=72></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=180 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 width=666>
      <tbody>
        <tr>
          <td width=10>
            <table cellspacing=0 cellpadding=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=1></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td valign=top width=469 rowspan=5>
            <font class=ct>Instruções (Instruções de responsabilidade do beneficiário. Qualquer dúvida sobre este boleto, contate o beneficiário)</font>
            <br>
            <br>
            <span class=cp>
              <FONT class=campo>
                <?php echo $dadosboleto["instrucoes1"]; ?>
                <br>
                <?php echo $dadosboleto["instrucoes2"]; ?>
                <br>
                <?php echo $dadosboleto["instrucoes3"]; ?>
                <br>
                <?php echo $dadosboleto["instrucoes4"]; ?>
              </FONT>
              <br>
              <br>
            </span></td>
          <td width=188>
            <table cellspacing=0 cellpadding=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=ct valign=top width=180 height=13>(-) Desconto / Abatimentos</td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
                  <td valign=top width=180 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
                </tr>
              </tbody>
            </table></td>
        </tr>
        <tr>
          <td width=10>
            <table cellspacing=0 cellpadding=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=1></td>
                </tr>
              </tbody>
            </table></td>
          <td width=188>
            <table cellspacing=0 cellpadding=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=ct valign=top width=180 height=13>(-) Outras deduções</td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
                  <td valign=top width=180 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
                </tr>
              </tbody>
            </table></td>
        </tr>
        <tr>
          <td width=10>
            <table cellspacing=0 cellpadding=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=1></td>
                </tr>
              </tbody>
            </table></td>
          <td width=188>
            <table cellspacing=0 cellpadding=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=ct valign=top width=180 height=13>(+) Mora / Multa</td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
                  <td valign=top width=180 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
                </tr>
              </tbody>
            </table></td>
        </tr>
        <tr>
          <td width=10>
            <table cellspacing=0 cellpadding=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=1></td>
                </tr>
              </tbody>
            </table></td>
          <td width=188>
            <table cellspacing=0 cellpadding=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=ct valign=top width=180 height=13>(+) Outros acréscimos</td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
                  <td valign=top width=180 height=1>
                    <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
                </tr>
              </tbody>
            </table></td>
        </tr>
        <tr>
          <td width=10>
            <table cellspacing=0 cellpadding=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                </tr>
              </tbody>
            </table></td>
          <td width=188>
            <table cellspacing=0 cellpadding=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=ct valign=top width=180 height=13>(=) Valor cobrado</td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12>
                    <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                </tr>
              </tbody>
            </table></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 width=666>
      <tbody>
        <tr>
          <td valign=top width=666 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=666></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=659 height=13>Pagador</td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top width=659 height=12>
            <span class="campo" style="float:left;"><?php echo substr($dadosboleto["sacado"],0,30). ' (CPF: '.$dadosboleto["cpf"].')';?></span>
            <span class="campo" style="float:right; margin-right:190px;"><?php echo "Aluno: ".substr($dadosboleto["nomealuno"],0, 32)?></span>
          </td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0>
      <tbody>
        <tr>
          <td class=cp valign=top width=7 height=12>
            <img height=12 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top width=659 height=12>
            <span class="campo" style="float:left;"><?php echo $dadosboleto["endereco1"]?></span>
            <span class="campo" style="float:right; margin-right:190px;"><?php echo substr($dadosboleto["cursoturma"],0, 30)?></span>
          </td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=cp valign=top width=472 height=13>
            <span class="campo">
              <?php echo $dadosboleto["endereco2"]?>
            </span></td>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=./lib/boletos/imagens/1.png width=1></td>
          <td class=ct valign=top width=180 height=13>Cód. baixa</td>
        </tr>
        <tr>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=472 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=472></td>
          <td valign=top width=7 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=7></td>
          <td valign=top width=180 height=1>
            <img height=1 src=./lib/boletos/imagens/2.png width=180></td>
        </tr>
      </tbody>
    </table>
    <TABLE cellSpacing=0 cellPadding=0 width=666>
      <TBODY>
        <TR>
          <TD class=ct  width=7 height=12></TD>
          <TD class=ct  width=409 >Pagador/Avalista</TD>
          <TD class=ct  width=250 >
            <div align=right>Autenticação mecânica -
              <b class=cp>Ficha de Compensação</b>
            </div></TD>
        </TR>
        <TR>
          <TD class=ct  colspan=3 ></TD>
        </tr>
      </tbody>
    </table>
    <TABLE cellSpacing=0 cellPadding=0 width=666>
      <tbody>
        <tr>
          <td height=50 style="text-align: left;">
            <barcode code="<?=$dadosboleto["codigo_barras"]?>" type="I25" height="1.5" style="padding: 0;margin: 10px -10px;" />
          </td>
        </tr>
      </tbody>
    </table>
          <TABLE cellSpacing=0 cellPadding=0 width=666>
            <TR>
              <TD class=ct width=666></TD>
            </TR>
            <TBODY>
              <TR>
                <TD class=ct width=666>
                  <div align=right>Corte  na linha pontilhada
                  </div></TD>
              </TR>
              <TR>
                <TD class=ct width=666>
                  <img height=1 src=./lib/boletos/imagens/6.png width=665></TD>
              </tr>
            </tbody>
          </table>


</td></tr></table>

  </BODY>
</HTML>
