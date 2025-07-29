<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?=(isset($clienteNome)) ? $clienteNome . ' - SW Educ' : '..::  SW Educ  ::..' ?></title>
        <link href="<?=$this->asset('assets/app.css')?>" rel="stylesheet" type="text/css" />
        <link rel="icon" type="image/png" href="images/logo-sweduc.png"/>
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,700,700i" rel="stylesheet">
    </head>

    <body class="bg-blue-100 flex">
        <div class="p-5 w-full md:w-2/4 lg:w-1/3 mx-auto my-auto">
            <form action="/trocar-senha" method="POST" class="bg-white rounded p-5 shadow">
                <h1 class="m-0 px-0 py-3 text-gray-900 text-center relative">
                    Alterar senha
                    <i class="fa fa-key p-3 bg-yellow-300 rounded-full align-middle -mr-12"></i>
                </h1>

                <p class="text-gray-600 mb-0 text-center">
                    Você fez uso de uma senha de acesso temporário. <br>
                    Por favor, forneça uma nova senha.
                </p>

                <?php if (!empty($erro)) : ?>
                    <p class="text-red-600 font-bold mb-0 text-center py-3">
                        <?=$erro?>
                    </p>
                <?php endif ?>

                <div class="my-3">
                    <label for="nova-senha">Nova senha</label>
                    <input
                        id="nova-senha"
                        type="password"
                        name="nova-senha"
                        minlength="6"
                        class="form-element"
                    >
                </div>

                <div class="my-3">
                    <label for="nova-senha">Repita a senha</label>
                    <input
                        id="repete-nova-senha"
                        type="password"
                        minlength="6"
                        class="form-element"
                    >
                </div>

                <div class="pt-2 text-right">
                    <button class="sw-btn sw-btn-primary">Confirmar</button>
                </div>
            </form>
        </div>

        <script src="<?=$this->asset('js/moment.js')?>"></script>
        <script defer src="<?=$this->asset('/js/fontawesome/all.min.js')?>"></script>
        <script src="<?=$this->asset('js/index.js')?>" defer></script>
        <script src="<?=$this->asset('assets/app.js')?>" defer></script>

        <script>
            assetsVersion = '<?=$assetsVersion?>';
        </script>
    </body>
</html>
