
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$this->e($title ? "SW Educ · $title" : 'SW Educ')?></title>
    <link rel="icon" type="image/png" href="/images/logo-sweduc.png"/>
    <link rel="stylesheet" href="<?=$this->asset('/assets/index.css')?>">
</head>
<body class="bg-light">
    <?=$this->insert('Layout/Header')?>

    <div id="conteudo">
        <?=$this->section('content')?>
    </div>

    <script src="<?=$this->asset('/assets/guest.js')?>" defer></script>
</body>
</html>
