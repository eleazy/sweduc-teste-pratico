<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWeduc - <?=$nome?></title>
    <style>
        #print-btn {
            display: inline-block;
            padding: 0.2em 1.45em;
            margin: 0.1em;
            border: 0.15em solid #CCCCCC;
            box-sizing:  border-box;
            text-decoration: none;
            font-family: 'Segoe UI','Roboto',sans-serif;
            font-weight: 400;
            color: #000000;
            background-color: #CCCCCC;
            text-align: center;
            position: relative;
        }
        #print-btn:hover{
            border-color: #7a7a7a;
        }
        #print-btn:active{
            background-color: #999999;
        }

        @media print {
            #print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div style="position: fixed;top: 0;left: 0;z-index: 99999;">
        <button id="print-btn" type="button" onclick="window.print()">
            Imprimir
        </button>
    </div>

    <?php foreach ($documentos as $documento) : ?>
        <div style="page-break-before: always;page-break-after: always;">
            <?=$documento?>
        </div>
    <?php endforeach ?>
</body>
</html>
