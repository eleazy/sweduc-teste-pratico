<?php

use Endroid\QrCode\QrCode;
use Picqer\Barcode\BarcodeGeneratorSVG;
use App\Service\Financeiro\DescontoComercialService;

//header("Content-Type: text/html; charset=UTF-8", true); //ISO-8859-1 UTF-8
require __DIR__ . '/../../dao/conectar.php';
$agora = date("Y-m-d");

$debug = 0;

$keys = array_keys($_REQUEST);
foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}

/**
 * Esse script é chamado em loop pelo envio de e-mails
 * ao adicionar novas funções, evite que elas gerem exceções
 * fazendo wrapping da função dentro de um function_exists
 */

if (!function_exists('completarZero')) {
    function completarZero($lado, $txt, $qunat)
    {
        $t = strlen($txt);
        if ($t < $qunat) {
            if ($lado == 0) {
                for ($i = $t; $i < $qunat; $i++) {
                    $txt = 0 . $txt;
                }
            } else {
                for ($i = $t; $i < $qunat; $i++) {
                    $txt = $txt . 0;
                }
            }
        } else {
            $txt = substr($txt, 0, $qunat);
        }
        return $txt;
    }
}


if (!function_exists('descontoMatricula')) {
    function descontoMatricula($idfichafin)
    {
        $q = "SELECT bolsa FROM alunos_fichafinanceira WHERE id=" . $idfichafin . " LIMIT 1";
        $res = mysql_query($q);
        $row = mysql_fetch_array($res, MYSQL_ASSOC);

        return $row['bolsa'];
    }
}


if (!function_exists('verificaValores')) {
    function verificaValores($idfichafin, $valoritem, $num)
    {
        $q = "SELECT bolsa, valor FROM alunos_fichafinanceira WHERE id=" . $idfichafin . " LIMIT 1";
        $res = mysql_query($q);
        $row = mysql_fetch_array($res, MYSQL_ASSOC);

        $vtotalffin = valorTotalFichaFin($idfichafin);

        if ($row['bolsa'] > 0 && $num == 1) {
            if (($row['valor'] == $valoritem || $row['valor'] == $vtotalffin) && ($valoritem > $row['bolsa'])) {
                return $valoritem - $row['bolsa'];
            }
        }
        return false;
    }
}

if (!function_exists('valorTotalFichaFin')) {
    function valorTotalFichaFin($idfichafin)
    {
        $q = "SELECT SUM(valor) as vtotal FROM alunos_fichaitens WHERE idalunos_fichafinanceira=" . $idfichafin . " LIMIT 1";
        $res = mysql_query($q);
        $row = mysql_fetch_array($res, MYSQL_ASSOC);

        return $row['vtotal'];
    }
}

if (!function_exists('dataJuliano')) {
    function dataJuliano($data)
    {
        $dia = (int) substr($data, 1, 2);
        $mes = (int) substr($data, 3, 2);
        $ano = (int) substr($data, 6, 4);
        $dataf = strtotime("$ano/$mes/$dia");
        $datai = strtotime(($ano - 1) . '/12/31');
        $dias  = (int)(($dataf - $datai) / (60 * 60 * 24));
        return str_pad($dias, 3, '0', STR_PAD_LEFT) . substr($data, 9, 4);
    }
}

if (!function_exists('fqrcode')) {
    function fqrcode($valor)
    {
        $qrCode = new QrCode($valor);
        return base64_encode($qrCode->writeString());
    }
}

if (!function_exists('fbarcode')) {
    function fbarcode($valor)
    {
        $geradorBarcode = new BarcodeGeneratorSVG();
        $barcode = $geradorBarcode->getBarcode(
            $valor,
            BarcodeGeneratorSVG::TYPE_INTERLEAVED_2_5,
            2,
            50
        );

        echo '<img src="data:image/svg+xml;base64, ' . base64_encode($barcode) . '" alt="">';
    }
}

if (!function_exists('digitoVerificador_nossonumero')) {
    function digitoVerificador_nossonumero($numero)
    {
        $resto2 = modulo_11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito == 10 || $digito == 11) {
            $dv = 0;
        } else {
            $dv = $digito;
        }
        return $dv;
    }
}

if (!function_exists('digitoVerificador_barra')) {
    function digitoVerificador_barra($numero)
    {
        $resto2 = modulo_11($numero, 9, 1);
        if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
            $dv = 1;
        } else {
            $dv = 11 - $resto2;
        }
        return $dv;
    }
}

if (!function_exists('formata_numero')) {
    function formata_numero($numero, $loop, $insert, $tipo = "geral")
    {
        if ($tipo == "geral") {
            $numero = str_replace(",", "", $numero);
            while (strlen($numero) < $loop) {
                $numero = $insert . $numero;
            }
        }
        if ($tipo == "valor") {
            /*
            retira as virgulas
            formata o numero
            preenche com zeros
            */
            $numero = str_replace(",", "", $numero);
            while (strlen($numero) < $loop) {
                $numero = $insert . $numero;
            }
        }
        if ($tipo == "convenio") {
            while (strlen($numero) < $loop) {
                $numero = $numero . $insert;
            }
        }
        return $numero;
    }
}

if (!function_exists('esquerda')) {
    function esquerda($entra, $comp)
    {
        return substr($entra, 0, $comp);
    }
}

if (!function_exists('direita')) {
    function direita($entra, $comp)
    {
        return substr($entra, strlen($entra) - $comp, $comp);
    }
}

if (!function_exists('_dateToDays')) {
    function _dateToDays($year, $month, $day)
    {
        $century = substr($year, 0, 2);
        $year = substr($year, 2, 2);
        if ($month > 2) {
            $month -= 3;
        } else {
            $month += 9;
            if ($year) {
                $year--;
            } else {
                $year = 99;
                $century--;
            }
        }

        return (
            floor((146097 * $century) / 4) +
            floor((1461 * $year) / 4) +
            floor((153 * $month + 2) / 5) +
            $day +  1_721_119
        );
    }
}

if (!function_exists('modulo_10')) {
    function modulo_10($num)
    {
        $numeros = [];
        $parcial10 = [];
        $numtotal10 = 0;
        $fator = 2;

        // Separacao dos numeros
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = (int) substr($num, $i - 1, 1);
            // Efetua multiplicacao do numero pelo (falor 10)
            // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do Ita�
            $temp = $numeros[$i] * $fator;
            $temp0 = 0;
            foreach (preg_split('//', $temp, -1, PREG_SPLIT_NO_EMPTY) as $k => $v) {
                $temp0 += $v;
            }
            $parcial10[$i] = $temp0; //$numeros[$i] * $fator;
            // monta sequencia para soma dos digitos no (modulo 10)
            $numtotal10 += $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2; // intercala fator de multiplicacao (modulo 10)
            }
        }

        // v�rias linhas removidas, vide fun��o original
        // Calculo do modulo 10
        $resto = $numtotal10 % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }

        return $digito;
    }
}

if (!function_exists('modulo_11')) {
    function modulo_11($num, $base = 9, $r = 0)
    {
        $numeros = [];
        $parcial = [];
        /**
         *   Autor:
         *           Pablo Costa <pablo@users.sourceforge.net>
         *
         *   Fun��o:
         *    Calculo do Modulo 11 para geracao do digito verificador
         *    de boletos bancarios conforme documentos obtidos
         *    da Febraban - www.febraban.org.br
         *
         *   Entrada:
         *     $num: string num�rica para a qual se deseja calcularo digito verificador;
         *     $base: valor maximo de multiplicacao [2-$base]
         *     $r: quando especificado um devolve somente o resto
         *
         *   Sa�da:
         *     Retorna o Digito verificador.
         *
         *   Observa��es:
         *     - Script desenvolvido sem nenhum reaproveitamento de c�digo pr� existente.
         *     - Assume-se que a verifica��o do formato das vari�veis de entrada � feita antes da execu��o deste script.
         */

        $soma = 0;
        $fator = 2;

        /* Separacao dos numeros */
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = (int) substr($num, $i - 1, 1);
            // Efetua multiplicacao do numero pelo falor
            $parcial[$i] = $numeros[$i] * $fator;
            // Soma dos digitos
            $soma += $parcial[$i];
            if ($fator == $base) {
                // restaura fator de multiplicacao para 2
                $fator = 1;
            }
            $fator++;
        }

        /* Calculo do modulo 11 */
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;
            if ($digito == 10) {
                $digito = 0;
            }
            return $digito;
        } elseif ($r == 1) {
            $resto = $soma % 11;
            return $resto;
        }
    }
}

if (!function_exists('modulo_11_invertido')) {
    function modulo_11_invertido($num)  // Calculo de Modulo 11 "Invertido" (com pesos de 9 a 2  e n�o de 2 a 9)
    {
        $ftini = 2;
        $fator = $ftfim = 9;
        $soma = 0;

        for ($i = strlen($num); $i > 0; $i--) {
            $soma += substr($num, $i - 1, 1) * $fator;
            if (--$fator < $ftini) {
                $fator = $ftfim;
            }
        }

        $digito = $soma % 11;

        if ($digito > 9) {
            $digito = 0;
        }

        return $digito;
    }
}

if (!function_exists('fator_vencimento')) {
    function fator_vencimento($data)
    {
        if ($data != "") {
            $data = explode("/", $data);
            $ano = (int) $data[2];
            $mes = (int) $data[1];
            $dia = (int) $data[0];

            $dias_data = _dateToDays($ano, $mes, $dia);

            $dias_inicio = _dateToDays(1997, 10, 7);
            $dias_limite = _dateToDays(2025, 2, 21); // data limite
            $dias_reset = _dateToDays(2025, 2, 22);  // data do reset (22/02/2025)

            if ($dias_data <= $dias_limite) {
                return abs($dias_data - $dias_inicio);
            } else {
                // volta fator de Vencimento para 1000 a partir dos vencimentos 22/02/2025
                return 1000 + ($dias_data - $dias_reset);
            }
        } else {
            return "0000";
        }
    }
}

if (!function_exists('digitoVerificador_cedente')) {
    function digitoVerificador_cedente($numero)
    {
        $resto2 = modulo_11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito == 10 || $digito == 11) {
            $digito = 0;
        }
        $dv = $digito;
        return $dv;
    }
}

if (!function_exists('geraCodigoBanco')) {
    function geraCodigoBanco($numero)
    {
        $parte1 = substr($numero, 0, 3);
        $parte2 = modulo_11($parte1);
        return $parte1 . "-" . $parte2;
    }
}

if (!function_exists('geraNossoNumero')) {
    function geraNossoNumero($ndoc, $cedente, $venc, $tipoid)
    {
        $ndoc = $ndoc . modulo_11_invertido($ndoc) . $tipoid;
        $venc = substr($venc, 0, 2) . substr($venc, 3, 2) . substr($venc, 8, 2);
        $res = $ndoc + $cedente + $venc;
        return $ndoc . modulo_11_invertido($res);
    }
}

if (!function_exists('picture9')) {
    function picture9($palavra, $limite)
    {
        $var = str_pad($palavra, $limite, "0", STR_PAD_LEFT);
        return $var;
    }
}

$query = "SELECT
                unidades.cidade,
                empresas.*,
                unidades.endereco AS uendereco,
                unidades.numero AS unumero,
                unidades.complemento AS ucomplemento,
                unidades.bairro AS ubairro,
                unidades.cep AS ucep,
                contasbanco.*,
                alunos_matriculas.idunidade,
                tarifaboleto,
                alunos_fichafinanceira.titulo,
                alunos_fichafinanceira.identificacao_banco,
                valor,
                mensagemboleto, --
                mensagemboletopadrao, --
                IF(alunos.desconto_comercial > 0 OR desconto_titulo.id IS NOT NULL, 1, 0) as desconto_comercial, --
                IFNULL(desconto_titulo.id, alunos.desconto_comercial_msg) as desconto_comercial_msg, --
                IF(desconto_titulo.id IS NOT NULL, desconto_titulo.msg_boleto, financeiro_descontocomercial.msg_boleto) as msg_boleto,
                IF(desconto_titulo.id IS NOT NULL, desconto_titulo.msg_boletopadrao, financeiro_descontocomercial.msg_boletopadrao) as msg_boletopadrao,
                alunos_fichafinanceira.matricula,
                alunos_matriculas.idaluno,
                idtitulopai,
                alunos_matriculas.nummatricula,
                DATEDIFF(datavencimento, CURDATE()) AS atrasado,
                empresas.multa AS emulta,
                empresas.mora AS emora,
                alunos_fichafinanceira.id AS afid,
                alunos_fichafinanceira.multa AS afmulta,
                alunos_fichafinanceira.juros AS afjuros,
                alunos_fichafinanceira.desconto AS afdesconto,
                alunos_fichafinanceira.bolsa AS afbolsa,
                contasbanco.bancoarquivo,
                DATE_FORMAT(datavencimento, '%d/%m/%Y') AS 'dtvenc',
                cidades.nom_cidade cidade_empresa,
                (SELECT
                        sgl_estado
                    FROM
                        estados
                    WHERE
                        id = cidades.cod_estado) uf_empresa
            FROM
                alunos_fichafinanceira
            LEFT JOIN
                financeiro_descontocomercial desconto_titulo ON desconto_titulo.id = alunos_fichafinanceira.id_desconto_comercial
            INNER JOIN
                contasbanco ON alunos_fichafinanceira.idcontasbanco = contasbanco.id
            INNER JOIN
                empresas ON contasbanco.idempresa = empresas.id
            INNER JOIN
                unidades_empresas ON unidades_empresas.idempresa = empresas.id
            INNER JOIN
                unidades ON unidades_empresas.idunidade = unidades.id
            INNER JOIN
                alunos_matriculas ON alunos_fichafinanceira.nummatricula = alunos_matriculas.nummatricula and alunos_fichafinanceira.idaluno = alunos_matriculas.idaluno
            INNER JOIN
                alunos ON alunos_fichafinanceira.idaluno = alunos.id
            LEFT JOIN
                financeiro_descontocomercial ON alunos.desconto_comercial_msg = financeiro_descontocomercial.id
            INNER JOIN
                cidades ON empresas.idcidade = cidades.id
            WHERE
                alunos_fichafinanceira.id=" . $idl;

$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

$uf_empresa = $row['uf_empresa'];
$cidade_empresa = $row['cidade_empresa'];
$idunidade = $row['idunidade'];
$idaluno = $row['idaluno'];
$nummatricula = $row['nummatricula'];
$bolsa = $row['afbolsa'];
$atrasado = $row['atrasado'];

$valor = $row['valor'] - $bolsa - $row['afdesconto'] + $row['afmulta'] + $row['afmora'];
$descontos = $bolsa + $row['afdesconto'];
$desconto = $row['afdesconto'];

$taxa_boleto = $row['tarifaboleto'];
$nosso_numero = $row['titulo'];
$numero_documento = $row['titulo'];
$data_venc = $row['dtvenc'];
$multa = $row['emulta'];
$mora = $row['emora'];
$numero_operacao = $row['numero_operacao'];
$identificacaobanco = $row['identificacao_banco'];

$matricula = $row['matricula'];

$idtitulopai = $row['idtitulopai'] ?: 0;
$queryEventos = "SELECT *
    FROM alunos_fichaitens
    WHERE
        (
            idalunos_fichafinanceira='{$row['afid']}'
            OR idalunos_fichafinanceira='$idtitulopai'
        )";

$resultEventos = mysql_query($queryEventos);
$itensQtd = mysql_numrows($resultEventos);
$itensBoleto = [];

$itenscomdesconto = 0.00;
$itenssemdesconto = 0.00;
$num = 0;
while ($rowEventos = mysql_fetch_array($resultEventos, MYSQL_ASSOC)) {
    $verifValEvento = verificaValores($rowEventos['idalunos_fichafinanceira'], $rowEventos['valor'], $rowEventos['descontoboleto']);
    $valEvento = (!$verifValEvento) ? $rowEventos['valor'] : $verifValEvento;

    $itensBoleto[] = [
        'evento' => $rowEventos['eventofinanceiro'],
        'parcela' => $rowEventos['parcela'],
        'totalParcelas' => $rowEventos['totalparcelas'],
        'valor' => $valEvento
    ];

    $valorconsiderado = $valEvento;

    if ($rowEventos['descontoboleto'] == 1) {
        $itenscomdesconto += $valorconsiderado;
    }

    if ($rowEventos['descontoboleto'] == 0) {
        $itenssemdesconto += $rowEventos['valor'];
    }
}

if (count($itensBoleto) < 4) {
    $eventoBoleto = implode("", array_map(function ($item) {
        $valor = money_format('%.2n', $item['valor']);
        return "{$item['evento']} ({$item['parcela']} / {$item['totalParcelas']}) : $valor<br>";
    }, $itensBoleto));
} else {
    $eventoBoleto = count($itensBoleto) . " itens com total de R$ " . money_format('%.2n', array_sum(array_column($itensBoleto, 'valor')));
}

//******************************************************************************************//

$novovalorboleto = $valor;

// $valor = $itenscomdesconto;
// print '*** '.$novovalorboleto." *** ".$valor.' ***<br>';


if ($matricula == "1") {
    $demonstrativo1 = ($row['desconto_comercial'] == 1 && $itenscomdesconto > 0) ? $row['msg_boleto'] : $row['mensagemboleto'];
} else {
    $demonstrativo1 = ($row['desconto_comercial'] == 1 && $itenscomdesconto > 0) ? $row['msg_boletopadrao'] : $row['mensagemboletopadrao'];
}
$demonstrativoLinhas = explode("\n", $demonstrativo1);

$demonstrativoPartes1 = explode("@", $demonstrativoLinhas[0]);
$demonstrativoPartes2 = explode("@", $demonstrativoLinhas[1]);
$demonstrativoPartes3 = explode("@", $demonstrativoLinhas[2]);
$demonstrativoPartes4 = explode("@", $demonstrativoLinhas[3]);
$demonstrativoPartes5 = explode("@", $demonstrativoLinhas[4]);

if (isset($demonstrativoPartes1[1]) && (strpos($demonstrativoPartes1['0'], 'multa') || strpos($demonstrativoPartes1['0'], 'juros'))) {
    $valor = $novovalorboleto;
    $addsemdesconto1 = 0;
    eval("\$demConta1=$demonstrativoPartes1[1];");
} elseif (isset($demonstrativoPartes1[1])) {
    $valor = $itenscomdesconto;
    $addsemdesconto1 = $itenssemdesconto;
    $valorSemDesconto = $itenssemdesconto;
    $valorTotal = $valorSemDesconto + $valor;
    eval("\$demConta1=$demonstrativoPartes1[1];");
}

if (isset($demonstrativoPartes2[1]) && (strpos($demonstrativoPartes2['0'], 'multa') || strpos($demonstrativoPartes2['0'], 'juros'))) {
    $valor = $novovalorboleto;
    $addsemdesconto2 = 0;
    eval("\$demConta2=$demonstrativoPartes2[1];");
} elseif (isset($demonstrativoPartes2[1])) {
    $valor = $itenscomdesconto;
    $addsemdesconto2 = $itenssemdesconto;
    $valorSemDesconto = $itenssemdesconto;
    $valorTotal = $valorSemDesconto + $valor;
    eval("\$demConta2=$demonstrativoPartes2[1];");
}

if (isset($demonstrativoPartes3[1]) && (strpos($demonstrativoPartes3['0'], 'multa') || strpos($demonstrativoPartes3['0'], 'juros'))) {
    $valor = $novovalorboleto;
    $addsemdesconto3 = 0;
    eval("\$demConta3=$demonstrativoPartes3[1];");
} elseif (isset($demonstrativoPartes3[1])) {
    $valor = $itenscomdesconto;
    $addsemdesconto3 = $itenssemdesconto;
    $valorSemDesconto = $itenssemdesconto;
    $valorTotal = $valorSemDesconto + $valor;
    eval("\$demConta3=$demonstrativoPartes3[1];");
}

if (isset($demonstrativoPartes4[1]) && (strpos($demonstrativoPartes4['0'], 'multa') || strpos($demonstrativoPartes4['0'], 'juros'))) {
    $valor = $novovalorboleto;
    $addsemdesconto4 = 0;
    eval("\$demConta4=$demonstrativoPartes4[1];");
} elseif (isset($demonstrativoPartes4[1])) {
    $valor = $itenscomdesconto;
    $addsemdesconto4 = $itenssemdesconto;
    $valorSemDesconto = $itenssemdesconto;
    $valorTotal = $valorSemDesconto + $valor;
    eval("\$demConta4=$demonstrativoPartes4[1];");
}

if (isset($demonstrativoPartes5[1]) && (strpos($demonstrativoPartes5['0'], 'multa') || strpos($demonstrativoPartes5['0'], 'juros'))) {
    $valor = $novovalorboleto;
    $addsemdesconto5 = 0;
    eval("\$demConta5=$demonstrativoPartes5[1];");
} elseif (isset($demonstrativoPartes5[1])) {
    $valor = $itenscomdesconto;
    $addsemdesconto5 = $itenssemdesconto;
    $valorSemDesconto = $itenssemdesconto;
    $valorTotal = $valorSemDesconto + $valor;
    eval("\$demConta5=$demonstrativoPartes5[1];");
}

if ($demConta1 != 0) {
    $demConta1 = number_format(($demConta1 + $addsemdesconto1), 2, ',', '');
} else {
    $demConta1 = "";
}
if ($demConta2 != 0) {
    $demConta2 = number_format(($demConta2 + $addsemdesconto2), 2, ',', '');
} else {
    $demConta2 = "";
}
if ($demConta3 != 0) {
    $demConta3 = number_format(($demConta3 + $addsemdesconto3), 2, ',', '');
} else {
    $demConta3 = "";
}
if ($demConta4 != 0) {
    $demConta4 = number_format(($demConta4 + $addsemdesconto4), 2, ',', '');
} else {
    $demConta4 = "";
}
if ($demConta5 != 0) {
    $demConta5 = number_format(($demConta5 + $addsemdesconto5), 2, ',', '');
} else {
    $demConta5 = "";
}

$demonstrativo1 = $eventoBoleto . "<br />" . $demonstrativoPartes1[0] . $demConta1;
$demonstrativo2 = $demonstrativoPartes2[0] . $demConta2;
$demonstrativo3 = $demonstrativoPartes3[0] . $demConta3;
$demonstrativo4 = $demonstrativoPartes4[0] . $demConta4 . "<br />" . $demonstrativoPartes5[0] . $demConta5;

//******************************************************************************************//


$codigo_cliente = $row['convenio'];
$convenio = $row['convenio'];
$ponto_venda = $row['agencia'];
$agencia = $row['agencia'];

$cta = explode("-", $row['conta']);

$conta = $cta[0];

$conta_dv = $cta[1];
$numBanco = $row['banconum'];
$carteira = $row['carteira'];

$bancoarquivo = $row['bancoarquivo'];
if ($debug) {
    echo $idl . "<br>" . $bancoarquivo . "<br>";
}
// HCN
$identificacao = $row['razaosocial'];
$cpf_cnpj = $row['cnpj'];
$endereco = $row['uendereco'] . ", " . $row['unumero'] . " " . $row['ucomplemento'];
$cidade_uf = $row['nom_cidade'];
// $cedente = $row['razaosocial'];
$cedente = $row['nomefantasia'];
// /HCN
$query1 = "SELECT *, responsaveis.id as rid FROM responsaveis, pessoas, parentescos, estados, cidades WHERE ( pessoas.idestado=estados.id OR pessoas.idestado<1) AND (pessoas.idcidade=cidades.id OR pessoas.idcidade<1) AND responsaveis.idpessoa=pessoas.id AND responsaveis.idparentesco=parentescos.id AND idaluno=$idaluno AND respfin=1 GROUP BY idpessoa";
$result1 = mysql_query($query1);
$row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
if ($debug) {
    echo $query1 . " - " . $result1 . "<br>";
}

$query1a = "SELECT * FROM alunos, pessoas, estados, cidades WHERE ( pessoas.idestado=estados.id OR pessoas.idestado<1) AND (pessoas.idcidade=cidades.id OR pessoas.idcidade<1) AND alunos.id=$idaluno AND alunos.idpessoa=pessoas.id GROUP BY idpessoa";
$result1a = mysql_query($query1a);
if ($debug) {
    echo $query1a . " - " . $result1a . "<br>";
}
$row1a = mysql_fetch_array($result1a, MYSQL_ASSOC);
$nomealuno = $row1a['nome'];

if (trim($row1['nome']) == "") {
    $sacado = $row1a['nome'];
    $cpf = $row1a['cpf'];
} else {
    $sacado = $row1['nome'];
    $cpf = $row1['cpf'];
}

if (trim($row1['logradouro']) == "") {
    if ($row1a['idcidade'] > 0) {
        $nom_cidade = $row1a['nom_cidade'];
    } else {
        $nom_cidade = "";
    }
    if ($row1a['idestado'] > 0) {
        $sgl_estado = $row1a['sgl_estado'];
    } else {
        $sgl_estado = "";
    }

    $endereco1 = $row1a['logradouro'] . ", " . $row1a['numero'] . " " . $row1a['complemento'];
    $endereco2 = $row1a['bairro'] . ", " . $nom_cidade . ", " . $sgl_estado . " - CEP:" . $row1a['cep'];
} else {
    if ($row1['idcidade'] > 0) {
        $nom_cidade = $row1['nom_cidade'];
    } else {
        $nom_cidade = "";
    }
    if ($row1['idestado'] > 0) {
        $sgl_estado = $row1['sgl_estado'];
    } else {
        $sgl_estado = "";
    }

    $endereco1 = $row1['logradouro'] . ", " . $row1['numero'] . " " . $row1['complemento'];
    $endereco2 = $row1['bairro'] . ", " . $nom_cidade . ", " . $sgl_estado . " - CEP:" . $row1['cep'];
}

$query1a = "SELECT curso, turma FROM alunos_matriculas, cursos, series, turmas WHERE turmas.idserie=series.id AND series.idcurso=cursos.id AND alunos_matriculas.turmamatricula=turmas.id AND alunos_matriculas.nummatricula='" . $nummatricula . "' AND alunos_matriculas.idunidade=" . $idunidade;

$result1a = mysql_query($query1a);
if ($debug) {
    echo $query1a . " - " . $result1a . "<br>";
}
$row1a = mysql_fetch_array($result1a, MYSQL_ASSOC);
$cursoturma = $row1a['curso'] . "-" . $row1a['turma'];


// ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)   //
// DADOS DO BOLETO PARA O SEU CLIENTE

$novovalorboleto = str_replace(",", ".", $novovalorboleto);

$valor_boleto = number_format($novovalorboleto + $taxa_boleto, 2, ',', '');

$dadosboleto["nosso_numero"] = $nosso_numero;  // Nosso numero sem o DV - REGRA: Máximo de 7 caracteres!
$dadosboleto["numero_documento"] = $numero_documento; // Num do pedido ou nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto;  // Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $sacado;
$dadosboleto["endereco1"] = $endereco1;
$dadosboleto["endereco2"] = $endereco2;
$dadosboleto["nomealuno"] = $nomealuno;
$dadosboleto["cursoturma"] = $cursoturma;
$dadosboleto["cpf"] = $cpf;

// INFORMACOES PARA O CLIENTE
//$dadosboleto["demonstrativo1"] = $demonstrativo1;
//$dadosboleto["demonstrativo2"] = "Mensalidade referente a nonon nonooon nononon<br>Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '');
//$dadosboleto["demonstrativo3"] = "BoletoPhp - http://www.boletophp.com.br";
//$demons=explode("\r\n",trim($demonstrativo1));
//$demonstrativo1=$demons[0]."<br>".$demons[1]."<br>".$demons[2]."<br>".$demons[3];
$dadosboleto["instrucoes1"] = $demonstrativo1;
$dadosboleto["instrucoes2"] = $demonstrativo2;
$dadosboleto["instrucoes3"] = $demonstrativo3;
$dadosboleto["instrucoes4"] = $demonstrativo4;


// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "N";
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = ($carteira == '101') ? "DM" : "DS";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
$dadosboleto["carteira"] = $carteira;
$registro_carteira = ($carteira == '101') ? " - COM REGISTRO" : " - SEM REGISTRO";
$dadosboleto["conta_cedente"] = $conta;
if ($numBanco == 104) {
    $carteira == '01';
    $registro_carteira = ($carteira == '01') ? " - COM REGISTRO" : " - SEM REGISTRO";
}

if ($carteira) {
    $dadosboleto["carteira_descricao"] = $carteira . $registro_carteira;
} else {
    $dadosboleto["carteira_descricao"] = "CARNÊ DE COBRANÇA";  // Descrição da Carteira
}

$dadosboleto["agencia"] = $agencia; // Num da agencia, sem digito
$dadosboleto["conta"] = $conta; // Num da conta, sem digito
//$dadosboleto["conta_dv"] = $conta_dv;  // Digito do Num da conta
// DADOS PERSONALIZADOS - SANTANDER BANESPA
if ($bancoarquivo == "santander_banespa") {
    $dadosboleto["codigo_cliente"] = $codigo_cliente; // Código do Cliente (PSK) (Somente 7 digitos)
    $dadosboleto["ponto_venda"] = $ponto_venda; // Ponto de Venda = Agencia
    $dadosboleto["especie_doc"] = "DS";
}

// DADOS PERSONALIZADOS - BRADESCO
if ($bancoarquivo == "bradescp") {
    $dadosboleto["conta_cedente"] = $conta;
    $dadosboleto["conta_cedente_dv"] = $conta_dv;
}

// Composição Nosso Numero - CEF SIGCB
if ($bancoarquivo == "cef") {
    $dadosboleto["nosso_numero1"] = "000"; // tamanho 3
    $dadosboleto["nosso_numero2"] = "000"; // tamanho 3
    $dadosboleto["nosso_numero_const2"] = "4"; //constanto 2 , 4=emitido pelo proprio cliente
    $dadosboleto["nosso_numero3"] = str_pad($nosso_numero, 9, "0", STR_PAD_LEFT); // tamanho 9
    // DADOS PERSONALIZADOS - CEF

    $dadosboleto["conta_cedente"] = $codigo_cliente; // Código Cedente do Cliente, com 6 digitos (Somente Números)
}
if ($numBanco == 104) {
    if ($carteira == 01) {
        $dadosboleto["nosso_numero_const1"] = "1"; //constanto 1 , 1=registrada , 2=sem registro
        $dadosboleto["carteira"] = "RG";  // Código da Carteira: pode ser SR (Sem Registro) ou CR (Com Registro)
    } else {
        $dadosboleto["nosso_numero_const1"] = "2"; //constanto 1 , 1=registrada , 2=sem registro
        $dadosboleto["carteira"] = "SR";  // Código da Carteira: pode ser SR (Sem Registro) ou CR (Com Registro)
    }
}
// DADOS PERSONALIZADOS - HSBC
if ($bancoarquivo == "hsbc") {
    $dadosboleto["codigo_cedente"] = "1122334"; // Código do Cedente (Somente 7 digitos)
    $dadosboleto["carteira"] = "CNR";  // Código da Carteira
}

// DADOS PERSONALIZADOS - BB
if ($bancoarquivo == "bb" || $bancoarquivo == "bb2") {
    $dadosboleto["convenio"] = $convenio; // Código do Cedente (Somente 7 digitos)
}

// DADOS PERSONALIZADOS - INTER
if ($bancoarquivo == "inter") {
    $dadosboleto["numero_operacao"] = $numero_operacao;  // Número de Operação (Somente 7 digitos)
    $dadosboleto["identificacao_banco"] = $identificacaobanco;  // Identificação do boleto no banco Inter (Max. 11 digitos)
}

// SEUS DADOS
$dadosboleto["identificacao"] = $identificacao;
$dadosboleto["cpf_cnpj"] = $cpf_cnpj;
$dadosboleto["endereco"] = $endereco;
$dadosboleto["cidade_uf"] = $cidade_uf;
$dadosboleto["cedente"] = $cedente;

// por alguma razão o fluxo do "bb2" é diferente, (isso é uma conciliação das alterações locais que existiam no "gpipechincha1")
if ($bancoarquivo == "bb2") {
    require_once "include/funcoes_" . $bancoarquivo . ".php";

    if (intval($row['webhook_boleto_criado']) > 0 || intval($row['ultimo_nsu']) < 1) {
        $queryb = "SELECT RR.tipo
        FROM contasbanco C
        INNER JOIN remessa_retorno RR ON (C.remessa_tipo = RR.id)
        WHERE C.id = " . $row['id'];
        $resultb = mysql_query($queryb);
        $rowb = mysql_fetch_array($resultb, MYSQL_ASSOC);
    } elseif ($boletoEmPdf) {
        require "include/pdf/layout_" . $bancoarquivo . ".php";
    } else {
        require "include/layout_" . $bancoarquivo . ".php";
    }
}

// NÃO ALTERAR!
if ($debug == 0) {
    require "include/funcoes_" . $bancoarquivo . ".php";

    $queryb = "SELECT RR.tipo
               FROM contasbanco C
               INNER JOIN remessa_retorno RR ON (C.remessa_tipo = RR.id)
               WHERE C.id = " . $row['id'];

    $resultb = mysql_query($queryb);
    $rowb = mysql_fetch_array($resultb, MYSQL_ASSOC);

    if (str_contains($rowb['tipo'], "pix")) {
        // QR CODE
        $queryQ = "SELECT emvqrcode, `location` FROM pix WHERE alunos_fichafinanceira_id = $idl LIMIT 1";
        $resultQ = mysql_query($queryQ);

        if (mysql_num_rows($resultQ) > 0) {
            $rowQ = mysql_fetch_array($resultQ, MYSQL_ASSOC);
            $qrcode = $rowQ['emvqrcode'];
            $dadosboleto["qrcode"] = $qrcode;
            $dadosboleto["pixUrl"] = $qrcode ? $qrcode : $rowQ['location'];

            //file_put_contents('qrcode_debug.txt', $qrcode);
        }

        $layoutFile = "include/layout_";

        if ($rowb['tipo'] == "400@itaupix") {
            $layoutFile .= "itau_pix.php";
        } elseif (
            $rowb['tipo'] == "240@santanderpix" ||
            $rowb['tipo'] == "240@santanderpixremessa" ||
            $rowb['tipo'] == "400@santanderpix"
        ) {
            $layoutFile .= "santander_pix.php";
        }

        if ($apenasqrcode == "1") {
            if (mysql_num_rows($resultQ) > 0) {
                $layoutFile = "include/layout_somente_pix.php";
            } else {
                $layoutFile = "QRCodeNaoDisponivel.php";
            }
        }
        require $layoutFile;
    } elseif ($bancoarquivo == "carne") {
        require "include/layout_carne.php";
    } elseif ($boletoEmPdf) {
        require "include/pdf/layout_" . $bancoarquivo . ".php";
    } else {
        require "include/layout_" . $bancoarquivo . ".php";
    }
}
