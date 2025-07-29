<?php

use App\Financeiro\Controller\PixController;

$bancoarquivo = $bancoarquivo ?? '';
$dadosboleto = $dadosboleto ?? [];
$qrCode = PixController::extractQrCodeEMV($dadosboleto['qrcode']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Pix <?php echo htmlspecialchars($bancoarquivo); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;

        }
        .container {
            text-align: center;
            background-color: #ffffff;
            padding: 20px;
        }
        .logo {
            margin-bottom: 20px;
        }
        .qrcode {
            margin: 30px 0;
        }
        .ct {
            display: block;
            margin: 10px 0px;
            font-size: 14px;
            color: #333;
        }
        .copiarCodigo {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.copiarCodigo').addEventListener('click', function() {
                var text = "<?php echo htmlspecialchars($qrCode); ?>";
                var input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                alert('Código copiado para a área de transferência.');
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <?php if (!empty($qrCode)) : ?>
            <img src="imagens/logopix.png" width="160" alt="Logo Pix" class="logo">
            <div class="qrcode">
                <img src="data:image/png;base64,<?php echo fqrcode($qrCode); ?>" alt="QR Code" width="220" height="220">
            </div>
            <div class="info">
                <span class="ct">Vencimento: <?= htmlspecialchars($dadosboleto["data_vencimento"]); ?></span>
                <span class="ct">Valor: <?= htmlspecialchars($dadosboleto["valor_boleto"]); ?></span>
                <span class="ct"><?= strip_tags($dadosboleto["instrucoes1"]); ?></span>
            </div>
            <span class="ct">Pix Copia e Cola</span>
            <span class="ct"><?= htmlspecialchars($qrCode); ?></span>
            <button class="copiarCodigo">Copiar</button>
        <?php endif; ?>
    </div>
</body>
</html>
