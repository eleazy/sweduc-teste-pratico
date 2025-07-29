<?php

use App\Usuarios\AuthManager;

require __DIR__ . '/../../../../public/permissoes.php';

?>
<link rel="stylesheet" type="text/css" href="css/headerMenu.css" />

<style type="text/css">
    .window{
        display:none;
        width:900px;
        height:500px;
        position:absolute;
        left:0;
        top:0;
        background:#FFF;
        z-index:9900;
        padding:10px;
        border-radius:10px;
    }

    #mascara{
        position:absolute;
        left:0;
        top:0;
        z-index:9000;
        background-color:#000;
        display:none;
    }

    #videoRematricula:hover {
        background: url(../images/shared/nav/repeat.jpg) repeat-x;
    }

    .fechar{display:block; text-align:right;}
</style>

<div id="header-menu">
    <!-- <?php if ($marketing[2] != 1 && $academico[11] != 1) : ?>
        <ul>
            <li>
                <a class="no-hover" href="#" onClick="sweduc.carregarUrl('desktop.php');" style="padding:2px;"><img src="images/shared/nav/home.png" border="0" style="margin-top:-4px;"></a>
            </li>
        </ul>
         <?php endif ?> -->

    <?php if ($tipo == 0) { //ALUNOS    ?>
        <!-- ********************** ALUNOS ( ) *************************************** -->

        <ul class="mainOption">
            <li><a href="#" onClick="sweduc.carregarUrl('alunos_alunos_lista.php');"><b>Alunos</b></a>
            </li>
        </ul>
        <!-- ********************** FINANCEIRO ( ) *************************************** -->

        <!-- ********************** CONFIGURAÇÕES ( ) *************************************** -->

        <ul class="mainOption">
            <li><a href="#nogo"><b>Configurações</b></a>
            </li>
        </ul>
        <ul class="mainOption">
            <!-- ********************** CONFIGURAÇÕES EMPRESA *************************************** -->
            <li><a href="#" onClick="sweduc.carregarUrl('alunos_troca_senha.php');">Troca de Senha</a></li>
        </ul>

    <?php } elseif ($tipo == 1) { //RESPONSÁVEIS ?>
        <?php
        $idpessoalogin = (new AuthManager())->usuario()->idpessoa;

        // HCN
        $queryFin = "SELECT alunos_fichafinanceira.id as ffin_id, alunos.id as aluno_id, alunos_fichafinanceira.nummatricula
                    FROM alunos_fichafinanceira
                    INNER JOIN alunos ON alunos_fichafinanceira.idaluno=alunos.id
                    INNER JOIN responsaveis ON alunos.id=responsaveis.idaluno
                    INNER JOIN alunos_fichaitens ON alunos_fichafinanceira.id=alunos_fichaitens.idalunos_fichafinanceira
                    INNER JOIN eventosfinanceiros ON alunos_fichaitens.codigo=eventosfinanceiros.codigo
                    WHERE responsaveis.idpessoa='$idpessoalogin'
                        AND alunos_fichafinanceira.situacao='0'
                        AND eventosfinanceiros.eventofinanceiro LIKE '1%Parcela%da%Anuidade'";
        $resultFin = mysql_query($queryFin);
        // print_r(mysql_fetch_array($resultFin, MYSQL_ASSOC));
        $rowFin = mysql_fetch_array($resultFin, MYSQL_ASSOC);

        try {
            $queryUnid = "SELECT idunidade FROM alunos_matriculas WHERE idaluno=" . $rowFin['aluno_id'] . " AND nummatricula=" . $rowFin['nummatricula'];
            $resultUnid = mysql_query($queryUnid);
            $rowUnid = mysql_fetch_array($resultUnid, MYSQL_ASSOC);
        } catch (\Throwable) {
            $rowUnid = [];
        }

        $idalunoDocValue = $rowUnid['idunidade'] . '@' . $rowFin['aluno_id'] . '@' . $rowFin['nummatricula'] . '@';

        if (!empty($rowFin)) {
            // define o contrato a ser exibido
            if ($rowUnid['idunidade'] == 2) {
                $nome_documento = 'Contrato%Puggi';
            } else {
                $qserie = "SELECT series.id as serieid, series.serie, cursos.id as cursoid, cursos.curso
                        FROM alunos_matriculas
                        INNER JOIN unidades ON alunos_matriculas.idunidade=unidades.id
                        INNER JOIN turmas ON alunos_matriculas.turmamatricula=turmas.id
                        INNER JOIN series ON turmas.idserie=series.id
                        INNER JOIN cursos ON series.idcurso=cursos.id
                        WHERE alunos_matriculas.idaluno=" . $rowFin['aluno_id'] . "
                        AND alunos_matriculas.nummatricula=" . $rowFin['nummatricula'];

                $resSerie = mysql_query($qserie);
                $rowSerie = mysql_fetch_array($resSerie, MYSQL_ASSOC);

                if ($rowSerie['curso'] == 'Educação Infantil') {
                    $nome_documento = ($rowSerie['serie'] == 'Pré-Escola 2') ? 'Contrato%Prisma' : 'Contrato%Alfacem';
                } else {
                    $nome_documento = 'Contrato%Prisma';
                }
                // print_r($rowSerie);
            }
        }


        $queryReq = "SELECT id FROM doceditor where nomedoc like 'Requerimento de Matricula - 2018' LIMIT 1";
        $resultReq = mysql_query($queryReq);
        // print_r(mysql_fetch_array($resultFin, MYSQL_ASSOC));
        $rowReq = mysql_fetch_array($resultReq, MYSQL_ASSOC);

        $queryCon = "SELECT id FROM doceditor where nomedoc like '$nome_documento' LIMIT 1";
        $resultCon = mysql_query($queryCon);
        // print_r(mysql_fetch_array($resultFin, MYSQL_ASSOC));
        $rowCon = mysql_fetch_array($resultCon, MYSQL_ASSOC);

        $queryAnam = "SELECT id FROM doceditor where nomedoc like 'Anamnese%' LIMIT 1";
        $resultAnam = mysql_query($queryAnam);
        // print_r(mysql_fetch_array($resultFin, MYSQL_ASSOC));
        $rowAnam = mysql_fetch_array($resultAnam, MYSQL_ASSOC);

        // reemitir boleto janeiro
        $queryBol = "SELECT alunos_fichafinanceira.id as ffin_id, alunos.id as aluno_id, alunos_fichafinanceira.nummatricula
                    FROM alunos_fichafinanceira
                    INNER JOIN alunos ON alunos_fichafinanceira.idaluno=alunos.id
                    INNER JOIN responsaveis ON alunos.id=responsaveis.idaluno
                    WHERE responsaveis.idpessoa='$idpessoalogin'
                        AND alunos_fichafinanceira.situacao='0'
                        AND ( alunos_fichafinanceira.datavencimento BETWEEN '2016-01-01' AND '2016-01-31')";
        // print $queryBol;
        $resultBol = mysql_query($queryBol);
        // $rowBol = mysql_fetch_array($resultBol, MYSQL_ASSOC);

        $idboletosreemitir = '';

        while ($rowBol = mysql_fetch_array($resultBol, MYSQL_ASSOC)) {
            $idboletosreemitir .= $rowBol['ffin_id'] . ',';
        }

        $idboletosreemitir = rtrim($idboletosreemitir, ',');

        // /reemitir boleto janeiro
        // /HCN
        ?>

        <script>
            function umboleto(idlancamento) {
                $("#idlancamento").val(idlancamento);
                $("#dadosboleto").submit();
            }

            function reemiteboleto() {
                $("#reemiteboletoform").submit();
            }

            function executarequerimento(idunidade, idaluno, nummat, nomerequerimento) {
                $.ajax({
                    url: 'define_contrato_aluno.php',
                    type: 'POST',
                    data: {idunidade: idunidade, idaluno: idaluno, nummat: nummat, tipodocumento: '2', nomerequerimento: nomerequerimento},
                    success: function (data) {
                        $("#idDOC").val(data);
                        $("#nomefuncDOC").val($("#nomefuncionario").val());
                        $("#idalunoDOC").val(idunidade + '@' + idaluno + '@' + nummat + '@');
                        $("#geraDocs").submit();
                    }
                });
            }

            function executaBoleto(idaluno) {
                $.ajax({
                    url: 'define_contrato_aluno.php',
                    type: 'POST',
                    data: {idaluno: idaluno, tipodocumento: '3'},
                    success: function (data) {
                        $('#boleto-reemissao').val(data);
                        $("#reemiteboletoform").submit();
                    }
                });
            }

            function executacontrato() {
                $("#nomefuncDOC").val($("#nomefuncionario").val());
                $("#idalunoDOC").val("<?= $idalunoDocValue ?>");
                $("#idDOC").val(<?= $rowCon['id'] ?>);
                $("#geraDocs").submit();
            }

            function abreanamnese() {
                $("#nomefuncDOC").val($("#nomefuncionario").val());
                $("#idalunoDOC").val("<?= $idalunoDocValue ?>");
                $("#idDOC").val(<?= $rowAnam['id'] ?>);
                $("#geraDocs").submit();
            }

            function contratoAluno(idunidade, idaluno, nummat, idpessoa, idempresa) {
                $.ajax({
                    url: 'define_contrato_aluno.php',
                    type: 'POST',
                    data: {idunidade: idunidade, idaluno: idaluno, nummat: nummat, idpessoa: idpessoa, idempresa: idempresa, tipodocumento: '1'},
                    success: function (data) {
                        $("#idDOC").val(data);
                        $("#nomefuncDOC").val($("#nomefuncionario").val());
                        $("#idalunoDOC").val(idunidade + '@' + idaluno + '@' + nummat + '@' + idpessoa + '@' + idempresa);
                        $("#idanoletivoDOC").val('<?= $idAno['id'] ?>');
                        $("#geraDocs").submit();
                    }
                });
            }

            $(document).ready(function () {
                $('[data-toggle="tooltip"]').tooltip();
                $("a[rel=modal]").click(function (ev) {
                    ev.preventDefault();
                    var id = $(this).attr("href");
                    var alturaTela = $(document).height();
                    var larguraTela = $(window).width();
                    $('#mascara').css({'width': larguraTela, 'height': alturaTela});
                    $('#mascara').fadeIn(1000);
                    $('#mascara').fadeTo("slow", 0.8);
                    var left = ($(window).width() / 2) - ($(id).width() / 2);
                    var top = ($(window).height() / 2) - ($(id).height() / 2);
                    $(id).css({'top': top, 'left': left});
                    $(id).show();
                });

                $("#mascara").click(function () {
                    $(this).hide();
                    $(".window").hide();
                });

                $('.fechar').click(function (ev) {
                    ev.preventDefault();
                    $("#mascara").hide();
                    $(".window").hide();
                });

            });


        </script>
        <!-- ********************** ALUNOS ( ) *************************************** -->

        <ul>
            <li><a href="#" onClick="sweduc.carregarUrl('/responsaveis/matricula');"><b>Alunos</b></a>
            </li>
        </ul>
        <!-- ********************** FINANCEIRO ( ) *************************************** -->
        <!-- ********************** CONFIGURAÇÕES ( ) *************************************** -->

        <ul>
            <li><a href="#nogo"><b>Configurações</b></a>
                <ul>
                    <!-- ********************** CONFIGURAÇÕES EMPRESA *************************************** -->
                    <li><a href="#" onClick="sweduc.carregarUrl('resp_troca_senha.php');"><b>Troca de Senha</b></a></li>
                </ul>
            </li>
        </ul>

        <?php if ($cliente == 'grupoalfacem') { ?>
        <ul>
            <li>
                <a href="#janela1" id="videoRematricula" rel="modal" data-toggle="tooltip" data-placement="right" title="Vídeo explicativo de como prosseguir com a rematrícula"><img src="images/video1.png" title="Ajuda" border="0" /></a>
            </li>
        </ul>
        <?php } ?>
        <!-- ********************** NOVA MATRICULA ( ) *************************************** -->
        <?php /*

          <ul>
          <!-- <li style="background-color:#81CF73;border:1px solid #2BBA2A;"><a href="#nogo"><b>Matrícula nova</b></a> -->
          <li style="margin-top: 10px;"><a href="#nogo"><b>Boletos</b></a>
          <ul>
          <!-- ********************** CONFIGURAÇÕES EMPRESA *************************************** -->
          <li><a href="#" onClick="sweduc.carregarUrl('resp_alunos_lista_respfin.php');"><b>Dados cadastrais</b></a></li>
          <li><a href="#" onClick="abreanamnese();"><b>Anamnese</b></a></li>
          <li><a href="#" onClick="executacontrato();"><b>Contrato</b></a></li>
          <li><a href="#" onClick="executarequerimento();"><b>Requerimento</b></a></li>
          <li><a href="#" onClick="umboleto(<?=$rowFin['ffin_id']?>);"><b>Boleto</b></a></li>
          <!-- <li><a href="#" onClick="reemiteboleto();"><b>Reemitir Boleto</b></a></li> -->
          </ul>
          </li>
          </ul>
        */ ?>

        <div class="window" id="janela1">
            <a href="#" class="fechar"><img src="images/fechar.png" /></a>
            <center>
                <video width="853" height="480" controls>
                    <source src="videos/video-matr-responsaveis.mp4" type="video/mp4">
                    Seu navegador nao tem suporte para rodar video
                </video>
            </center>
        </div>
        <div id="mascara"></div>
        <form name="dadosboleto" id="dadosboleto" target="_blank" method="POST" action="lib/boletos/boletos.php">
            <input type="hidden" name="idlancamento" id="idlancamento" value="" />
        </form>

        <form name="reemiteboletoform" id="reemiteboletoform" target="_blank" method="POST" action="lib/boletos/boletos.php">
            <input type="hidden" name="idlancamento" id="boleto-reemissao" value="<?= $idboletosreemitir ?>" />
        </form>

        <form id="geraDocs" action="documentos_alunos.php" method="post" target="_blank" >
            <input type="hidden" name="nomefuncDOC" id="nomefuncDOC" value="" />
            <input type="hidden" name="idalunoDOC" id="idalunoDOC" value="" />
            <input type="hidden" name="idDOC" id="idDOC" value="" />
            <input type="hidden" name="idfuncionarioDOC" id="idfuncionarioDOC" value="" />
            <input type="hidden" name="idanoletivoDOC" id="idanoletivoDOC" value="" />
        </form>
    <?php } elseif ($tipo == 2 && $marketing[2] != 1 && $academico[11] != 1) { //FUNCIONÁRIOS ?>
        <?php foreach ($menu as $item) : ?>
            <?php $this->insert('Core/Components/MenuItem', $item + [ 'root' => true ]) ?>
        <?php endforeach ?>

        <!-- ********************** CONTATOS SW [5] ( 5 ) *************************************** -->

        <ul class="mainOption">
            <li>
                <a href="#" onClick="sweduc.carregarUrl('contatos_sw.php');"><b>Contatos SW</b></a>

            </li>
        </ul>

        <!-- <?php if (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] == 'development') : ?>
            <ul class="mainOption">
                <li>
                    <b><a href="#" onClick="sweduc.r()">Recarregar</a></b>
                </li>
            </ul>
             <?php endif ?> -->

    <?php } ?>
</div>
