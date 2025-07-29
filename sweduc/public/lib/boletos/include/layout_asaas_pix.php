<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento via Pix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        img {
            width: 280px;
            height: 280px;
            margin-bottom: 15px;
        }
        .copy-button {
            display: block;
            margin: 20px auto 0; /* Centraliza horizontalmente */
            padding: 12px 24px;
            background-color: #007bff; /* Azul claro */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .copy-button:hover {
            background-color: #0056b3; /* Azul mais escuro no hover */
        }
    </style>
</head>
<body>

<div class="container">
    <img src="imagens/asaaslogo.png" alt="Asaas">
    <h2>Escaneie o QR Code para pagar</h2>
    <img src="data:image/png;base64,<?= htmlspecialchars($pixEncodedImage) ?>" alt="QR Code">

    <button class="copy-button" onclick="copyPixCode()">Copiar Código Pix</button>
</div>

<script>
    function copyPixCode() {
        const pixCode = "<?= htmlspecialchars($pixPayload) ?>"; // Código Pix
        navigator.clipboard.writeText(pixCode).then(() => {
            alert("Código Pix copiado com sucesso!");
        }).catch(err => {
            alert("Erro ao copiar código Pix.");
        });
    }
</script>

</body>
</html>
