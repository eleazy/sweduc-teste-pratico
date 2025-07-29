<?php
include('headers.php');
include('dao/conectar.php');
$keys = array_keys($_POST);
foreach ($keys as $k) {
    ${$k} = $_POST[$k];
}
include('permissoes.php');

$finalquery = "";
if ($idturma != "todos") {
    $finalquery = "AND alunos_matriculas.turmamatricula=$idturma";
}
$query = "SELECT *, alunos.id as aid FROM alunos, alunos_matriculas, pessoas, turmas WHERE alunos_matriculas.turmamatricula=turmas.id AND alunos.idpessoa=pessoas.id AND alunos.id=alunos_matriculas.idaluno " . $finalquery;
$result = mysql_query($query);
//echo $query."<br>";
?>

<script>
    $.mask.definitions['#'] = '[0123]';
    $.mask.definitions['@'] = '[01]';
    $.mask.definitions['&'] = '[12]';
    $('.date').mask("#9/@9/&999", {placeholder: 'dd/mm/yyyy'});
    $(".date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd/mm/yy"
    });


    function medias(idturma, idanoletivo, idaluno, nummatricula) {
        if ($('#conteudoBusca').html() != null)
            tmp = $('#conteudoBusca').html();
        else
            tmp = $('#conteudoLista').html();

        $.ajax({
            url: "alunos_medias.php",
            type: 'POST',
            context: jQuery('#dialog'),
            data: {idturma: idturma, idanoletivo: idanoletivo, idaluno: idaluno, idgr: "0", nummatricula: nummatricula, idpermissoes: $("#idpermissoes").val(), idpessoalogin: $("#idpessoalogin").val()},
            beforeSend: function () {
                $.blockUI({
                    message: $('#displayBox'),
                    css: {
                        top: ($(window).height() - 400) / 2 + 'px',
                        left: ($(window).width() - 400) / 2 + 'px',
                        width: '400px'
                    }
                });
            },
            complete: function () {
                $.unblockUI();
            },
            success: function (data) {
                this.html(data);
                $("#dialog").dialog("open");
            }
        });
    }

    function buscaNotas() {
        if ($("#idavaliacao").val() == -1) {
            $("#headnotas").html("");
            $("#bodynotas").html("");
            $("#salvanota").prop("disabled", "true");
        } else {
            $.ajax({
                url: "dao/notas.php",
                type: 'POST',
                data: {action: "buscaNotas", idanoletivo: "<?= $idanoletivo ?>", idturma: "<?= $idturma ?>", iddisciplina: $("#iddisciplina").val(), idperiodo: $("#idperiodo").val(), idavaliacao: $("#idavaliacao").val()},
                context: $("#headnotas"),
                beforeSend: function () {
                    $.blockUI({
                        message: $('#displayBox'),
                        css: {
                            top: ($(window).height() - 400) / 2 + 'px',
                            left: ($(window).width() - 400) / 2 + 'px',
                            width: '400px'
                        }
                    });
                },
                complete: function () {
                    $.unblockUI();
                },
                success: function (data) {
                    retorno = data.split("|");
                    this.html(retorno[0]);
                    $("#bodynotas").html(retorno[1]);
                    update_rows();
                    $("#salvanota").removeAttr("disabled");
                }
            });
        }
    }

    $("#salvanota").click(function () {
        $.ajax({
            url: "dao/notas.php",
            type: "POST",
            data: $("#formsalvanota").serialize(),
            beforeSend: function () {
                $.blockUI({
                    message: $('#displayBox'),
                    css: {
                        top: ($(window).height() - 400) / 2 + 'px',
                        left: ($(window).width() - 400) / 2 + 'px',
                        width: '400px'
                    }
                });
            },
            complete: function () {
                $.unblockUI();
            },
            success: function (data) {
                retorno = data.split("|");
                $('html, body').animate({scrollTop: 0}, 'slow');
                insereAlerta(retorno[0], "content", retorno[1], "", "");
                //$("#formsalvanota").get(0).reset();
            }
        });
    });


    $("#chkMarcaNota").click(function () {
        if ($('#datadanota').val() == "") {
            alert("Escolha a data para lançar as notas!");
            if ($(this).is(':checked'))
                $(this).attr("checked", false);
            else
                $(this).attr("checked", true);
        } else {
            $.ajax({
                url: "dao/notas.php",
                type: 'POST',
                data: {action: "marcaNota", idaluno: $(this).attr("name"), datadanota: $('#datadanota').val()},
                success: function (data) {
                    resposta = data.split("|");
                    insereAlerta(resposta[0], 'table-content-child', resposta[1], "", "");
                }
            });
        }
    });

    $('#datadanota').focus(function () {
        $(this).calendario({
            target: '#datadanota',
            top: 0,
            left: 200
        });
    }).blur(function () {
        if (!$('.calendario').is(':hover')) {
            $(".calendario").remove();
        }
    });
</script>
<!-- start content-outer ........................................................................................................................START -->
<div>
    <!-- start content -->
    <div id="content">
        <!--  start page-heading -->
        <div id="page-heading">
            <h3>Alunos</h3>
        </div>
        <!-- end page-heading -->
        <div id="table-content-child">


            <table border="0" width="100%" cellpadding="0" cellspacing="0" id="content-table">
                <tr>
                    <th rowspan="3" class="sized" style="background-image:url('images/shared/side_shadowleft.jpg');background-size: 100% 100%;" width="20" height="300" alt="" /></th>
                    <th class="topleft"></th>
                    <td id="tbl-border-top">&nbsp;</td>
                    <th class="topright"></th>
                    <th rowspan="3" class="sized" style="background-image:url('images/shared/side_shadowright.jpg');background-size: 100% 100%;" width="20" height="300" alt="" /></th>
                </tr>
                <tr>
                    <td id="tbl-border-left"></td>
                    <td>
                        <!--  start content-table-inner ...................................................................... START -->
                        <div id="content-table-inner">

                            <!--  start table-content  -->
                            <div id="table-content">

                                <!--  start product-table ..................................................................................... -->
                                <form id="formsalvanota" action="dao/notas.php" method="post" target="_blank">
                                    <input type="hidden" name="action" value="salvanota" />
                                    <input type="hidden" name="idanoletivo" value="<?= $_POST['idanoletivo'] ?>" />
                                    <input type="hidden" name="idpessoalogin" value="<?= $_POST['idpessoalogin'] ?>" />                    

                                    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
                                        <tr>
                                            <th class="table-header-repeat line-left minwidth-1">
                                                Período: <select name="idperiodo" id="idperiodo" onchange="buscaNotas();">  
                                                    <?php
                                                    $query1 = "SELECT * FROM periodos ORDER BY periodo ASC";
                                                    $result1 = mysql_query($query1);
                                                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                                                        ?>                           
                                                        <option value="<?= $row1['id'] ?>"><?= $row1['periodo'] ?></option>
                                                    <?php } ?>
                                                </select>        
                                            </th>
                                            <th class="table-header-repeat line-left minwidth-1">
                                                Avaliação: <select name="idavaliacao" id="idavaliacao" onchange="buscaNotas();">    
                                                    <option value="-1">Escolha uma avaliação</option>
                                                    <option value="0">TODAS</option>

                                                    <?php
                                                    $query1 = "SELECT * FROM avaliacoes ORDER BY avaliacao ASC";
                                                    $result1 = mysql_query($query1);
                                                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                                                        ?>                           
                                                        <option value="<?= $row1['id'] ?>"><?= $row1['avaliacao'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </th>
                                            <th class="table-header-repeat line-left minwidth-1">
                                                Disciplina: <select name="iddisciplina" id="iddisciplina" onchange="buscaNotas();"> 
                                                    <?php
                                                    $query1 = "SELECT * FROM disciplinas WHERE idanoletivo=" . $idanoletivo . " ORDER BY disciplina ASC";
                                                    $result1 = mysql_query($query1);
                                                    while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
                                                        ?>                           
                                                        <option value="<?= $row1['id'] ?>"><?= $row1['disciplina'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </th>   
                                            <th class="table-header-repeat line-left minwidth-1" style="text-align:right">
                                                <input type="button" name="salvanota" id="salvanota" disabled="disabled" value=" SALVAR NOTAS " class="button" />
                                            </th>
                                        </tr>
                                    </table>

                                    <table border="0" width="100%" cellpadding="0" cellspacing="0" id="product-table">
                                        <thead id="headnotas">

                                        </thead>
                                        <tbody id="bodynotas">          

                                        </tbody>
                                    </table>
                                    <!--  end product-table................................... --> 
                                </form>
                            </div>
                        </div>
        <!-- <script>$('#blue').parent().parent().parent().hide(500); insereAlerta('blue', 'table-content', 'Encontrados <?= $cnt ?> alunos', '', '');</script> -->

                        <!--  end content-table-inner ............................................END  -->
                    </td>
                    <td id="tbl-border-right"></td>
                </tr>
                <tr>
                    <th class="sized bottomleft"></th>
                    <td id="tbl-border-bottom">&nbsp;</td>
                    <th class="sized bottomright"></th>
                </tr>
            </table>
        </div>
        <div class="clear">&nbsp;</div>

    </div>
    <!--  end content -->
    <div class="clear">&nbsp;</div>
</div>
<!--  end content-outer........................................................END -->
<script type="text/javascript">
    $(function () {
        $('input').checkBox();
        $('#toggle-all').click(function () {
            $('#toggle-all').toggleClass('toggle-checked');
            $('#mainform input[type=checkbox]').checkBox('toggle');
            return false;
        });
    });

    $(document).ready(function () {
        $('.styledselect').selectbox({inputClass: "selectbox_styled"});
    });

    $('#loader').hide();
    update_rows();

</script>
