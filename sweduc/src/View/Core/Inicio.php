<?php
require_once  __DIR__ . '/../../../public/dao/conectar.php';
require_once  __DIR__ . '/../../../public/auth/injetaCredenciais.php';
include __DIR__ . '/../../../public/permissoes.php';

$idfuncionario = $_SESSION['id_funcionario'];
$idfuncionariounidade = $_SESSION['id_unidade'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= (isset($clienteNome)) ? $clienteNome . ' - SW Educ' : '..::  SW Educ  ::..' ?></title>
    <link href="<?= $this->asset('assets/app.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="css/style.css"> <!-- Estilo Geral -->
    <link href="<?= $this->asset('css/headerMenu.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= $this->asset('css/headerNotification.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="icon" type="image/png" href="images/logo-sweduc.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.min.css" integrity="sha512-XaXIRtZS8tPFOIDUPIUZIEc7uBiTTo+WQQjFeZ0MJ7jG/RUFh+Hx4yX5Vee6xSnEj2XXaFm545NgREfvyE9Qgw==" crossorigin="anonymous" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,700,700i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <!-- Fonte usada no calendário -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/customEventsCalendar.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboardTelaInicial.css">

    <?php
    $propaganda = 1;
    if ($tipo > 1) {  // FUNCIONARIOS teste
        $query = "SELECT funcionarios.idunidade as idunidade, funcionarios.id as fid, unidade  FROM funcionarios, unidades WHERE funcionarios.idunidade=unidades.id AND  funcionarios.idpessoa=$idpessoalogin";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        $idfuncionario = $row['fid'];
        $idfuncionariounidade = $row['idunidade'];
        $nomeunidade = $row['unidade'];
        echo '<input type="hidden" name="idfuncionario" id="idfuncionario" value="' . $idfuncionario . '">';
        echo '<input type="hidden" name="idfuncionariounidade" id="idfuncionariounidade" value="' . $idfuncionariounidade . '">';
    } else {
        echo '<input type="hidden" name="idfuncionario" id="idfuncionario" value="' . $idpessoalogin . '">';
        echo '<input type="hidden" name="idfuncionariounidade" id="idfuncionariounidade" value="0">';
    }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->
    <!-- Char.js, usado no dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!--  jquery core -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js" integrity="sha512-YHQNqPhxuCY2ddskIbDlZfwY6Vx3L3w9WRbyJCY81xpqLmrM6rL2+LocBgeVHwGY9SXYfQWJ+lcEWx1fKS2s8A==" crossorigin="anonymous"></script>
    <!--  jquery UI -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" integrity="sha512-kHKdovQFIwzs2NABJSo9NgJKZOrRqLPSoqIumoCkaIytTRGgsddo7d0rFyyh8RvYyNNKcMF7C9+4sM7YhMylgg==" crossorigin="anonymous"></script>
    <!-- usando a versão modificada de http://www.conductiva.com/en/blog/post/76/Improvement-in-the-Masked-Input-Plugin-for-jQuery
            Removido o $.browser.msie que não é mais suportado após jquery1.9 -->
    <script src="<?= $this->asset('js/jquery/jquery.maskedinput-1.2.2-co.new.min.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery/jshashtable-2.1.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery/jquery.numberformatter-1.2.3.min.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery/jquery.filestyle.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery.maskedMoney.min.js') ?>"></script>

    <!--  checkbox styling script -->
    <script src="<?= $this->asset('js/jquery/ui.core.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery/ui.checkbox.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery/jquery.ui.timepicker.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jasny-bootstrap.min.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/toastr.min.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery.maskMoney-3.1.2.min.js') ?>"></script>
    <script src="<?= $this->asset('js/sweetalert.min.js') ?>"></script>
    <script src="<?= $this->asset('js/clockpicker.js') ?>"></script>
    <script src="<?= $this->asset('js/bootstrap-colorpicker.min.js') ?>"></script>
    <![if !IE 7]>
    <!--  styled select box script version 1 -->
    <script src="<?= $this->asset('js/jquery/jquery.selectbox-0.5.js') ?>" class="jsbin"></script>
    <script type="text/javascript">
        jQuery(function() {
            jQuery.support.borderRadius = false;
            jQuery.each(['BorderRadius', 'MozBorderRadius', 'WebkitBorderRadius', 'OBorderRadius', 'KhtmlBorderRadius'], function() {
                if (document.body.style[this] !== undefined)
                    jQuery.support.borderRadius = true;
                return (!jQuery.support.borderRadius);
            });
        });
        $(function() {
            if (!$.support.borderRadius) {
                $('.button').each(function() {
                    $(this).wrap('<div class="buttonwrap"></div>')
                        .before('<div class="corner tl"></div><div class="corner tr"></div>')
                        .after('<div class="corner bl"></div><div class="corner br"></div>');
                });
            }
        });
    </script>
    <![endif]>
    <script src="<?= $this->asset('js/jquery/jquery.selectbox-0.5_style_2.js') ?>" class="jsbin"></script>
    <script src="<?= $this->asset('js/jquery/jquery.filestyle.js') ?>" class="jsbin"></script>
    <script type="text/javascript" charset="utf-8">
        location = '#';

        function update_rows() {
            $("#product-table tbody").find("tr:even").css("background-color", "#aaa");
            $("#product-table tbody").find("tr:odd").css("background-color", "#eee");
        }

        $.fn.hasAttr = function(name) {
            return this.attr(name) !== undefined;
        };

        $(function() {
            $("#dialog").dialog({
                autoOpen: false,
                width: 920,
                position: {
                    my: "left top",
                    at: "left bottom",
                    of: "#table-content"
                },
                modal: true,
                show: {
                    effect: "blind",
                    duration: 1000
                },
                hide: {
                    effect: "clip",
                    duration: 1000
                }
            });
        });
    </script>
    <style>
        .ui-dialog {
            z-index: 999;
            top: 200px;
            left: 200px;
            position: absolute;
        }
    </style>

    <script>
        (function(h, o, t, j, a, r) {
            h.hj = h.hj || function() {
                (h.hj.q = h.hj.q || []).push(arguments)
            };
            h._hjSettings = {
                hjid: 3412862,
                hjsv: 6
            };
            a = o.getElementsByTagName('head')[0];
            r = o.createElement('script');
            r.async = 1;
            r.src = t + h._hjSettings.hjid + j + h._hjSettings.hjsv;
            a.appendChild(r);
        })(window, document, 'https://static.hotjar.com/c/hotjar-', '.js?sv=');
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JF3BFCWP26"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-JF3BFCWP26');
    </script>
</head>

<body style="position: relative; ">
    <div id="dialog" title="" style="z-index:-100;"></div>
    <div id="displayBox" style="display:none">
        <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" fill="#f0c552">
            <circle cx="60" cy="60" r="45" fill="none" stroke="#fceabb" stroke-width="18" opacity="0.5" />
            <circle cx="60" cy="60" r="45" fill="none" stroke="#f0c552" stroke-width="18" stroke-dasharray="203" stroke-dashoffset="15" stroke-linecap="round">
                <animateTransform
                    attributeName="transform"
                    type="rotate"
                    from="0 60 60"
                    to="360 60 60"
                    dur="1.2s"
                    repeatCount="indefinite" />
            </circle>
        </svg>
    </div>
    <div id="displayResp" style="display:none;overflow:auto;"></div>
    <input type="hidden" name="idpessoalogin" id="idpessoalogin" value="<?= $idpessoalogin ?>">
    <input type="hidden" name="nomefuncionario" id="nomefuncionario" value="<?= $_SESSION['nome'] ?>">
    <input type="hidden" name="idpermissoes" id="idpermissoes" value="<?= $idpermissoes ?>">
    <input type="hidden" name="tipo" id="tipo" value="<?= $tipo ?>">

    <div id="header-outer" class="print:hidden noPrint">
        <div id="header-upper">
            <div id="menu_button_inicio">
                <svg width="40px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_429_11066)">
                        <path d="M3 6.00092H21M3 12.0009H21M3 18.0009H21" stroke="#292929" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>

            <div id="header-logos">
                <a href="/"><img id="swlogo" src="images/logo-sweduc.png" alt="" /></a>
                <a href="/"><img id="clientelogo" src="/logo" alt="" /></a>
            </div>

            <div id="header-right" class="flex">
                <div class="user-options-outer">
                    <div class="header-user-icon">
                        <i class="fa fa-user-circle fa-3x"></i>
                    </div>

                    <div class="user-options">
                        <div><i class="fa fa-user"></i> Bem vindo <?= $nomeusuario ?></div>
                        <div><i class="fa fa-industry"></i> Unidade <?= $nomeunidade ?></div>
                        <div class="cursor-pointer" onclick="sweduc.carregarUrl('alunos_troca_senha.php');"><i class="fa fa-exchange-alt"></i> Trocar senha</div>

                        <div class="cursor-pointer" onclick="sweduc.logout(); sessionStorage.clear();">
                            <i class="fa fa-sign-out-alt"></i>
                            <?php if (empty($_SESSION['personificador'])) : ?>
                                Sair
                            <?php else : ?>
                                Retornar para conta <?= $_SESSION['personificador']['nome'] ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->insert('Core/Components/Menu', compact(
            'tipo',
            'usuario',
            'menu',
            'tipodefaltas',
        )) ?>
    </div>

    <?php
    function arredonda05($num)
    {
        $inteiro = floor($num);
        $dec = number_format($num - $inteiro, 1);

        if ($dec < (0.3)) {
            return $inteiro;
        }
        if ($dec >= (0.3) && $dec < (0.8)) {
            return $inteiro += (0.5);
        }
        if ($dec >= (0.8)) {
            return $inteiro += 1;
        }

        return $inteiro;
    }
    ?>

    <div id="content-outer">
        <div id="conteudo" style="float:left;position: relative;width:100%; margin-top: 190px;"></div>
        <div id="app" style="float:left;position: relative;width:100%; margin-top: 190px;"></div>
        <div class="clear">&nbsp;</div>
    </div>

    <div class="clear">&nbsp;</div>
    <?php if (in_array("marketing-acesso-prospeccao-responsavel", $listaPermissoes)) : ?>
        <script>
            $(document).ready(function() {
                update_rows();
                $.ajax({
                    url: "/marketing/cadastrar",
                    type: 'GET',
                    context: $("#conteudo"),
                    success: function(data) {
                        this.html(data);
                        $('#loader').hide();
                    }
                });
            });
        </script>
    <?php elseif ($academico[11] == 1) : ?>
        <script>
            $(document).ready(function() {
                update_rows();
                $.ajax({
                    url: "academico_diario_online.php",
                    type: 'POST',
                    data: {
                        idfuncionario: $("#idfuncionario").val(),
                        idpessoalogin: $("#idpessoalogin").val(),
                        idpermissoes: $("#idpermissoes").val()
                    },
                    context: $("#conteudo"),
                    success: function(data) {
                        this.html(data);
                        $('#loader').hide();
                    }
                });
            });
        </script>
    <?php elseif ($tipoUsuario == 1 || $tipoUsuario == 0) :
        ?> <!-- Redirecionamento para perfil Responsavel ou perfil Aluno -->
        <script>
            $(document).ready(function() {
                var url = '<?= $tipoUsuario == 1 ? '/responsaveis/matricula' : '/alunos_alunos_lista.php' ?>';
                sweduc.carregarUrl(url);
                const headerMenu = document.querySelector('#header-outer'); /* Esconde menu */
                if (headerMenu) headerMenu.style.display = 'none';
            });
        </script>
    <?php else : ?>
        <script>
            $(document).ready(function() {
                update_rows();
                $.ajax({
                    url: "desktop.php",
                    type: 'POST',
                    data: {
                        idfuncionario: $("#idfuncionario").val(),
                        idpessoalogin: $("#idpessoalogin").val(),
                        idpermissoes: $("#idpermissoes").val(),
                        tipo: $('#tipo').val()
                    },
                    context: $("#conteudo"),
                    success: function(data) {
                        this.html(data);
                        $('#loader').hide();
                        if ($("#usrprofessor").val() == 1) {
                            $("#usrprofessor").val(0);
                            sweduc.carregarUrl('academico_diario_online.php');
                            $("#smoothmenu1").hide();
                        }
                    }
                });
            });
        </script>
    <?php endif ?>

    <div id="loading-screen" class="w-full h-screen fixed top-0 left-0" style="display: none;z-index: 999;">
        <div class="mx-auto my-auto rounded overflow-hidden" style="z-index: 9999;">
            <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg" fill="#f0c552">
                <circle cx="60" cy="60" r="45" fill="none" stroke="#fceabb" stroke-width="18" opacity="0.5" />
                <circle cx="60" cy="60" r="45" fill="none" stroke="#f0c552" stroke-width="18" stroke-dasharray="203" stroke-dashoffset="15" stroke-linecap="round">
                    <animateTransform
                        attributeName="transform"
                        type="rotate"
                        from="0 60 60"
                        to="360 60 60"
                        dur="1.2s"
                        repeatCount="indefinite" />
                </circle>
            </svg>
        </div>

        <div class="bg-black w-full h-screen absolute opacity-50"></div>
    </div>

    <script src="<?= $this->asset('/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?= $this->asset('js/moment.js') ?>"></script>
    <script src="<?= $this->asset('js/customEventsCalendar.js') ?>"></script>
    <script src="<?= $this->asset('js/dashboardTelaInicial.js') ?>"></script>
    <script defer src="<?= $this->asset('/js/fontawesome/all.min.js') ?>"></script>
    <script src="<?= $this->asset('js/index.js') ?>" defer></script>
    <script src="<?= $this->asset('js/headerMenu.js') ?>" defer></script>
    <script src="<?= $this->asset('js/svgIcons.js') ?>" defer></script>
    <script src="<?= $this->asset('assets/app.js') ?>" defer></script>
    <script>
        assetsVersion = '<?= $assetsVersion ?>';
        sweduc_permissions = ['<?= implode('\',\'', $listaPermissoes) ?>']
    </script>
</body>

</html>
