<?php

include_once '../../dao/conectar.php';

use App\Model\Financeiro\AsaasCobranca;
use App\Model\Financeiro\Titulo;
use App\Asaas\Models\Asaas;

$keys = array_keys($_REQUEST);

foreach ($keys as $k) {
    ${$k} = $_REQUEST[$k];
}

$idlanca = explode(",", $idlancamento);

$query = "SELECT
        banconum,
        nomeb
    FROM
        alunos_fichafinanceira,
        contasbanco,
        empresas,
        unidades,
        unidades_empresas,
        alunos_matriculas,
        cidades
    WHERE
        (unidades.cidade=cidades.id OR unidades.cidade=0) AND
        alunos_fichafinanceira.nummatricula = alunos_matriculas.nummatricula AND
        unidades_empresas.idempresa=empresas.id AND
        unidades_empresas.idunidade=unidades.id AND
        alunos_fichafinanceira.idaluno=alunos_matriculas.idaluno AND
        alunos_matriculas.idunidade=unidades.id AND
        alunos_fichafinanceira.idcontasbanco=contasbanco.id AND
        contasbanco.idempresa=empresas.id AND
        alunos_fichafinanceira.id='{$idlanca[0]}'";

$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Boleto <?= $row['nomeb'] ?></title>
    <link rel="icon" type="image/png" href="/images/logo-sweduc.png?v=3">
    <style type="text/css">
        @media all {
            .page-break {
                display: none;
            }
        }

        @media print {
            .page-break {
                display: block;
                page-break-before: always;
            }
        }

        @page {
            /* this affects the margin in the printer settings */
            margin: 5mm;
            /*size:8.27in 11.69in;     */
        }
    </style>
</head>

<?php
$apenasqrcode = $apenasqrcode == 'true';

function recuperarCobrancaBoleto($cobranca)
{
    $asaas = new Asaas();

    if ($cobranca->data_excluida) {
        try {
            $response = $asaas->restaurarCobranca($cobranca);
        } catch (\Exception $e) {
            echo "Erro ao gerar boleto. Por favor entre em contato.";
            exit;
        }

        if (!$response['id']) {
            exit;
        }
    }
    return [
        'boleto' => $cobranca->link_boleto,
        'imagePix' =>  $cobranca->pix_encodedImage,
        'payload' => $cobranca->pix_payload
    ];
}

if ($row['banconum'] == '461') {
    if (count($idlanca) == 1) {
        $cobranca = AsaasCobranca::where('id_alunos_fichafinanceira', $idlanca[0])->where('billing_type', 'BOLETO')->first();
        if (!$cobranca) {
            echo "Cobrança Asaas não encontrada, pode ter sido excluída.";
            exit;
        }
        $url = recuperarCobrancaBoleto($cobranca);

        if ($apenasqrcode) {
            $pixEncodedImage = $url['imagePix']; // QR Code em base64
            $pixPayload = $url['payload'];  // Código Pix copiável
            require 'include/layout_asaas_pix.php';
        } else {
            echo "<script>window.location.href='{$url['boleto']}';</script>";
        }
        exit;
    } else {
        // foreach ($idlanca as $idl) {
        //     $cobranca = AsaasCobranca::where('id_alunos_fichafinanceira', $idl)->where('billing_type', 'BOLETO')->first();
        //     if (!$cobranca) {
        //         echo "Cobrança Asaas não encontrada, pode ter sido excluída.";
        //         continue;
        //     }
        //     $titulo = Titulo::find($idl)->titulo;
        //     $url = recuperarCobrancaBoleto($cobranca);

        //     $linkBoleto = $url['boleto'];

        //     echo "<h2>Título $titulo</h2>";

        //     echo "<iframe src='$linkBoleto' style='width:100%; height:800px; border:1px solid #ccc; margin-bottom: 30px;'></iframe>";
        // }
        echo "<h2>Para visualizar ou imprimir vários títulos por favor acesse a plataforma do Asaas.</h2>";
    }
} else {
    foreach ($idlanca as $idl) {
        $h = '';
        if ($row['banconum'] == '0') {
            // Se banconum==0 (carnê), a altura do iframe é reduzida
            $h = 'height:425px;padding-bottom:20px';
        } elseif ($row['banconum'] == '341' || $row['banconum'] == '033') {
            // Para os bancos 341 e 033 (Itaú e Santander), pode haver QRCode, então a altura é aumentada
            $h = 'height:550px';
        }
        echo "<iframe src='fazboletos.php?idl=" . $idl . "&apenasqrcode=" . $apenasqrcode . "&tipoUsuario=" . $tipousuario . "' class='ifr' style='width:1100px;$h;top:0px;left:0px;border-bottom:3px dashed #000;' frameborder='0' scrolling='no' height='460px' id='fazbol" . $idl . "' ></iframe><br />";
    }
}
?>
