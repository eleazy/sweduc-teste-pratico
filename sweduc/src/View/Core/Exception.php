<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .title {
            color: #444;
            text-align: center;
            margin: 3rem;
        }

        .details {
            padding: 1rem;
            margin: 3rem;
            background-color: #ddd;
            border-radius: 5px;
        }

        .details-header {
            color: #444;
            margin-top: 0;
            font-weight: bold;
            text-align: center;
        }

        .context {
            margin: 1rem 0;
        }

        .context p {
            margin: 0;
        }

        .context-title {
            color: #444;
            font-weight: bold;
        }

        .context-value {
            color: #c6262e;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1 class="title">
        <?=$code?> - <?=$title?>
    </h1>

    <div class="details">
        <?php if (!empty($details)) : ?>
            <p class="details-header">
                <?=$details?>
            </p>
        <?php endif ?>

        <?php if (!empty($contexto)) : ?>
            <?php foreach ($contexto as $title => $val) : ?>
                <div class="context">
                    <p class="context-title"><?=$title?></p>
                    <p class="context-value"><?=$val?></p>
                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>
</body>
</html>
