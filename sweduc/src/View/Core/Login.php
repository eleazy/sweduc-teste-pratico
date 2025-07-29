<!DOCTYPE html>
<html style="height: 100%;">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>..::  SW Educ  ::..</title>
        <link rel="icon" type="image/png" href="images/logo-sweduc.png"/>
        <link rel="stylesheet" href="<?=$this->asset('/assets/login.css')?>">

        <style>
            .wrapper {
                background: <?=$corRgb?> !important;
            }
            form button {
                background-color: <?=$corRgbTransparent?> !important;
            }
            form button:hover {
                background-color: <?=$corRgb?> !important;
            }
            form input {
                background-color: <?=$corRgbTransparent?> !important;
            }
        </style>
    </head>

    <body class="strech-page" style="height: 100%;">
        <div class="center">
            <img src="clientes/<?=$cliente?>/logo.png" class="logo" alt="" />
        </div>

        <div class="wrapper center">
            <form id="formlogin" action="<?=$loginUrl?>" method="post" name="formlogin" class="form">
                <?php if (!empty($_SESSION['erro_msg'])) : ?>
                    <p class="erro-msg">
                        <?=$_SESSION['erro_msg']?>
                    </p>
                    <?php unset($_SESSION['erro_msg']); ?>
                <?php endif ?>

                <h1>Acesso</h1>
                <div class="separador"></div>

                <div id="loginInputs">
                    <input type="hidden" name="idescola" value="1">
                    <input type="text" placeholder="Usuário" name="login">
                    <input type="password" placeholder="Senha" name="senha" >
                </div>

                <button type="submit" name="logar" id="envialogin"  class="submit-login">Entrar</button>

            </form>

            <div class="container-staff-login ">
                <a href="/staff-login" class="btn-staff-login btn-staff-login-bottom">
                    Staff Login
                </a>
            </div>

        </div>

        <div class="center">
            <a href="http://swsistemas.com.br" target="_blank">
                <img src="images/logo_sweduc_80.png" alt="Sistema Sweduc" class="logo" />
            </a>
        </div>
    </body>
</html>
